<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Household;
use App\Models\Officer;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private function resident(array $attrs = []): Resident
    {
        $house = Household::create(['block_lot_number' => 'Block 1 Lot 1']);

        return Resident::create(array_merge([
            'name' => 'Resident', 'email' => 'r@x.com',
            'password' => 'Secret@12345', 'status' => 'Active', 'house_id' => $house->id,
        ], $attrs));
    }

    private function officer(array $attrs = []): Officer
    {
        return Officer::create(array_merge([
            'name' => 'Officer', 'email' => 'o@x.com',
            'password' => 'Secret@12345', 'status' => 'Active',
        ], $attrs));
    }

    private function admin(): Admin
    {
        return Admin::create(['name' => 'Admin', 'email' => 'a@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    public function test_login_page_loads(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_admin_login_page_loads(): void
    {
        $this->get('/admin-access')->assertOk();
    }

    // ── Admin ──
    public function test_admin_can_log_in_with_valid_credentials(): void
    {
        $admin = $this->admin();

        $res = $this->postJson('/admin-access', ['email' => 'a@x.com', 'password' => 'Secret@12345'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertStringEndsWith('/dashboard', $res->json('redirect'));
        $this->assertSame($admin->id, session('admin_id'));
        $this->assertSame('admin', session('active_role'));
    }

    public function test_admin_login_rejects_wrong_password(): void
    {
        $this->admin();

        $this->postJson('/admin-access', ['email' => 'a@x.com', 'password' => 'wrong-password'])
            ->assertOk()
            ->assertJson(['success' => false]);

        $this->assertNull(session('admin_id'));
    }

    public function test_admin_login_seeds_default_admin_when_none_exists(): void
    {
        $this->assertSame(0, Admin::count());

        $this->postJson('/admin-access', ['email' => 'admin@subsync.com', 'password' => 'Admin@12345'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame(1, Admin::count());
    }

    // ── Resident ──
    public function test_active_resident_can_log_in(): void
    {
        $resident = $this->resident();

        $res = $this->postJson('/login', ['email' => 'r@x.com', 'password' => 'Secret@12345', 'role' => 'resident'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertStringEndsWith('/residents', $res->json('redirect'));
        $this->assertAuthenticatedAs($resident, 'resident');
        $this->assertSame('resident', session('active_role'));
    }

    public function test_pending_resident_cannot_log_in(): void
    {
        $this->resident(['status' => 'Pending']);

        $this->postJson('/login', ['email' => 'r@x.com', 'password' => 'Secret@12345', 'role' => 'resident'])
            ->assertOk()
            ->assertJson(['success' => false])
            ->assertJsonPath('message', 'Your account is awaiting admin approval.');

        $this->assertGuest('resident');
    }

    public function test_resident_login_rejects_wrong_password(): void
    {
        $this->resident();

        $this->postJson('/login', ['email' => 'r@x.com', 'password' => 'nope-nope', 'role' => 'resident'])
            ->assertOk()
            ->assertJson(['success' => false]);

        $this->assertGuest('resident');
    }

    public function test_resident_login_requires_email_and_password(): void
    {
        $this->postJson('/login', ['email' => '', 'password' => '', 'role' => 'resident'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    // ── Officer ──
    public function test_active_officer_can_log_in(): void
    {
        $officer = $this->officer();

        $res = $this->postJson('/login', ['email' => 'o@x.com', 'password' => 'Secret@12345', 'role' => 'officer'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertStringEndsWith('/officer-portal', $res->json('redirect'));
        $this->assertAuthenticatedAs($officer, 'officer');
        $this->assertSame('officer', session('active_role'));
    }

    public function test_pending_officer_cannot_log_in(): void
    {
        $this->officer(['status' => 'Pending']);

        $this->postJson('/login', ['email' => 'o@x.com', 'password' => 'Secret@12345', 'role' => 'officer'])
            ->assertOk()
            ->assertJson(['success' => false])
            ->assertJsonPath('message', 'Your account is awaiting admin approval.');

        $this->assertGuest('officer');
    }

    public function test_officer_login_ignores_resident_credentials(): void
    {
        // A resident email must not authenticate through the officer role.
        $this->resident();

        $this->postJson('/login', ['email' => 'r@x.com', 'password' => 'Secret@12345', 'role' => 'officer'])
            ->assertOk()
            ->assertJson(['success' => false]);

        $this->assertGuest('officer');
    }

    public function test_officer_login_switches_off_resident_session(): void
    {
        $resident = $this->resident();
        $officer = $this->officer(['email' => 'o@x.com']);

        $this->actingAs($resident, 'resident');

        $this->postJson('/login', ['email' => 'o@x.com', 'password' => 'Secret@12345', 'role' => 'officer'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertAuthenticatedAs($officer, 'officer');
        $this->assertGuest('resident');
    }

    public function test_resident_login_switches_off_officer_session(): void
    {
        $officer = $this->officer();
        $resident = $this->resident(['email' => 'r2@x.com']);

        $this->actingAs($officer, 'officer');

        $this->postJson('/login', ['email' => 'r2@x.com', 'password' => 'Secret@12345', 'role' => 'resident'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertAuthenticatedAs($resident, 'resident');
        $this->assertGuest('officer');
    }
}
