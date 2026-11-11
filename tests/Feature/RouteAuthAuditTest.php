<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Household;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Route-level auth audit: every registered route must carry one of the portal
 * guards unless it is an explicit public entry point (login/landing/oauth/etc).
 * Also guards the shared request-context script (X-Portal / ngrok header),
 * which previously existed as six copies whose inline patches silently
 * no-oped when APP_BASE was empty.
 */
class RouteAuthAuditTest extends TestCase
{
    use RefreshDatabase;

    private const PUBLIC_URIS = [
        '/',
        'login',
        'register',
        'logout',
        'admin-access',
        'privacy',
        'mapping',
        'auth/google',
        'auth/google/callback',
        'auth/google/complete',
        // Framework routes.
        'broadcasting/auth',
        'up',
    ];

    public function test_every_route_is_auth_protected_or_whitelisted(): void
    {
        $unprotected = [];

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            if (in_array($uri, self::PUBLIC_URIS, true)) {
                continue;
            }

            $middleware = array_filter($route->gatherMiddleware(), 'is_string');

            $protected = false;
            foreach ($middleware as $m) {
                if ($m === 'admin.auth' || $m === 'officer.auth'
                    || str_starts_with($m, 'auth:')
                    || str_starts_with($m, 'portal.role')) {
                    $protected = true;
                    break;
                }
            }

            if (! $protected) {
                $unprotected[] = strtoupper(implode('|', $route->methods())).' '.$uri
                    .' -> ['.implode(', ', $middleware).']';
            }
        }

        $this->assertSame([], $unprotected, "Routes without auth middleware:\n".implode("\n", $unprotected));
    }

    public function test_portal_views_load_the_shared_request_context(): void
    {
        $ctx = file_get_contents(public_path('js/portal-context.js'));

        $this->assertStringContainsString('X-Portal', $ctx);
        $this->assertStringContainsString('ngrok-skip-browser-warning', $ctx);
        $this->assertStringContainsString("'portal=' + window.__PORTAL", $ctx);

        foreach ([
            'layouts/admin-dashboard.blade.php',
            'layouts/officer-portal.blade.php',
            'layouts/residents.blade.php',
            'layouts/login_admin.blade.php',
            'layouts/login_residen_officers.blade.php',
            'settings.blade.php',
        ] as $view) {
            $html = file_get_contents(resource_path('views/'.$view));

            $this->assertStringContainsString('js/portal-context.js', $html, $view.' must load portal-context.js');
            // The old inline copies skipped the whole patch when APP_BASE was
            // empty (the normal root-domain install), so headers never applied.
            $this->assertStringNotContainsString('if (!base) return;', $html, $view.' still has the broken inline patch');
        }
    }

    public function test_portal_responses_include_the_request_context_script(): void
    {
        $house = Household::create(['block_lot_number' => 'Block 1 Lot 1']);
        $resident = Resident::create([
            'name' => 'Resident', 'email' => 'r@x.com', 'password' => 'Secret@12345',
            'status' => 'Active', 'house_id' => $house->id,
        ]);
        $admin = Admin::create(['name' => 'Admin', 'email' => 'a@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);

        $this->actingAs($resident, 'resident')->get('/residents')->assertOk()
            ->assertSee('js/portal-context.js', false);

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'Admin'])->get('/dashboard')->assertOk()
            ->assertSee('js/portal-context.js', false);

        $this->get('/login')->assertOk()
            ->assertSee('js/portal-context.js', false);

        $this->get('/admin-access')->assertOk()
            ->assertSee('js/portal-context.js', false);
    }
}
