<?php

namespace Tests\Feature;

use App\Events\SubSyncEvent;
use App\Models\Admin;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\Officer;
use App\Models\OfficerFile;
use App\Models\PaymentSubmission;
use App\Models\Recommendation;
use App\Models\Resident;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event as EventBus;
use Tests\TestCase;

/**
 * Guards the realtime bus for everything beyond announcements/issues/policies/
 * expenses/messages: approvals, GCash payments, suggestions, the calendar and
 * officer documents all have to reach clients live (and named).
 */
class RealtimeBusTest extends TestCase
{
    use RefreshDatabase;

    private function resident(array $attrs = []): Resident
    {
        return Resident::create(array_merge([
            'name' => 'Juan Dela Cruz', 'email' => 'juan@example.test',
            'password' => 'Secret@12345', 'status' => 'Pending',
        ], $attrs));
    }

    public function test_resident_approval_publishes_who_and_status(): void
    {
        EventBus::fake([SubSyncEvent::class]);

        $resident = $this->resident();

        EventBus::assertDispatched(
            fn (SubSyncEvent $e) => $e->eventName === 'resident'
                && ! array_key_exists('name', $e->payload) // no PII on the bus
                && $e->payload['status'] === 'Pending'
        );

        $resident->update(['status' => 'Active']);

        EventBus::assertDispatched(
            fn (SubSyncEvent $e) => $e->eventName === 'resident'
                && $e->payload['latestId'] === $resident->id
                && $e->payload['status'] === 'Active'
        );

        $this->assertDatabaseHas('sse_events', ['channel' => 'residents', 'event' => 'resident']);
    }

    public function test_officer_registration_and_approval_publish(): void
    {
        EventBus::fake([SubSyncEvent::class]);

        $officer = Officer::create([
            'name' => 'Off One', 'email' => 'off@example.test',
            'password' => 'Secret@12345', 'status' => 'Pending',
        ]);

        EventBus::assertDispatched(
            fn (SubSyncEvent $e) => $e->eventName === 'officer'
                && ! array_key_exists('name', $e->payload) // no PII on the bus
                && $e->payload['status'] === 'Pending'
        );

        $officer->update(['status' => 'Active']);

        EventBus::assertDispatched(
            fn (SubSyncEvent $e) => $e->eventName === 'officer' && $e->payload['status'] === 'Active'
        );
    }

    public function test_payments_suggestions_calendar_and_files_publish(): void
    {
        EventBus::fake([SubSyncEvent::class]);

        $resident = $this->resident(['status' => 'Active']);
        $officer = Officer::create([
            'name' => 'Off One', 'email' => 'off@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);

        $payment = PaymentSubmission::create([
            'resident_id' => $resident->id, 'amount' => 500, 'method' => 'gcash', 'status' => 'Pending',
        ]);
        EventBus::assertDispatched(
            fn (SubSyncEvent $e) => $e->eventName === 'payment'
                && $e->payload['residentId'] === $resident->id
                && $e->payload['status'] === 'Pending'
        );

        $payment->update(['status' => 'Validated']);
        EventBus::assertDispatched(
            fn (SubSyncEvent $e) => $e->eventName === 'payment' && $e->payload['status'] === 'Validated'
        );

        Recommendation::create([
            'resident_id' => $resident->id, 'title' => 'Add street lights',
            'description' => 'Dark corner near the gate.', 'status' => 'Pending',
        ]);
        EventBus::assertDispatched(fn (SubSyncEvent $e) => $e->eventName === 'recommendation');

        $event = Event::create(['title' => 'Clean-up drive', 'starts_at' => now()->addWeek()]);
        EventBus::assertDispatched(
            fn (SubSyncEvent $e) => $e->eventName === 'event' && $e->payload['title'] === 'Clean-up drive'
        );

        EventRsvp::create(['event_id' => $event->id, 'resident_id' => $resident->id, 'status' => 'going']);
        EventBus::assertDispatched(
            fn (SubSyncEvent $e) => $e->eventName === 'rsvp' && $e->payload['eventId'] === $event->id
        );

        OfficerFile::create([
            'officer_id' => $officer->id, 'officer_name' => 'Off One',
            'original_name' => 'monthly-report.xlsx', 'category' => 'Report', 'size_bytes' => 1024,
        ]);
        EventBus::assertDispatched(
            fn (SubSyncEvent $e) => $e->eventName === 'officer-file' && $e->payload['name'] === 'monthly-report.xlsx'
        );

        foreach ([
            ['payment_submissions', 'payment'],
            ['recommendations', 'recommendation'],
            ['events', 'event'],
            ['event_rsvps', 'rsvp'],
            ['officer_files', 'officer-file'],
        ] as [$channel, $eventName]) {
            $this->assertDatabaseHas('sse_events', ['channel' => $channel, 'event' => $eventName]);
        }
    }

    public function test_clients_subscribe_to_the_new_events(): void
    {
        $admin = file_get_contents(public_path('js/admin-dashboard.js'));
        $this->assertStringContainsString(".listen('.resident'", $admin);
        $this->assertStringContainsString(".listen('.officer'", $admin);
        $this->assertStringContainsString(".listen('.payment'", $admin);
        $this->assertStringContainsString(".listen('.officer-file'", $admin);
        $this->assertStringContainsString('!_rtWsReady) pollOfficerFiles();', $admin, 'the officer-files poll must be socket-gated');

        $residents = file_get_contents(public_path('js/residents.js'));
        $this->assertStringContainsString(".listen('.payment'", $residents);
        $this->assertStringContainsString(".listen('.recommendation'", $residents);
        $this->assertStringContainsString(".listen('.event'", $residents);
        $this->assertStringContainsString(".listen('.rsvp'", $residents);
        $this->assertStringContainsString('subsync:refresh-alerts', $residents);

        $officer = file_get_contents(public_path('js/officer-portal.js'));
        $this->assertStringContainsString(".listen('.event'", $officer);
        $this->assertStringContainsString('subsync:refresh-alerts', $officer);

        $bell = file_get_contents(public_path('js/notifications.js'));
        $this->assertStringContainsString('subsync:refresh-alerts', $bell);
    }

    public function test_realtime_bus_is_a_private_channel(): void
    {
        $event = new SubSyncEvent('announcement', ['x' => 1]);
        $channels = $event->broadcastOn();

        $this->assertInstanceOf(PrivateChannel::class, $channels[0]);
        $this->assertSame('private-subsync', (string) $channels[0]);
    }

    public function test_broadcasting_auth_requires_a_portal_session(): void
    {
        // phpunit runs with BROADCAST_CONNECTION=null, whose broadcaster skips
        // authorization entirely — switch to the real (Reverb) broadcaster so
        // the channel checks actually execute (signing is local, no network).
        // Channel definitions are registered on whichever driver is default at
        // boot, so they must be replayed onto the swapped driver.
        config(['broadcasting.default' => 'reverb']);
        Broadcast::purge();
        require base_path('routes/channels.php');

        $resident = $this->resident(['status' => 'Active']);

        // Guests cannot subscribe.
        $this->postJson('/broadcasting/auth', ['channel_name' => 'private-subsync', 'socket_id' => '1.2'])
            ->assertStatus(403);

        // Residents (guard-based session) are authorized.
        $this->actingAs($resident, 'resident')
            ->postJson('/broadcasting/auth', ['channel_name' => 'private-subsync', 'socket_id' => '1.2'])
            ->assertOk()
            ->assertJsonStructure(['auth']);

        // Admins authenticate via the admin guard (set by adminLogin).
        $this->app['auth']->forgetGuards();
        $this->flushSession();

        $admin = Admin::create([
            'name' => 'Boss', 'email' => 'boss@example.test',
            'password' => 'Secret@12345', 'status' => 'Active',
        ]);
        $this->withSession(['admin_id' => $admin->id]);
        $this->app['auth']->guard('admin')->login($admin);

        $this->postJson('/broadcasting/auth', ['channel_name' => 'private-subsync', 'socket_id' => '1.2'])
            ->assertOk()
            ->assertJsonStructure(['auth']);
    }
}
