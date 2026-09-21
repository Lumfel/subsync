<?php

namespace App\Observers;

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
    }

    protected function payloadFor($model, string $action): array
    {
        return match (true) {
            $model instanceof \App\Models\Announcement => [
                'announcement',
                [
                    'latestId' => $model->id,
                    'action'   => $action,
                    'title'    => $model->title,
                ],
            ],
            $model instanceof \App\Models\HoaPolicy => [
                'policy',
                [
                    'latestId' => $model->id,
                    'action'   => $action,
                    'title'    => $model->title,
                ],
            ],
            $model instanceof \App\Models\IssueReport => [
                'issue',
                [
                    'latestId' => $model->id,
                    'action'   => $action,
                    'isNew'    => $action === 'created',
                    'title'    => $model->title,
                    'priority' => $model->priority,
                    'status'   => $model->status,
                ],
            ],
            $model instanceof \App\Models\HoaExpense => [
                'hoa-expense',
                [
                    'latestId' => $model->id,
                    'action'   => $action,
                    'title'    => $model->title,
                    'amount'   => $model->amount,
                ],
            ],
            default => ['update', ['model' => $model->getTable(), 'action' => $action]],
        };
    }
}