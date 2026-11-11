<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Household;
use App\Models\Officer;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Handle admin login (checks the admins table).
     */
    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email = strtolower(trim($credentials['email']));
        $admin = Admin::firstWhere('email', $email);

        if (! $admin && Admin::count() === 0 && app()->environment(['local', 'testing'])) {
            $admin = Admin::create([
                'name' => 'System Admin',
                'email' => 'admin@subsync.com',
                'password' => Hash::make('Admin@12345'),
                'status' => 'Active',
            ]);
        }

        if ($admin && $admin->status === 'Active' && Hash::check($credentials['password'], $admin->password)) {
            Auth::guard('resident')->logout();
            Auth::guard('officer')->logout();
            $request->session()->forget(['resident_id', 'officer_id']);
            // Also log in on the admin guard: the broadcasting auth endpoint
            // resolves private-channel subscribers through the guards.
            Auth::guard('admin')->login($admin);
            $request->session()->put('admin_id', $admin->id);
            $request->session()->put('admin_name', $admin->name);
            $request->session()->put('active_role', 'admin');
            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'redirect' => $this->basePath($request).'/dashboard',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials.',
        ]);
    }

    /**
     * Handle resident / officer login (checks the residents or officers table).
     */
    public function residentLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $role = $request->input('role', 'resident');

        if ($role === 'officer') {
            $officer = Officer::firstWhere('email', $credentials['email']);

            if ($officer && Hash::check($credentials['password'], $officer->password)) {
                if ($officer->status !== 'Active') {
                    return response()->json([
                        'success' => false,
                        'message' => $officer->status === 'Pending'
                            ? 'Your account is awaiting admin approval.'
                            : 'Your account has been deactivated. Contact the HOA office.',
                    ]);
                }
                Auth::guard('resident')->logout();
                $request->session()->forget(['admin_id', 'admin_name', 'resident_id']);
                Auth::guard('officer')->login($officer);
                $request->session()->put('active_role', 'officer');
                $request->session()->put('officer_id', $officer->id);
                $request->session()->regenerate();

                return response()->json([
                    'success' => true,
                    'redirect' => $this->basePath($request).'/officer-portal',
                ]);
            }
        } else {
            $resident = Resident::where('email', $credentials['email'])->first();
            if ($resident && Hash::check($credentials['password'], $resident->password) && $resident->status !== 'Active') {
                return response()->json([
                    'success' => false,
                    'message' => $resident->status === 'Pending'
                        ? 'Your account is awaiting admin approval.'
                        : 'Your account has been deactivated. Contact the HOA office.',
                ]);
            }
            if (Auth::guard('resident')->attempt([
                'email' => $credentials['email'],
                'password' => $credentials['password'],
            ])) {
                Auth::guard('officer')->logout();
                $request->session()->forget(['admin_id', 'admin_name', 'officer_id']);
                $request->session()->put('active_role', 'resident');
                $request->session()->put('resident_id', Auth::guard('resident')->id());
                $request->session()->regenerate();

                return response()->json([
                    'success' => true,
                    'redirect' => $this->basePath($request).'/residents',
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials. Please check your email and password.',
        ]);
    }

    /**
     * Public self-registration for residents and officers (creates with Pending status).
     */
    public function register(Request $request)
    {
        $role = $request->input('role', 'resident');

        if ($role === 'officer') {
            $data = $request->validate([
                'name' => 'required|string|max:150',
                'email' => 'required|email|unique:officers,email',
                'password' => 'required|string|min:8',
                'contact_number' => 'nullable|string|max:20',
                'role_description' => 'nullable|string|max:100',
                'consent' => 'accepted',
            ]);
            $data['password'] = Hash::make($data['password']);
            $data['status'] = 'Pending';
            Officer::create($data);
        } else {
            $data = $request->validate([
                'name' => 'required|string|max:150',
                'email' => 'required|email|unique:residents,email',
                'password' => 'required|string|min:8',
                'contact_number' => 'nullable|string|max:20',
                'block_lot' => 'nullable|string|max:50',
                'consent' => 'accepted',
            ]);

            $residentData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'contact_number' => $data['contact_number'] ?? null,
                'status' => 'Pending',
                'current_balance' => 0,
            ];

            if (! empty($data['block_lot'])) {
                $residentData['house_id'] = Household::findOrCreateByBlockLot($data['block_lot'])->id;
            }

            Resident::create($residentData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Registration submitted. Awaiting admin approval.',
        ]);
    }

    /**
     * Log out the current user / admin.
     */
    public function logout(Request $request)
    {
        $isAdmin = $request->input('portal') === 'admin'
            || $request->session()->get('active_role') === 'admin'
            || (bool) $request->session()->get('admin_id')
            || Auth::guard('admin')->check();

        $isOfficer = $request->input('portal') === 'officer'
            || $request->session()->get('active_role') === 'officer'
            || (bool) $request->session()->get('officer_id')
            || Auth::guard('officer')->check();

        Auth::guard('resident')->logout();
        Auth::guard('officer')->logout();
        Auth::guard('admin')->logout();
        $request->session()->forget(['admin_id', 'admin_name', 'active_role', 'resident_id', 'officer_id']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($isAdmin) {
            return redirect(url('admin-access'));
        }

        if ($isOfficer) {
            return redirect(url('login').'?portal=officer');
        }

        return redirect(url('login'));
    }

    /**
     * Base path of the app under the current web server.
     * Handles subdirectory installs (e.g. Apache serving from /subsync/public)
     * so browser redirects resolve to the correct URL.
     */
    private function basePath(Request $request): string
    {
        return rtrim((string) $request->getBaseUrl(), '/');
    }
}
