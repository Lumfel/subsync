<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Assessment;
use App\Models\Budget;
use App\Models\Facility;
use App\Models\FinancialRecord;
use App\Models\HoaExpense;
use App\Models\HoaPolicy;
use App\Models\Officer;
use App\Models\OfficerFile;
use App\Models\Report;
use App\Models\ReportTemplate;
use App\Models\Resident;
use App\Support\Actor;

/**
 * Records create/update/delete of key models into the activity log.
 *
 * Registered for: Announcement, HoaPolicy, HoaExpense, FinancialRecord,
 * ReportTemplate, Report, Assessment, Budget, Resident, Officer,
 * OfficerFile, Facility. (IssueReport keeps its own dedicated audit trail.)
 *
 * Never throws: a logging failure must not break the request.
 */
class ActivityLogObserver
{
    private const IGNORED_FIELDS = [
        'updated_at', 'created_at', 'remember_token', 'password', 'last_seen_at',
    ];

    public function created($model): void
    {
        $this->record($model, 'created');
    }

    public function updated($model): void
    {
        $fields = array_keys(array_diff_key($model->getDirty(), array_flip(self::IGNORED_FIELDS)));
        if (empty($fields)) {
            return;
        }

        $this->record($model, 'updated', $fields);
    }

    public function deleted($model): void
    {
        $this->record($model, 'deleted');
    }

    protected function record($model, string $action, array $fields = []): void
    {
        try {
            [$actorType, $actorId, $actorName] = $this->actor();

            $description = ucfirst($action).' '.$this->label($model);
            if ($fields) {
                $description .= ' — '.implode(', ', array_slice($fields, 0, 4));
            }

            ActivityLog::create([
                'actor_type' => $actorType,
                'actor_id' => $actorId,
                'actor_name' => $actorName,
                'action' => $action,
                'subject_type' => class_basename($model),
                'subject_id' => $model->getKey(),
                'description' => mb_substr($description, 0, 250),
                'meta' => $fields ? ['fields' => $fields] : null,
            ]);
        } catch (\Throwable $e) {
            // Never let auditing break the request.
        }
    }

    /** @return array{0:?string,1:?int,2:string} */
    protected function actor(): array
    {
        try {
            $actor = Actor::current();
            if ($actor['type']) {
                return [$actor['type'], $actor['id'], (string) $actor['name']];
            }
        } catch (\Throwable $e) {
            // Console / queue context — fall through to system.
        }

        return ['system', null, 'System'];
    }

    protected function label($model): string
    {
        $name = match (true) {
            $model instanceof Announcement => $model->title,
            $model instanceof HoaPolicy => $model->title,
            $model instanceof HoaExpense => $model->title,
            $model instanceof ReportTemplate => $model->name,
            $model instanceof Report => $model->reference_no ?: $model->subject,
            $model instanceof Assessment => $model->name,
            $model instanceof Budget => $model->category,
            $model instanceof Resident => trim(($model->name ?? '').($model->email ? ' ('.$model->email.')' : '')),
            $model instanceof Officer => trim(($model->name ?? '').($model->email ? ' ('.$model->email.')' : '')),
            $model instanceof OfficerFile => $model->original_name ?: 'file #'.$model->getKey(),
            $model instanceof FinancialRecord => trim(($model->record_type ?? '').' '.($model->description ?? '')) ?: 'record',
            $model instanceof Facility => $model->name,
            default => strtolower(class_basename($model)),
        };

        $label = strtolower(class_basename($model));
        $name = trim((string) $name);

        return $name !== '' ? "{$label}: {$name}" : $label;
    }
}
