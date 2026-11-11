<?php

namespace App\Support;

use App\Models\FinancialRecord;
use App\Models\Resident;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The single source of truth for a resident's money:
 *   balance = Σ charges (Due, Penalty) − Σ payments/credits.
 * `residents.current_balance` is kept as a cached projection via reconcile().
 */
class Ledger
{
    public static function balanceFor(int $residentId): float
    {
        return round((float) FinancialRecord::where('resident_id', $residentId)
            ->get(['record_type', 'amount'])
            ->sum(fn ($r) => $r->signed_amount), 2);
    }

    /** Recompute and store the cached balance; returns the new value. */
    public static function reconcile(int $residentId): float
    {
        $balance = self::balanceFor($residentId);
        Resident::whereKey($residentId)->update(['current_balance' => $balance]);

        return $balance;
    }

    /** Full statement: running balance + totals + line items. */
    public static function statement(int $residentId): array
    {
        $records = FinancialRecord::where('resident_id', $residentId)
            ->orderBy('record_date')->orderBy('id')->get();

        $running = 0.0;
        $charges = 0.0;
        $payments = 0.0;

        $entries = $records->map(function (FinancialRecord $r) use (&$running, &$charges, &$payments) {
            $signed = $r->signed_amount;
            $running += $signed;
            if ($signed >= 0) {
                $charges += $signed;
            } else {
                $payments += -$signed;
            }

            return [
                'id' => $r->id,
                'date' => $r->record_date?->format('M d, Y'),
                'type' => $r->record_type,
                'description' => $r->description,
                'amount' => $signed,
                'charge' => $signed > 0 ? (float) $r->amount : 0.0,
                'payment' => $signed < 0 ? (float) $r->amount : 0.0,
                'balance' => round($running, 2),
                'period' => $r->period,
                'due_date' => $r->due_date?->format('Y-m-d'),
            ];
        })->values();

        return [
            'balance' => round($running, 2),
            'total_charges' => round($charges, 2),
            'total_payments' => round($payments, 2),
            'entries' => $entries,
        ];
    }

    /**
     * Outstanding charges bucketed by age (FIFO: payments/credits settle the
     * oldest charges first).
     */
    public static function agingFor(int $residentId): array
    {
        $records = FinancialRecord::where('resident_id', $residentId)
            ->orderBy('id')->get(['record_type', 'amount', 'due_date', 'record_date']);

        return self::agingFromRecords($records);
    }

    /**
     * Aging for many residents in a single query (avoids the per-resident N+1).
     *
     * @param  iterable<int>  $residentIds
     * @return array<int, array{buckets: array<string, float>, total: float, credit: float}>
     */
    public static function agingForMany(iterable $residentIds): array
    {
        $ids = collect($residentIds)->map(fn ($id) => (int) $id)->unique()->values();
        if ($ids->isEmpty()) {
            return [];
        }

        $grouped = FinancialRecord::whereIn('resident_id', $ids)
            ->orderBy('id')
            ->get(['resident_id', 'record_type', 'amount', 'due_date', 'record_date'])
            ->groupBy('resident_id');

        $out = [];
        foreach ($ids as $id) {
            $out[$id] = self::agingFromRecords($grouped->get($id, collect()));
        }

        return $out;
    }

    /** @param  Collection<int, FinancialRecord>  $records */
    private static function agingFromRecords($records): array
    {
        $charges = [];
        $credit = 0.0;

        foreach ($records as $r) {
            $signed = $r->signed_amount;
            if ($signed > 0) {
                $charges[] = [
                    'amount' => (float) $r->amount,
                    'date' => $r->due_date ?? $r->record_date,
                    'remaining' => (float) $r->amount,
                ];
            } else {
                $credit += -$signed;
            }
        }

        usort($charges, fn ($a, $b) => (strtotime((string) ($a['date'] ?? '')) ?: 0) <=> (strtotime((string) ($b['date'] ?? '')) ?: 0));

        foreach ($charges as &$c) {
            if ($credit <= 0) {
                break;
            }
            $apply = min($credit, $c['remaining']);
            $c['remaining'] -= $apply;
            $credit -= $apply;
        }
        unset($c);

        $today = Carbon::today();
        $buckets = ['current' => 0.0, 'd1_30' => 0.0, 'd31_60' => 0.0, 'd61_90' => 0.0, 'd90_plus' => 0.0];

        foreach ($charges as $c) {
            if ($c['remaining'] <= 0) {
                continue;
            }
            $due = $c['date'] ? Carbon::parse($c['date']) : $today;
            $daysPast = $today->greaterThan($due) ? (int) $due->diffInDays($today) : 0;

            if ($daysPast <= 0) {
                $buckets['current'] += $c['remaining'];
            } elseif ($daysPast <= 30) {
                $buckets['d1_30'] += $c['remaining'];
            } elseif ($daysPast <= 60) {
                $buckets['d31_60'] += $c['remaining'];
            } elseif ($daysPast <= 90) {
                $buckets['d61_90'] += $c['remaining'];
            } else {
                $buckets['d90_plus'] += $c['remaining'];
            }
        }

        $buckets = array_map(fn ($v) => round($v, 2), $buckets);

        return [
            'buckets' => $buckets,
            'total' => round(array_sum($buckets), 2),
            'credit' => round(max(0, $credit), 2),
        ];
    }
}
