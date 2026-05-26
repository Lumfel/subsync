<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OfficerAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('officer')->check()) {
            return redirect()->route('login');
        }

        $request->session()->forget(['admin_id', 'admin_name']);
        $request->session()->put('active_role', 'officer');

        return $next($request);
    }
}
