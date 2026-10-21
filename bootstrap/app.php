<?php

use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\OfficerAuth;
use App\Http\Middleware\PortalRole;
use App\Http\Middleware\ResolvePortalSession;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Per-portal session cookies — must run before StartSession.
        $middleware->prepend(ResolvePortalSession::class);

        // ResolvePortalSession runs BEFORE EncryptCookies, so it reads cookies
        // raw. The Google OAuth portal hint must therefore stay unencrypted or
        // the officer callback silently resolves to the resident session and
        // fails with InvalidStateException. It only carries a role name.
        $middleware->encryptCookies(except: ['subsync_portal_hint']);

        // Trust ngrok/reverse proxies: Laravel then sees X-Forwarded-Proto (https)
        // and generates https:// URLs (prevents mixed-content blocked fetches),
        // and rate limiting sees real client IPs.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'admin.auth' => AdminAuth::class,
            'officer.auth' => OfficerAuth::class,
            'portal.role' => PortalRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
