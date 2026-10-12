<?php

namespace Tests\Feature;

use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Live smoke test that runs against the real app over ngrok (or any remote host).
 *
 * It is SKIPPED unless NGROK_TEST_URL is set, so it never runs in the normal
 * suite. This is what catches environment-only problems that in-process tests
 * miss (strict Eloquent 500s, bad cookies, restarted Reverb, stale tunnels).
 *
 * Usage (PowerShell):
 *   $env:NGROK_TEST_URL = 'https://preschool-yearling-washout.ngrok-free.dev'
 *   php artisan test tests/Feature/LiveNgrokSmokeTest.php
 *
 * Optional logins (each role is exercised only when its credentials are set):
 *   $env:NGROK_ADMIN_EMAIL / NGROK_ADMIN_PASSWORD
 *   $env:NGROK_RESIDENT_EMAIL / NGROK_RESIDENT_PASSWORD
 *   $env:NGROK_OFFICER_EMAIL / NGROK_OFFICER_PASSWORD
 */
class LiveNgrokSmokeTest extends TestCase
{
    private string $base;

    private CookieJar $jar;

    protected function setUp(): void
    {
        parent::setUp();

        $base = env('NGROK_TEST_URL') ?: getenv('NGROK_TEST_URL') ?: null;
        if (! $base) {
            $this->markTestSkipped('Set NGROK_TEST_URL to run live ngrok smoke tests.');
        }

        $this->base = rtrim($base, '/');
        $this->jar = new CookieJar;
    }

    private function client()
    {
        $request = Http::withOptions(['cookies' => $this->jar, 'allow_redirects' => false])
            ->withHeaders([
                'Accept' => 'application/json',
                // Bypass ngrok's free-tier browser interstitial.
                'ngrok-skip-browser-warning' => '1',
            ])
            ->timeout(20);

        // Dev smoke over ngrok: local PHP often lacks a CA bundle (cURL error 60).
        // Skip verification unless NGROK_VERIFY_SSL=1 is explicitly set.
        $verify = filter_var(env('NGROK_VERIFY_SSL') ?: getenv('NGROK_VERIFY_SSL') ?: false, FILTER_VALIDATE_BOOL);
        if (! $verify) {
            $request = $request->withoutVerifying();
        }

        return $request;
    }

    private function login(string $portal, string $path, array $credentials): bool
    {
        $page = $this->client()->get("{$this->base}/login?portal={$portal}");
        if ($page->status() < 200 || $page->status() >= 400) {
            return false;
        }

        preg_match('/name="csrf-token" content="([^"]+)"/', $page->body(), $m);
        $token = $m[1] ?? '';

        $res = $this->client()
            ->withHeaders(['X-CSRF-TOKEN' => $token, 'Content-Type' => 'application/json'])
            ->post("{$this->base}{$path}", $credentials);

        return $res->json('success') === true;
    }

    private function assertPagesHealthy(array $paths): void
    {
        foreach ($paths as $path) {
            $res = $this->client()->get("{$this->base}{$path}");
            $this->assertLessThan(
                500,
                $res->status(),
                "Live {$path} returned HTTP {$res->status()} on {$this->base}"
            );
        }
    }

    public function test_live_public_pages_load(): void
    {
        foreach (['/', '/login', '/admin-access', '/up'] as $path) {
            $res = $this->client()->get("{$this->base}{$path}");
            $this->assertLessThan(500, $res->status(), "Live {$path} returned HTTP {$res->status()}");
        }
    }

    public function test_live_api_requires_authentication(): void
    {
        foreach (['/api/stats', '/api/residents', '/api/dashboard-data'] as $path) {
            $res = $this->client()->get("{$this->base}{$path}");
            $this->assertContains($res->status(), [401, 403], "Live {$path} should be guarded, got {$res->status()}");
        }
    }

    public function test_live_admin_session_pages_are_healthy(): void
    {
        $email = env('NGROK_ADMIN_EMAIL') ?: getenv('NGROK_ADMIN_EMAIL');
        $password = env('NGROK_ADMIN_PASSWORD') ?: getenv('NGROK_ADMIN_PASSWORD');
        if (! $email || ! $password) {
            $this->markTestSkipped('Set NGROK_ADMIN_EMAIL / NGROK_ADMIN_PASSWORD to test the admin portal.');
        }

        $this->assertTrue($this->login('admin', '/admin-access', compact('email', 'password')), 'Admin login failed on live app.');

        $this->assertPagesHealthy([
            '/dashboard',
            '/api/dashboard-data',
            '/api/residents',
            '/api/households',
            '/api/officers',
            '/api/announcements',
            '/api/issues',
            '/api/hoa-expenses',
            '/api/hoa-policies',
            '/api/gcash-settings',
            '/api/assessments',
            '/api/budgets',
            '/api/ledger/aging',
            '/api/settings',
        ]);
    }

    public function test_live_resident_session_pages_are_healthy(): void
    {
        $email = env('NGROK_RESIDENT_EMAIL') ?: getenv('NGROK_RESIDENT_EMAIL');
        $password = env('NGROK_RESIDENT_PASSWORD') ?: getenv('NGROK_RESIDENT_PASSWORD');
        if (! $email || ! $password) {
            $this->markTestSkipped('Set NGROK_RESIDENT_EMAIL / NGROK_RESIDENT_PASSWORD to test the resident portal.');
        }

        $this->assertTrue($this->login('resident', '/login', [
            'email' => $email, 'password' => $password, 'role' => 'resident',
        ]), 'Resident login failed on live app.');

        $this->assertPagesHealthy([
            '/residents',
            '/api/resident/portal-data',
            '/api/residents/me',
            '/api/announcements',
            '/api/hoa-expenses',
            '/api/hoa-policies',
            '/api/map/all',
        ]);
    }

    public function test_live_officer_session_pages_are_healthy(): void
    {
        $email = env('NGROK_OFFICER_EMAIL') ?: getenv('NGROK_OFFICER_EMAIL');
        $password = env('NGROK_OFFICER_PASSWORD') ?: getenv('NGROK_OFFICER_PASSWORD');
        if (! $email || ! $password) {
            $this->markTestSkipped('Set NGROK_OFFICER_EMAIL / NGROK_OFFICER_PASSWORD to test the officer portal.');
        }

        $this->assertTrue($this->login('officer', '/login', [
            'email' => $email, 'password' => $password, 'role' => 'officer',
        ]), 'Officer login failed on live app.');

        $this->assertPagesHealthy([
            '/officer-portal',
            '/api/dashboard-data',
            '/api/residents',
            '/api/issues',
            '/api/announcements',
            '/api/settings',
        ]);
    }
}
