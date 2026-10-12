<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Event;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventRsvpTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create(['name' => 'A', 'email' => 'a@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    private function resident(): Resident
    {
        return Resident::create(['name' => 'R', 'email' => 'r@x.com', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    private function as(string $role, $user): void
    {
        $this->app['auth']->forgetGuards();
        $this->flushSession();
        $this->actingAs($user, $role);
    }

    public function test_staff_can_create_an_event(): void
    {
        $this->withSession(['admin_id' => $this->admin()->id, 'admin_name' => 'A'])
            ->postJson('/api/events', [
                'title' => 'General Assembly',
                'location' => 'Clubhouse',
                'starts_at' => now()->addDays(3)->toDateTimeString(),
            ])
            ->assertOk();

        $this->assertDatabaseHas('events', ['title' => 'General Assembly', 'location' => 'Clubhouse']);
    }

    public function test_resident_can_list_events_and_rsvp(): void
    {
        $event = Event::create(['title' => 'Clean-up Drive', 'starts_at' => now()->addDays(2), 'location' => 'Park']);
        $resident = $this->resident();

        $this->as('resident', $resident);
        $this->assertContains($event->id, array_column($this->getJson('/api/events')->assertOk()->json(), 'id'));

        $this->postJson('/api/events/'.$event->id.'/rsvp', ['status' => 'going'])->assertOk();
        $this->postJson('/api/events/'.$event->id.'/rsvp', ['status' => 'maybe'])->assertOk();

        $row = collect($this->getJson('/api/events')->json())->firstWhere('id', $event->id);
        $this->assertSame('maybe', $row['my_status']);
        $this->assertSame(1, $row['maybe']);
        $this->assertSame(0, $row['going']);
    }

    public function test_past_events_are_not_listed(): void
    {
        Event::create(['title' => 'Old Meeting', 'starts_at' => now()->subDays(5)]);

        $this->as('resident', $this->resident());
        $this->assertStringNotContainsString('Old Meeting', $this->getJson('/api/events')->getContent());
    }

    public function test_resident_cannot_create_events(): void
    {
        $this->actingAs($this->resident(), 'resident')
            ->postJson('/api/events', ['title' => 'X', 'starts_at' => now()->toDateTimeString()])
            ->assertStatus(401);
    }
}
