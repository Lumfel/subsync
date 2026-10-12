<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Household;
use App\Models\Officer;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Documents the multi-portal session model:
 *  - each portal (admin / officer / resident) has its OWN session cookie, so
 *    the three roles CAN be signed in simultaneously in the same browser;
 *  - within a single portal session only one guard is active, and logging in
 *    as another role clears the others (defensive, same-portal only).
 */
class SessionIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function sessionCookieName($response): ?string
    {
        foreach ($response->headers->getCookies() as $cookie) {
            $name = $cookie->getName();
            if (str_starts_with($name, 'subsync_')) {
                return $name;
            }
        }

        return null;
    }

    private function resident(): Resident
    {
        $house = Household::create(['block_lot_number' => 'Block 1 Lot 1']);

        return Resident::create(['name' => 'R', 'email' => 'r@x.com', 'password' => 'Secret@12345', 'status' => 'Active', 'house_id' => $house->id]);
    }

    private function officer(): Officer
    {
        return Officer::create(['name' => 'O', 'email' => 'o@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    private function admin(): Admin
    {
        return Admin::create(['name' => 'A', 'email' => 'a@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    // One request per test method (fresh app) so the session cookie name isn't cached.
    public function test_resident_portal_uses_resident_cookie(): void
    {
        $this->assertSame('subsync_resident_session', $this->sessionCookieName($this->get('/login')));
    }

    public function test_officer_portal_uses_officer_cookie(): void
    {
        $this->assertSame('subsync_officer_session', $this->sessionCookieName($this->get('/login?portal=officer')));
    }

    public function test_admin_portal_uses_admin_cookie(): void
    {
        $this->assertSame('subsync_admin_session', $this->sessionCookieName($this->get('/admin-access')));
    }

    public function test_officer_login_clears_admin_and_resident_in_same_session(): void
    {
        $admin = $this->admin();
        $this->officer();

        $this->withSession(['admin_id' => $admin->id, 'admin_name' => 'A']);
        $this->actingAs($this->resident(), 'resident');

        $this->postJson('/login', ['email' => 'o@x.com', 'password' => 'Secret@12345', 'role' => 'officer'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNull(session('admin_id'));
        $this->assertTrue(auth('officer')->check());
        $this->assertFalse(auth('resident')->check());
    }

    public function test_admin_login_clears_officer_and_resident_in_same_session(): void
    {
        $admin = $this->admin();
        $this->actingAs($this->officer(), 'officer');
        $this->actingAs($this->resident(), 'resident');

        $this->postJson('/admin-access', ['email' => 'a@x.com', 'password' => 'Secret@12345'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame($admin->id, session('admin_id'));
        $this->assertFalse(auth('officer')->check());
        $this->assertFalse(auth('resident')->check());
    }

    public function test_logout_clears_current_session(): void
    {
        $resident = $this->resident();
        $this->actingAs($resident, 'resident');

        $this->post('/logout')->assertRedirect(url('login'));

        $this->assertGuest('resident');
        $this->assertNull(session('active_role'));
    }
}
