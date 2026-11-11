<?php

namespace App\Observers;

use App\Events\SubSyncEvent;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\HoaExpense;
use App\Models\HoaPolicy;
use App\Models\IssueReport;
use App\Models\Message;
use App\Models\Officer;
use App\Models\OfficerFile;
use App\Models\PaymentSubmission;
use App\Models\Recommendation;
use App\Models\Resident;
use App\Models\SseEvent;

class SsePublishingObserver
{
    public function created($model): void
    {
        $this->publish($model, 'created');
    }

    public function updated($model): void
    {
        $this->publish($model, 'updated');
    }

    public function deleted($model): void
    {
        $this->publish($model, 'deleted');
    }

    /**
     * Map a model instance to a compact SSE event and push it onto the bus.
     */
    protected function publish($model, string $action): void
    {
        $channel = $model->getTable();

        [$event, $data] = $this->payloadFor($model, $action);

        SseEvent::emit($channel, $event, $data);

        // Also push over WebSockets (Laravel Reverb) for true real-time. If the
        // Reverb server is offline this must not break the request — SSE still covers it.
        try {
            broadcast(new SubSyncEvent($event, $data));
        } catch (\Throwable $e) {
            // Reverb unavailable — ignore, SSE remains the fallback.
        }
    }

    protected function payloadFor($model, string $action): array
    {
        return match (true) {
            $model instanceof Announcement => [
                'announcement',
                [
                    'latestId' => $model->id,
                    'action' => $action,
                    'title' => $model->title,
                ],
            ],
            $model instanceof HoaPolicy => [
                'policy',
                [
                    'latestId' => $model->id,
                    'action' => $action,
                    'title' => $model->title,
                ],
            ],
            $model instanceof IssueReport => [
                'issue',
                [
                    'latestId' => $model->id,
                    'action' => $action,
                    'isNew' => $action === 'created',
                    'title' => $model->title,
                    'priority' => $model->priority,
                    'status' => $model->status,
                ],
            ],
            $model instanceof HoaExpense => [
                'hoa-expense',
                [
                    'latestId' => $model->id,
                    'action' => $action,
                    'title' => $model->title,
                    'amount' => $model->amount,
                ],
            ],
            $model instanceof Message => [
                'message',
                [
                    'latestId' => $model->id,
                    'action' => $action,
                    'conversationId' => $model->conversation_id,
                    'senderType' => $model->sender_type,
                    // Identification without PII: clients resolve the display name
                    // from their own conversation list. Never the message content.
                    'senderId' => $model->resident_id ?? $model->officer_id,
                ],
            ],
            // Approvals & membership - admins watch the pending queue live.
            // No names on the bus: clients refresh through authorized endpoints.
            $model instanceof Resident => [
                'resident',
                [
                    'latestId' => $model->id,
                    'action' => $action,
                    'status' => $model->status,
                ],
            ],
            $model instanceof Officer => [
                'officer',
                [
                    'latestId' => $model->id,
                    'action' => $action,
                    'status' => $model->status,
                ],
            ],
            // GCash payments - the staff queue and the resident's own status.
            $model instanceof PaymentSubmission => [
                'payment',
                [
                    'latestId' => $model->id,
                    'action' => $action,
                    'residentId' => $model->resident_id,
                    'status' => $model->status,
                ],
            ],
            // Suggestions - resident submits, admin decides.
            $model instanceof Recommendation => [
                'recommendation',
                [
                    'latestId' => $model->id,
                    'action' => $action,
                    'residentId' => $model->resident_id,
                    'status' => $model->status,
                ],
            ],
            // Community calendar + RSVP counts.
            $model instanceof Event => [
                'event',
                [
                    'latestId' => $model->id,
                    'action' => $action,
                    'title' => $model->title,
                ],
            ],
            $model instanceof EventRsvp => [
                'rsvp',
                [
                    'latestId' => $model->id,
                    'action' => $action,
                    'eventId' => $model->event_id,
                    'status' => $model->status,
                ],
            ],
            // Documents officers send to the admin desk.
            $model instanceof OfficerFile => [
                'officer-file',
                [
                    'latestId' => $model->id,
                    'action' => $action,
                    'name' => $model->original_name,
                ],
            ],
            default => ['update', ['model' => $model->getTable(), 'action' => $action]],
        };
    }
}
