<?php

namespace Database\Seeders;

use App\Models\HoaExpense;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class HoaExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample HOA expenses showing how resident fees are utilized.
        // Each expense ships with a real (placeholder) receipt file so the
        // resident "View" link resolves instead of returning a 404.
        $expenses = [
            [
                'title' => 'Monthly Landscaping Service',
                'description' => 'Professional landscaping maintenance for common areas, parks, and walkways throughout the subdivision.',
                'amount' => 1200.00,
                'expense_date' => '2026-09-01',
                'category' => 'Maintenance',
                'status' => 'Paid',
                'receipt_path' => 'uploads/expenses/landscaping_sept_2026.pdf',
            ],
            [
                'title' => 'Community Pool Maintenance',
                'description' => 'Monthly pool cleaning, chemical balancing, and equipment inspection for the subdivision swimming pool.',
                'amount' => 800.00,
                'expense_date' => '2026-09-05',
                'category' => 'Amenities',
                'status' => 'Paid',
                'receipt_path' => 'uploads/expenses/pool_maintenance_sept_2026.pdf',
            ],
            [
                'title' => 'Security Guard Services',
                'description' => 'Monthly contract for 24/7 security personnel at subdivision entrances and patrols.',
                'amount' => 2500.00,
                'expense_date' => '2026-09-10',
                'category' => 'Security',
                'status' => 'Paid',
                'receipt_path' => 'uploads/expenses/security_sept_2026.pdf',
            ],
            [
                'title' => 'Liability Insurance Premium',
                'description' => 'Quarterly payment for subdivision liability insurance covering common areas and amenities.',
                'amount' => 1500.00,
                'expense_date' => '2026-09-15',
                'category' => 'Insurance',
                'status' => 'Paid',
                'receipt_path' => 'uploads/expenses/insurance_q3_2026.pdf',
            ],
            [
                'title' => 'Clubhouse Utilities',
                'description' => 'Electricity, water, and gas for the community clubhouse and meeting facilities.',
                'amount' => 450.00,
                'expense_date' => '2026-09-20',
                'category' => 'Utilities',
                'status' => 'Paid',
                'receipt_path' => 'uploads/expenses/utilities_sept_2026.pdf',
            ],
        ];

        foreach ($expenses as $expense) {
            Storage::disk('public')->put(
                $expense['receipt_path'],
                $this->placeholderReceipt($expense['title'], $expense['amount'], $expense['expense_date'])
            );

            HoaExpense::create($expense);
        }
    }

    /** Build a minimal, valid single-page PDF placeholder receipt. */
    public function placeholderReceipt(string $title, float $amount, string $date): string
    {
        $lines = [
            ['Terra Nova HOA', 20],
            ['Official Expense Receipt', 14],
            ['', 12],
            ['Title:  '.$title, 12],
            ['Amount: PHP '.number_format($amount, 2), 12],
            ['Date:   '.$date, 12],
            ['', 12],
            ['Status: Paid', 12],
            ['', 12],
            ['This is a system-generated placeholder receipt', 10],
            ['for demonstration purposes.', 10],
        ];

        $content = '';
        $y = 720;
        foreach ($lines as $i => [$line, $size]) {
            if ($line !== '') {
                $content .= 'BT /F1 '.$size.' Tf 72 '.$y.' Td ('.$this->pdfEscape($line).") Tj ET\n";
            }
            $y -= $i <= 1 ? 28 : 22;
        }

        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            5 => '<< /Length '.strlen($content)." >>\nstream\n".$content.'endstream',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        for ($i = 1; $i <= 5; $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= $i." 0 obj\n".$objects[$i]."\nendobj\n";
        }

        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        for ($i = 1; $i <= 5; $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }
        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n".$xrefPos."\n%%EOF";

        return $pdf;
    }

    private function pdfEscape(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }
}
