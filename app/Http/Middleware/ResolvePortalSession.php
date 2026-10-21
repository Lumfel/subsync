<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gives each portal (admin / officer / resident) its own session cookie so that
 * different people/roles can be signed in simultaneously in the same browser
 * (e.g. the admin dashboard in one tab and the resident portal in another)
 * without their sessions overwriting each other.
 *
 * Runs as global middleware, before StartSession, so the cookie name is set
 * before the session is booted. The portal is resolved, in priority order, from:
 *   1. X-Portal header (added by each portal's fetch wrapper)
 *   2. ?portal= query string (settings links, SSE)
 *   3. portal input (logout forms)
 *   4. role input on /login + /register
 *   5. the request path (navigations)
 *   6. the Referer header (XHR/EventSource from a portal page)
 *
 * NOTE: this middleware is prepended globally and therefore runs BEFORE
 * EncryptCookies — any cookie read here must be excluded from encryption
 * (`subsync_portal_hint` is; see bootstrap/app.php). Encrypted values would
 * never match the whitelist and would silently fall back to "resident",
 * breaking the officer Google login.
 */
class ResolvePortalSession
{
    private const PORTALS = ['admin', 'officer', 'resident', 'shared'];

    public function handle(Request $request, Closure $next): Response
    {
        config(['session.cookie' => 'subsync_'.$this->resolvePortal($request).'_session']);

        return $next($request);
    }

    private function resolvePortal(Request $request): string
    {
        foreach ([
            $request->header('X-Portal'),
            $request->query('portal'),
            $request->input('portal'),
        ] as $hint) {
            if (is_string($hint) && in_array($hint, self::PORTALS, true)) {
                return $hint;
            }
        }

        $role = $request->input('role');
        if (is_string($role) && in_array($role, ['admin', 'officer', 'resident'], true)
            && ($request->is('login') || $request->is('register'))) {
            return $role;
        }

        // Google OAuth round-trip: the role lives in ?role= on redirect and in a
        // short-lived cookie on the callback (which comes back from Google).
        if ($request->is('auth/google*')) {
            $hint = $request->query('role');
            if (! in_array($hint, ['resident', 'officer'], true)) {
                $hint = $request->cookie('subsync_portal_hint');
            }

            return in_array($hint, ['resident', 'officer'], true) ? $hint : 'resident';
        }

        if ($request->is('admin-access') || $request->is('dashboard') || $request->is('design-system')) {
            return 'admin';
        }
        if ($request->is('officer-portal')) {
            return 'officer';
        }
        if ($request->is('residents') || $request->is('login') || $request->is('register')) {
            return 'resident';
        }

        $path = (string) (parse_url((string) $request->headers->get('referer', ''), PHP_URL_PATH) ?: '');
        if ($path === '/dashboard' || str_starts_with($path, '/admin')) {
            return 'admin';
        }
        if (str_starts_with($path, '/officer-portal')) {
            return 'officer';
        }
        if (str_starts_with($path, '/residents')) {
            return 'resident';
        }

        return 'shared';
    }
}
