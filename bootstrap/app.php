<?php

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
        $middleware->prepend(\App\Http\Middleware\ResolvePortalSession::class);

        // Trust ngrok/reverse proxies: Laravel then sees X-Forwarded-Proto (https)
        // and generates https:// URLs (prevents mixed-content blocked fetches),
        // and rate limiting sees real client IPs.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'admin.auth'   => \App\Http\Middleware\AdminAuth::class,
            'officer.auth' => \App\Http\Middleware\OfficerAuth::class,
            'portal.role'  => \App\Http\Middleware\PortalRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
