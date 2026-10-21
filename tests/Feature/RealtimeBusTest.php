<?php

namespace Tests\Feature;

use App\Events\SubSyncEvent;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\Officer;
use App\Models\OfficerFile;
use App\Models\PaymentSubmission;
use App\Models\Recommendation;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
                && $e->payload['name'] === 'Juan Dela Cruz'
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
                && $e->payload['name'] === 'Off One'
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
}
