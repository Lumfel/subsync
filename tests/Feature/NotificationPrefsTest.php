<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Resident;
use App\Models\UserSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationPrefsTest extends TestCase
{
    use RefreshDatabase;

    private function resident(): Resident
    {
        return Resident::create([
            'name' => 'Test Resident', 'email' => 'resident@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
    }

    private function announcement(): Announcement
    {
        return Announcement::create([
            'title' => 'Fresh Announcement', 'content' => 'Something happened.',
            'tag' => 'notice', 'status' => 'Active', 'priority' => 'Normal', 'target' => 'All Residents',
        ]);
    }

    public function test_resident_receives_announcement_alerts_by_default(): void
    {
        $this->announcement();
        $resident = $this->resident();

        $res = $this->actingAs($resident, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/alerts');

        $res->assertOk();
        $this->assertTrue(collect($res->json('alerts'))->contains(fn ($a) => str_starts_with($a['id'], 'announcement')));
    }

    public function test_announcement_alerts_are_hidden_when_disabled(): void
    {
        $this->announcement();
        $resident = $this->resident();

        UserSetting::create([
            'user_type' => 'resident', 'user_id' => $resident->id,
            'theme' => 'light', 'density' => 'comfortable', 'font_scale' => 'default',
            'notify_announcements' => false,
        ]);

        $res = $this->actingAs($resident, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/alerts');

        $res->assertOk();
        $this->assertFalse(collect($res->json('alerts'))->contains(fn ($a) => str_starts_with($a['id'], 'announcement')));
    }
}
