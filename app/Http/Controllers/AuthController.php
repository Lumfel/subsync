<?php

namespace App\Http\Controllers;

use App\Models\Admin;
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
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $admin = Admin::firstWhere('email', $credentials['email']);

        if ($admin && Hash::check($credentials['password'], $admin->password)) {
            Auth::guard('resident')->logout();
            Auth::guard('officer')->logout();
            $request->session()->forget(['resident_id', 'officer_id']);
            $request->session()->put('admin_id', $admin->id);
            $request->session()->put('admin_name', $admin->name);
            $request->session()->put('active_role', 'admin');
            $request->session()->regenerate();

            return response()->json([
                'success'  => true,
                'redirect' => route('dashboard'),
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
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $role = $request->input('role', 'resident');

        if ($role === 'officer') {
            $officer = Officer::firstWhere('email', $credentials['email']);

            if ($officer && Hash::check($credentials['password'], $officer->password)) {
                if ($officer->status === 'Pending') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your account is awaiting admin approval.',
                    ]);
                }
                Auth::guard('resident')->logout();
                $request->session()->forget(['admin_id', 'admin_name', 'resident_id']);
                Auth::guard('officer')->login($officer);
                $request->session()->put('active_role', 'officer');
                $request->session()->put('officer_id', $officer->id);
                $request->session()->regenerate();

                return response()->json([
                    'success'  => true,
                    'redirect' => route('officer.portal'),
                ]);
            }
        } else {
            $resident = Resident::where('email', $credentials['email'])->first();
            if ($resident && $resident->status === 'Pending' && Hash::check($credentials['password'], $resident->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is awaiting admin approval.',
                ]);
            }
            if (Auth::guard('resident')->attempt([
                'email'    => $credentials['email'],
                'password' => $credentials['password'],
            ])) {
                Auth::guard('officer')->logout();
                $request->session()->forget(['admin_id', 'admin_name', 'officer_id']);
                $request->session()->put('active_role', 'resident');
                $request->session()->put('resident_id', Auth::guard('resident')->id());
                $request->session()->regenerate();

                return response()->json([
                    'success'  => true,
                    'redirect' => route('residents'),
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
                'name'             => 'required|string|max:150',
                'email'            => 'required|email|unique:officers,email',
                'password'         => 'required|string|min:8',
                'role_description' => 'nullable|string|max:150',
            ]);
            $data['password'] = Hash::make($data['password']);
            $data['status']   = 'Pending';
            Officer::create($data);
        } else {
            $data = $request->validate([
                'name'           => 'required|string|max:150',
                'email'          => 'required|email|unique:residents,email',
                'password'       => 'required|string|min:8',
                'contact_number' => 'nullable|string|max:20',
            ]);
            Resident::create([
                'name'            => $data['name'],
                'email'           => $data['email'],
                'password'        => Hash::make($data['password']),
                'contact_number'  => $data['contact_number'] ?? null,
                'status'          => 'Pending',
                'current_balance' => 0,
            ]);
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
        Auth::guard('resident')->logout();
        Auth::guard('officer')->logout();
        $request->session()->forget(['admin_id', 'admin_name', 'active_role', 'resident_id', 'officer_id']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
