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
                'amount' => 12000.00,
                'expense_date' => '2026-09-01',
                'category' => 'Landscaping',
                'status' => 'Paid',
                'fund' => 'Operating',
                'receipt_path' => 'uploads/expenses/landscaping_sept_2026.pdf',
            ],
            [
                'title' => 'Community Pool Maintenance',
                'description' => 'Monthly pool cleaning, chemical balancing, and equipment inspection for the subdivision swimming pool.',
                'amount' => 6500.00,
                'expense_date' => '2026-09-05',
                'category' => 'Amenities',
                'status' => 'Paid',
                'fund' => 'Operating',
                'receipt_path' => 'uploads/expenses/pool_maintenance_sept_2026.pdf',
            ],
            [
                'title' => 'Security Guard Services',
                'description' => 'Monthly contract for 24/7 security personnel at subdivision entrances and perimeter patrols.',
                'amount' => 25000.00,
                'expense_date' => '2026-09-10',
                'category' => 'Security',
                'status' => 'Paid',
                'fund' => 'Operating',
                'receipt_path' => 'uploads/expenses/security_sept_2026.pdf',
            ],
            [
                'title' => 'Comprehensive Liability Insurance',
                'description' => 'Annual premium for subdivision liability insurance covering all common facilities, playgrounds, and amenities.',
                'amount' => 15000.00,
                'expense_date' => '2026-09-15',
                'category' => 'Insurance',
                'status' => 'Paid',
                'fund' => 'Reserve',
                'receipt_path' => 'uploads/expenses/insurance_q3_2026.pdf',
            ],
            [
                'title' => 'Clubhouse & Common Area Utilities',
                'description' => 'Electricity, streetlighting power grid, and water utilities for the community clubhouse and facilities.',
                'amount' => 8450.00,
                'expense_date' => '2026-09-20',
                'category' => 'Utilities',
                'status' => 'Paid',
                'fund' => 'Operating',
                'receipt_path' => 'uploads/expenses/utilities_sept_2026.pdf',
            ],
            [
                'title' => 'Drainage Sump Pump Servicing & Desilting',
                'description' => 'Preventive desilting of main drainage canals and mechanical servicing of the flood sump pump.',
                'amount' => 9500.00,
                'expense_date' => '2026-09-25',
                'category' => 'Maintenance',
                'status' => 'Paid',
                'fund' => 'Operating',
                'receipt_path' => 'uploads/expenses/drainage_sept_2026.pdf',
            ],
            [
                'title' => 'Perimeter CCTV & Guardhouse Hardware Maintenance',
                'description' => 'Quarterly maintenance, camera lens cleaning, and DVR backup storage replacement for entrance gate.',
                'amount' => 5200.00,
                'expense_date' => '2026-09-28',
                'category' => 'Security',
                'status' => 'Paid',
                'fund' => 'Operating',
                'receipt_path' => 'uploads/expenses/cctv_sept_2026.pdf',
            ],
            [
                'title' => 'Clubhouse Air Conditioning Preventive Servicing',
                'description' => 'Cleaning and freon replenishment for 4 split-type inverter air conditioning units in the multi-purpose hall.',
                'amount' => 3500.00,
                'expense_date' => '2026-10-02',
                'category' => 'Maintenance',
                'status' => 'Paid',
                'fund' => 'Operating',
                'receipt_path' => 'uploads/expenses/aircon_oct_2026.pdf',
            ],
            [
                'title' => 'Subdivision Waste Hauling Subsidy',
                'description' => 'Supplemental segregated recycling and solid waste hauling service for community cleanliness.',
                'amount' => 7200.00,
                'expense_date' => '2026-10-05',
                'category' => 'Maintenance',
                'status' => 'Paid',
                'fund' => 'Operating',
                'receipt_path' => 'uploads/expenses/waste_oct_2026.pdf',
            ],
            [
                'title' => 'Annual Independent Financial Audit',
                'description' => 'Professional CPA engagement for independent audit of HOA treasury and fiscal reporting.',
                'amount' => 18000.00,
                'expense_date' => '2026-10-10',
                'category' => 'Administrative',
                'status' => 'Paid',
                'fund' => 'Operating',
                'receipt_path' => 'uploads/expenses/audit_oct_2026.pdf',
            ],
            [
                'title' => 'General Community Assembly Logistics & Refreshments',
                'description' => 'Sound system rental, chairs, and light refreshments for the annual community election and general assembly.',
                'amount' => 4500.00,
                'expense_date' => '2026-10-15',
                'category' => 'Events',
                'status' => 'Paid',
                'fund' => 'Operating',
                'receipt_path' => 'uploads/expenses/assembly_oct_2026.pdf',
            ],
            [
                'title' => 'Street Tree Trimming & Hazard Mitigation',
                'description' => 'Professional arborist service to trim branches interfering with overhead power lines along Blocks 1-4.',
                'amount' => 6800.00,
                'expense_date' => '2026-10-18',
                'category' => 'Maintenance',
                'status' => 'Paid',
                'fund' => 'Operating',
                'receipt_path' => 'uploads/expenses/tree_trimming_oct_2026.pdf',
            ],
        ];

        foreach ($expenses as $expense) {
            $pdf = $this->placeholderReceipt($expense['title'], $expense['amount'], $expense['expense_date']);
            Storage::disk('public')->put($expense['receipt_path'], $pdf);
            Storage::disk('local')->put($expense['receipt_path'], $pdf);

            HoaExpense::firstOrCreate(
                ['title' => $expense['title']],
                $expense
            );
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
