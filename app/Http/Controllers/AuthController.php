<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();

            // admin only
            if (Auth::user()->role !== 'admin') {
                Auth::logout();
                return back()->with('error', 'Access denied. Admins only.');
            }

            return redirect('/');
        }

        return back()->with('error', 'Invalid credentials.');
    }
}