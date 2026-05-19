<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Officer;
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
            $request->session()->put('admin_id', $admin->id);
            $request->session()->put('admin_name', $admin->name);
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
                Auth::guard('officer')->login($officer);
                $request->session()->regenerate();

                return response()->json([
                    'success'  => true,
                    'redirect' => route('officer.portal'),
                ]);
            }
        } else {
            if (Auth::guard('resident')->attempt([
                'email'    => $credentials['email'],
                'password' => $credentials['password'],
            ])) {
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
     * Log out the current user / admin.
     */
    public function logout(Request $request)
    {
        Auth::guard('resident')->logout();
        Auth::guard('officer')->logout();
        $request->session()->forget(['admin_id', 'admin_name']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
