<?php

namespace Database\Seeders;

use App\Models\HoaExpense;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HoaExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample HOA expenses showing how resident fees are utilized
        HoaExpense::create([
            'title' => 'Monthly Landscaping Service',
            'description' => 'Professional landscaping maintenance for common areas, parks, and walkways throughout the subdivision.',
            'amount' => 1200.00,
            'expense_date' => '2026-09-01',
            'category' => 'Maintenance',
            'status' => 'Paid',
            'receipt_path' => 'uploads/expenses/landscaping_sept_2026.pdf'
        ]);

        HoaExpense::create([
            'title' => 'Community Pool Maintenance',
            'description' => 'Monthly pool cleaning, chemical balancing, and equipment inspection for the subdivision swimming pool.',
            'amount' => 800.00,
            'expense_date' => '2026-09-05',
            'category' => 'Amenities',
            'status' => 'Paid',
            'receipt_path' => 'uploads/expenses/pool_maintenance_sept_2026.pdf'
        ]);

        HoaExpense::create([
            'title' => 'Security Guard Services',
            'description' => 'Monthly contract for 24/7 security personnel at subdivision entrances and patrols.',
            'amount' => 2500.00,
            'expense_date' => '2026-09-10',
            'category' => 'Security',
            'status' => 'Paid',
            'receipt_path' => 'uploads/expenses/security_sept_2026.pdf'
        ]);

        HoaExpense::create([
            'title' => 'Liability Insurance Premium',
            'description' => 'Quarterly payment for subdivision liability insurance covering common areas and amenities.',
            'amount' => 1500.00,
            'expense_date' => '2026-09-15',
            'category' => 'Insurance',
            'status' => 'Paid',
            'receipt_path' => 'uploads/expenses/insurance_q3_2026.pdf'
        ]);

        HoaExpense::create([
            'title' => 'Clubhouse Utilities',
            'description' => 'Electricity, water, and gas for the community clubhouse and meeting facilities.',
            'amount' => 450.00,
            'expense_date' => '2026-09-20',
            'category' => 'Utilities',
            'status' => 'Paid',
            'receipt_path' => 'uploads/expenses/utilities_sept_2026.pdf'
        ]);
    }
}