<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Household;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivacyComplianceTest extends TestCase
{
    use RefreshDatabase;

    private function resident(string $email = 'resident@example.test'): Resident
    {
        $house = Household::create(['block_lot_number' => 'Block 1 Lot 1']);

        return Resident::create([
            'name' => 'Resident', 'email' => $email,
            'password' => 'Secret@12345', 'status' => 'Active', 'house_id' => $house->id,
        ]);
    }

    public function test_privacy_notice_is_publicly_available(): void
    {
        $this->get('/privacy')->assertOk()->assertSee('Data Privacy Notice');
    }

    public function test_registration_requires_consent(): void
    {
        $this->withHeaders(['Accept' => 'application/json'])
            ->post('/register', [
                'role' => 'resident', 'name' => 'No Consent',
                'email' => 'noconsent@example.test', 'password' => 'Secret@12345',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('consent');

        $this->assertDatabaseMissing('residents', ['email' => 'noconsent@example.test']);
    }

    public function test_registration_with_consent_succeeds(): void
    {
        $this->withHeaders(['Accept' => 'application/json'])
            ->post('/register', [
                'role' => 'resident', 'name' => 'With Consent',
                'email' => 'consent@example.test', 'password' => 'Secret@12345',
                'consent' => true,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('residents', ['email' => 'consent@example.test', 'status' => 'Pending']);
    }

    public function test_resident_can_export_their_personal_data(): void
    {
        $resident = $this->resident();

        $res = $this->actingAs($resident, 'resident')
            ->get('/api/settings/export')
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename="subsync-my-data.json"');

        $data = $res->json();
        $this->assertSame('resident', $data['portal']);
        $this->assertSame($resident->email, $data['profile']['email']);
        $this->assertArrayNotHasKey('password', $data['profile']);
        $this->assertSame('Block 1 Lot 1', $data['household']['block_lot_number']);
        $this->assertArrayHasKey('issue_reports', $data);
        $this->assertArrayHasKey('payment_submissions', $data);
    }

    public function test_resident_can_file_an_erasure_request(): void
    {
        $resident = $this->resident();

        $this->actingAs($resident, 'resident')
            ->postJson('/api/settings/erasure-request', ['reason' => 'Moving out'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'erasure_request',
            'actor_id' => $resident->id,
            'actor_type' => 'Resident',
        ]);
    }

    public function test_activity_logs_are_pruned_by_retention_window(): void
    {
        $old = ActivityLog::create([
            'actor_type' => 'System', 'action' => 'old', 'subject_type' => 'Test',
            'description' => 'very old entry',
        ]);
        $old->forceFill(['created_at' => now()->subDays(400)])->save();

        $fresh = ActivityLog::create([
            'actor_type' => 'System', 'action' => 'fresh', 'subject_type' => 'Test',
            'description' => 'recent entry',
        ]);

        $this->artisan('subsync:prune-activity-logs --days=180')->assertSuccessful();

        $this->assertDatabaseMissing('activity_logs', ['id' => $old->id]);
        $this->assertDatabaseHas('activity_logs', ['id' => $fresh->id]);
    }
}
