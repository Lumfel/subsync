<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PortalRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $activeRole = match (true) {
            $request->session()->has('admin_id') => 'admin',
            auth('officer')->check() => 'officer',
            auth('resident')->check() => 'resident',
            default => null,
        };

        if (!$activeRole || ($roles && !in_array($activeRole, $roles, true))) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            abort(403);
        }

        return $next($request);
    }
}
