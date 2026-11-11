<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Announcement;
use App\Models\HoaExpense;
use App\Models\PaymentSubmission;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateMonthlyFinancialDigest extends Command
{
    protected $signature = 'subsync:financial-digest {--month= : Override month in YYYY-MM format (defaults to previous month)} {--force : Re-publish even if already published}';

    protected $description = 'Automatically compile and publish the monthly community financial transparency digest.';

    public function handle(): int
    {
        $monthStr = (string) ($this->option('month') ?: now()->subMonth()->format('Y-m'));

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $monthStr)) {
            $this->error("Invalid month format '{$monthStr}'. Expected YYYY-MM (e.g. 2026-10).");

            return self::FAILURE;
        }

        $startDate = Carbon::createFromFormat('Y-m', $monthStr)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromFormat('Y-m', $monthStr)->endOfMonth()->toDateString();
        $monthLabel = Carbon::createFromFormat('Y-m', $monthStr)->format('F Y');

        $title = "Financial Transparency Digest — {$monthLabel}";

        // Idempotency: skip if already published unless --force
        if (! $this->option('force') && Announcement::where('title', $title)->exists()) {
            $this->info("Digest for {$monthLabel} already published. Use --force to re-publish.");

            return self::SUCCESS;
        }

        // Calculate collections for the month
        $paidSubmissions = PaymentSubmission::where('status', 'Validated')
            ->whereRaw('date(COALESCE(validated_at, created_at)) between ? and ?', [$startDate, $endDate]);

        $collected = (float) (clone $paidSubmissions)->sum('amount');
        $paymentsCount = (clone $paidSubmissions)->count();

        // Calculate expenditures for the month
        $expenses = HoaExpense::whereDate('expense_date', '>=', $startDate)
            ->whereDate('expense_date', '<=', $endDate);

        $spent = (float) (clone $expenses)->sum('amount');
        $operating = (float) (clone $expenses)->where('fund', 'Operating')->sum('amount');
        $reserve = (float) (clone $expenses)->where('fund', 'Reserve')->sum('amount');
        $net = $collected - $spent;

        // Categories breakdown
        $topCategories = (clone $expenses)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $catText = '';
        if ($topCategories->isEmpty()) {
            $catText = "• No expenditures recorded during this period.\n";
        } else {
            foreach ($topCategories as $cat) {
                $peso = '₱'.number_format((float) $cat->total, 2);
                $catText .= "• {$cat->category}: {$peso}\n";
            }
        }

        $pesoFmt = fn ($v) => '₱'.number_format((float) $v, 2);
        $netSign = $net >= 0 ? '+' : '';

        $content = "Dear Residents,\n\n"
            ."Here is our community financial transparency digest for {$monthLabel}:\n\n"
            ."💰 Collections & Dues:\n"
            ."• Total Collected: {$pesoFmt($collected)} ({$paymentsCount} validated payments)\n\n"
            ."🛠 Expenditures:\n"
            ."• Total Spent: {$pesoFmt($spent)}\n"
            ."  - Operating Fund (Maintenance & Utilities): {$pesoFmt($operating)}\n"
            ."  - Reserve Fund (Capital & Improvements): {$pesoFmt($reserve)}\n\n"
            ."📊 Net Period Posture:\n"
            ."• Net: {$netSign}{$pesoFmt($net)}\n\n"
            ."Top Spending Categories:\n"
            .$catText."\n"
            ."All itemized receipts and invoices are viewable directly under your portal's Expenses & Transparency tabs.";

        $adminId = Admin::first()?->id ?? 1;

        Announcement::create([
            'admin_id' => $adminId,
            'title' => $title,
            'content' => $content,
            'tag' => 'notice',
            'target' => 'All Residents',
            'priority' => 'Normal',
            'status' => 'Active',
        ]);

        $this->info("Successfully published financial transparency digest for {$monthLabel}.");

        return self::SUCCESS;
    }
}
