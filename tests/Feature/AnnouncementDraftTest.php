<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Household;
use App\Models\Officer;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AnnouncementDraftTest extends TestCase
{
    use RefreshDatabase;

    private function officer(): Officer
    {
        return Officer::create([
            'name' => 'Officer', 'email' => 'o@x.com',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
    }

    private function resident(): Resident
    {
        $house = Household::create(['block_lot_number' => 'Block 1 Lot 1']);

        return Resident::create([
            'name' => 'Resident', 'email' => 'r@x.com',
            'password' => 'Secret@12345', 'status' => 'Active', 'house_id' => $house->id,
        ]);
    }

    public function test_officer_can_save_a_draft_and_residents_never_see_it(): void
    {
        $officer = $this->officer();
        $resident = $this->resident();

        $this->actingAs($officer, 'officer')
            ->postJson('/api/announcements', ['title' => 'Draft Notice', 'content' => 'WIP', 'status' => 'Draft'])
            ->assertOk()
            ->assertJsonPath('announcement.status', 'Draft');

        $this->assertDatabaseHas('announcements', ['title' => 'Draft Notice', 'status' => 'Draft']);

        Cache::flush();
        $res = $this->actingAs($resident, 'resident')->getJson('/api/announcements')->assertOk();
        $this->assertNotContains('Draft Notice', array_column($res->json(), 'title'));
    }

    public function test_staff_can_list_drafts(): void
    {
        Announcement::create(['title' => 'Draft A', 'content' => 'x', 'status' => 'Draft']);

        Cache::flush();
        $res = $this->actingAs($this->officer(), 'officer')
            ->getJson('/api/announcements?status=Draft')->assertOk();

        $this->assertContains('Draft A', array_column($res->json(), 'title'));
    }

    public function test_publishing_a_draft_puts_it_on_the_live_feed(): void
    {
        $officer = $this->officer();
        $resident = $this->resident();
        $draft = Announcement::create(['title' => 'Publish Me', 'content' => 'x', 'status' => 'Draft']);

        $this->actingAs($officer, 'officer')
            ->patchJson('/api/announcements/'.$draft->id.'/publish')
            ->assertOk()
            ->assertJson(['status' => 'Active']);

        Cache::flush();
        $res = $this->actingAs($resident, 'resident')->getJson('/api/announcements')->assertOk();
        $this->assertContains('Publish Me', array_column($res->json(), 'title'));
    }

    public function test_residents_cannot_publish(): void
    {
        $draft = Announcement::create(['title' => 'Nope', 'content' => 'x', 'status' => 'Draft']);

        $this->actingAs($this->resident(), 'resident')
            ->patchJson('/api/announcements/'.$draft->id.'/publish')
            ->assertStatus(401);
    }
}
