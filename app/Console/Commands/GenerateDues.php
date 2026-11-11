<?php

namespace App\Console\Commands;

use App\Models\Assessment;
use App\Models\FinancialRecord;
use App\Models\Resident;
use App\Support\Ledger;
use Illuminate\Console\Command;

/**
 * Turns the active assessment(dues) schedule into ledger charges for the
 * current period. Idempotent: re-running never double-charges.
 */
class GenerateDues extends Command
{
    protected $signature = 'assessments:generate-dues {--period= : Override the period (YYYY-MM)}';

    protected $description = 'Generate dues/assessment charges for the current period (never double-charges).';

    public function handle(): int
    {
        $period = (string) ($this->option('period') ?: now()->format('Y-m'));

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
            $this->error("Invalid period '{$period}'. Expected YYYY-MM (e.g. 2026-10).");

            return self::FAILURE;
        }

        $month = (int) substr($period, 5, 2);

        $generated = 0;

        foreach (Assessment::where('is_active', true)->get() as $assessment) {
            if (! $this->appliesThisPeriod($assessment, $period, $month)) {
                continue;
            }

            $dueDate = $this->dueDateFor($assessment, $period);

            // Prefetch everyone already charged this period (one query, not one per resident).
            $alreadyCharged = FinancialRecord::where('assessment_id', $assessment->id)
                ->where('period', $period)
                ->pluck('resident_id')
                ->flip();

            foreach (Resident::where('status', 'Active')->pluck('id') as $residentId) {
                if ($alreadyCharged->has($residentId)) {
                    continue;
                }

                FinancialRecord::create([
                    'resident_id' => $residentId,
                    'record_type' => 'Due',
                    'description' => $assessment->name.' — '.$period,
                    'amount' => $assessment->amount,
                    'record_date' => now()->toDateString(),
                    'due_date' => $dueDate,
                    'assessment_id' => $assessment->id,
                    'period' => $period,
                    'source' => 'assessment',
                ]);

                Ledger::reconcile((int) $residentId);
                $generated++;
            }
        }

        $this->info("Generated {$generated} due charge(s) for {$period}.");

        return self::SUCCESS;
    }

    private function appliesThisPeriod(Assessment $a, string $period, int $month): bool
    {
        if ($a->start_date && $a->start_date->format('Y-m') > $period) {
            return false;
        }
        if ($a->end_date && $a->end_date->format('Y-m') < $period) {
            return false;
        }

        return match ($a->frequency) {
            'monthly' => true,
            'quarterly' => in_array($month, [1, 4, 7, 10], true),
            'annual' => $month === (int) ($a->start_date?->month ?? 0),
            'one_time' => $a->start_date?->format('Y-m') === $period,
            default => false,
        };
    }

    private function dueDateFor(Assessment $a, string $period): string
    {
        [$year, $mon] = explode('-', $period);
        $day = $a->due_day ?: ($a->start_date?->day ?? 1);
        $day = min($day, (int) date('t', strtotime("{$year}-{$mon}-01")));

        return sprintf('%04d-%02d-%02d', (int) $year, (int) $mon, $day);
    }
}
