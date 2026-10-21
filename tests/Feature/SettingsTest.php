<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Test Admin', 'email' => 'admin@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
    }

    private function resident(string $email = 'resident@example.test'): Resident
    {
        return Resident::create([
            'name' => 'Test Resident', 'email' => $email,
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
    }

    public function test_resident_can_open_settings_page(): void
    {
        $res = $this->actingAs($this->resident(), 'resident')->get('/settings');
        $res->assertOk()->assertSee('Settings')->assertSee('Appearance');
    }

    public function test_settings_endpoint_returns_profile_and_defaults(): void
    {
        $res = $this->actingAs($this->resident(), 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/settings');

        $res->assertOk()
            ->assertJsonPath('user.type', 'resident')
            ->assertJsonPath('preferences.theme', 'light')
            ->assertJsonPath('preferences.notify_announcements', true);
    }

    public function test_unauthenticated_settings_is_rejected(): void
    {
        $this->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/settings')
            ->assertStatus(401);
    }

    public function test_resident_can_update_profile(): void
    {
        $resident = $this->resident();

        $this->actingAs($resident, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->putJson('/api/settings/profile', [
                'name' => 'Juan Dela Cruz',
                'email' => 'juan@example.test',
                'contact_number' => '09171234567',
            ])
            ->assertOk()
            ->assertJsonPath('user.name', 'Juan Dela Cruz');

        $this->assertDatabaseHas('residents', ['id' => $resident->id, 'email' => 'juan@example.test']);
    }

    public function test_profile_update_rejects_duplicate_email(): void
    {
        $this->resident('taken@example.test');
        $resident = $this->resident('me@example.test');

        $this->actingAs($resident, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->putJson('/api/settings/profile', [
                'name' => 'Me', 'email' => 'taken@example.test',
            ])
            ->assertStatus(422);
    }

    public function test_resident_can_change_password(): void
    {
        $resident = $this->resident();

        $this->actingAs($resident, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/settings/password', [
                'current_password' => 'Secret@12345',
                'password' => 'NewSecret@123',
                'password_confirmation' => 'NewSecret@123',
            ])
            ->assertOk();

        $this->assertTrue(Hash::check('NewSecret@123', $resident->fresh()->password));
    }

    public function test_change_password_requires_correct_current_password(): void
    {
        $resident = $this->resident();

        $this->actingAs($resident, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/settings/password', [
                'current_password' => 'WrongPass@123',
                'password' => 'NewSecret@123',
                'password_confirmation' => 'NewSecret@123',
            ])
            ->assertStatus(422);
    }

    public function test_resident_can_update_preferences(): void
    {
        $resident = $this->resident();

        $this->actingAs($resident, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->putJson('/api/settings/preferences', [
                'theme' => 'dark',
                'density' => 'compact',
                'font_scale' => 'large',
                'notify_announcements' => false,
                'notify_payments' => true,
                'notify_issues' => true,
                'notify_messages' => false,
                'email_notifications' => true,
            ])
            ->assertOk()
            ->assertJsonPath('preferences.theme', 'dark')
            ->assertJsonPath('preferences.notify_announcements', false);

        $this->assertDatabaseHas('user_settings', [
            'user_type' => 'resident', 'user_id' => $resident->id, 'theme' => 'dark', 'density' => 'compact',
        ]);
    }

    public function test_settings_endpoint_is_strict_model_safe(): void
    {
        // Dev runs with Model::shouldBeStrict(); tests do not. Reproduce the missing-attribute
        // guard here so accessing e.g. role_description on Admin/Resident fails the suite.
        Model::preventAccessingMissingAttributes(true);
        try {
            $this->withSession(['admin_id' => $this->admin()->id])
                ->withHeaders(['Accept' => 'application/json'])
                ->getJson('/api/settings')
                ->assertOk()
                ->assertJsonPath('user.type', 'admin');

            $this->flushSession();

            $this->actingAs($this->resident('strict@example.test')->refresh(), 'resident')
                ->withHeaders(['Accept' => 'application/json'])
                ->getJson('/api/settings')
                ->assertOk()
                ->assertJsonPath('user.type', 'resident');
        } finally {
            Model::preventAccessingMissingAttributes(false);
        }
    }

    public function test_admin_settings_are_strict_mode_safe(): void
    {
        // Local runs Model::shouldBeStrict(); the test env does not, which is how
        // "admins have no contact_number column" slipped through as a 500.
        Model::preventAccessingMissingAttributes(true);
        Model::preventSilentlyDiscardingAttributes(true);

        try {
            $admin = $this->admin();

            $this->withSession(['admin_id' => $admin->id])
                ->withHeaders(['Accept' => 'application/json'])
                ->getJson('/api/settings')
                ->assertOk()
                ->assertJsonPath('user.contact_number', null);

            $this->withSession(['admin_id' => $admin->id])
                ->withHeaders(['Accept' => 'application/json'])
                ->putJson('/api/settings/profile', [
                    'name' => 'Strict Admin', 'email' => 'admin@example.test', 'contact_number' => '0900',
                ])
                ->assertOk();
        } finally {
            Model::preventAccessingMissingAttributes(false);
            Model::preventSilentlyDiscardingAttributes(false);
        }
    }

    public function test_admin_can_read_and_update_settings(): void
    {
        $admin = $this->admin();

        $this->withSession(['admin_id' => $admin->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/settings')
            ->assertOk()
            ->assertJsonPath('user.type', 'admin')
            ->assertJsonPath('user.name', 'Test Admin');

        $this->withSession(['admin_id' => $admin->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->putJson('/api/settings/profile', [
                'name' => 'Head Admin', 'email' => 'admin@example.test', 'contact_number' => '0900',
            ])
            ->assertOk();

        $this->assertDatabaseHas('admins', ['id' => $admin->id, 'name' => 'Head Admin']);
    }
}
