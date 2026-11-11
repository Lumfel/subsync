<?php

namespace App\Console\Commands;

use App\Mail\PaymentReminderMail;
use App\Models\Resident;
use App\Models\UserSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Monthly balance reminder: emails every Active resident whose ledger shows an
 * outstanding balance. Scheduled a few days after the monthly dues generation
 * so the reminder already includes the new month's charge.
 */
class RemindBalances extends Command
{
    protected $signature = 'subsync:remind-balances {--dry-run : List who would be reminded without sending}';

    protected $description = 'Email a balance reminder to every Active resident with an outstanding balance.';

    public function handle(): int
    {
        $candidates = Resident::where('status', 'Active')
            ->where('current_balance', '>', 0.01)
            ->whereNotNull('email')
            ->get(['id', 'name', 'email', 'current_balance']);

        if ($candidates->isEmpty()) {
            $this->info('No residents with an outstanding balance.');

            return self::SUCCESS;
        }

        // Honor the resident's Settings choice ("Email copies"). A missing row
        // means the default — which is now opt-out (enabled).
        $optOut = UserSetting::where('user_type', 'resident')
            ->where('email_notifications', false)
            ->pluck('user_id')
            ->flip();
        $residents = $candidates->reject(fn ($r) => $optOut->has($r->id))->values();
        $skipped = $candidates->count() - $residents->count();

        if ($residents->isEmpty()) {
            $this->info("No residents to remind ({$skipped} opted out of email).");

            return self::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        foreach ($residents as $resident) {
            if ($this->option('dry-run')) {
                $this->line(sprintf(
                    '  would remind %s (%s) — %s',
                    $resident->name,
                    $resident->email,
                    number_format((float) $resident->current_balance, 2)
                ));
                $sent++;

                continue;
            }

            try {
                Mail::to($resident->email)->send(
                    new PaymentReminderMail($resident, (float) $resident->current_balance)
                );
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                $this->warn("  {$resident->email}: {$e->getMessage()}");
            }
        }

        $verb = $this->option('dry-run') ? 'would be sent' : 'sent';
        $this->info("Balance reminders: {$sent} {$verb}"
            .($failed ? ", {$failed} failed" : '')
            .($skipped ? ", {$skipped} opted out of email" : '').'.');

        return self::SUCCESS;
    }
}
