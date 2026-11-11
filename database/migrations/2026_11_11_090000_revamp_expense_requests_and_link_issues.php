<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1 – Officer Expense Request pipeline + Issue-Linked Expenses.
 *
 * expense_requests: repurposed from the old resident-facing stub
 *   (table was empty) to an officer-submitted claim that feeds into
 *   the admin approval queue and auto-creates an HoaExpense on approval.
 *
 * hoa_expenses: gains optional FK columns so every ledger entry can
 *   trace back to the issue it resolved and/or the officer request
 *   that originated it.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Rebuild expense_requests for the officer workflow ──
        Schema::table('expense_requests', function (Blueprint $table) {
            // Drop the old resident FK (unused, 0 rows).
            $table->dropForeign(['resident_id']);
            $table->dropColumn('resident_id');
        });

        Schema::table('expense_requests', function (Blueprint $table) {
            // Officer who incurred the cost.
            $table->unsignedBigInteger('officer_id')->after('id');
            $table->foreign('officer_id')->references('id')->on('officers')->cascadeOnDelete();

            // Optional link to the issue this expense resolves.
            $table->unsignedBigInteger('issue_id')->nullable()->after('officer_id');
            $table->foreign('issue_id')->references('id')->on('issue_reports')->nullOnDelete();

            // Spending classification.
            $table->string('category', 50)->default('Maintenance')->after('expense_date');
            $table->string('fund', 20)->default('Operating')->after('category');

            // Receipt proof uploaded by the officer.
            $table->string('receipt_path')->nullable()->after('fund');

            // Admin review trail.
            $table->text('admin_notes')->nullable()->after('status');
            $table->timestamp('reviewed_at')->nullable()->after('admin_notes');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('reviewed_at');

            // Back-link to the HoaExpense created on approval.
            $table->unsignedBigInteger('hoa_expense_id')->nullable()->after('reviewed_by');
            $table->foreign('hoa_expense_id')->references('id')->on('hoa_expenses')->nullOnDelete();
        });

        // ── 2. Add traceability columns to hoa_expenses ──
        Schema::table('hoa_expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('issue_id')->nullable()->after('fund');
            $table->foreign('issue_id')->references('id')->on('issue_reports')->nullOnDelete();

            $table->unsignedBigInteger('expense_request_id')->nullable()->after('issue_id');
            $table->foreign('expense_request_id')->references('id')->on('expense_requests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // ── Reverse hoa_expenses additions ──
        Schema::table('hoa_expenses', function (Blueprint $table) {
            $table->dropForeign(['issue_id']);
            $table->dropForeign(['expense_request_id']);
            $table->dropColumn(['issue_id', 'expense_request_id']);
        });

        // ── Reverse expense_requests changes ──
        Schema::table('expense_requests', function (Blueprint $table) {
            $table->dropForeign(['officer_id']);
            $table->dropForeign(['issue_id']);
            $table->dropForeign(['hoa_expense_id']);
            $table->dropColumn([
                'officer_id', 'issue_id', 'category', 'fund',
                'receipt_path', 'admin_notes', 'reviewed_at',
                'reviewed_by', 'hoa_expense_id',
            ]);
        });

        Schema::table('expense_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('resident_id')->after('id');
            $table->foreign('resident_id')->references('id')->on('residents')->cascadeOnDelete();
        });
    }
};
