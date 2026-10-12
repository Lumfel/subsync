<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Announcement;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Test Admin', 'email' => 'admin@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
    }

    private function resident(string $name, string $email): Resident
    {
        return Resident::create([
            'name' => $name, 'email' => $email, 'password' => 'Secret@12345', 'status' => 'Active',
        ]);
    }

    public function test_empty_query_returns_no_results(): void
    {
        $this->actingAs($this->resident('A', 'a@example.test'), 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/search?q=')
            ->assertOk()
            ->assertJsonPath('results', []);
    }

    public function test_staff_search_finds_residents(): void
    {
        $this->resident('Juan Unique Surname', 'juan@example.test');

        $res = $this->withSession(['admin_id' => $this->admin()->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/search?q=Unique');

        $res->assertOk();
        $this->assertTrue(collect($res->json('results'))->contains(fn ($r) => $r['tag'] === 'Resident'));
        $this->assertTrue(collect($res->json('results'))->contains(fn ($r) => $r['tag'] === 'Resident'));
    }

    public function test_resident_search_finds_active_announcements_only(): void
    {
        Announcement::create([
            'title' => 'Water Interruption Notice', 'content' => 'Maintenance on Saturday.',
            'tag' => 'notice', 'status' => 'Active', 'priority' => 'Normal', 'target' => 'All Residents',
        ]);
        Announcement::create([
            'title' => 'Archived Water Note', 'content' => 'Old.',
            'tag' => 'notice', 'status' => 'Archived', 'priority' => 'Normal', 'target' => 'All Residents',
        ]);

        $res = $this->actingAs($this->resident('Me', 'me@example.test'), 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/search?q=Water');

        $res->assertOk();
        $labels = collect($res->json('results'))->pluck('label');
        $this->assertTrue($labels->contains('Water Interruption Notice'));
        $this->assertFalse($labels->contains('Archived Water Note'));
    }

    public function test_resident_search_does_not_expose_residents(): void
    {
        $this->resident('Secret Person', 'secret@example.test');

        $res = $this->actingAs($this->resident('Me', 'me@example.test'), 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/search?q=Secret');

        $res->assertOk();
        $this->assertFalse(collect($res->json('results'))->contains(fn ($r) => $r['tag'] === 'Resident'));
    }
}
