<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Recurring assessments (dues) that generate charges.
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->decimal('amount', 10, 2);
            $table->string('frequency', 20)->default('monthly'); // monthly | quarterly | annual | one_time
            $table->unsignedTinyInteger('due_day')->nullable();    // day of month the charge is due
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('target', 30)->default('all');          // all | block / reserved for future targeting
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });

        // Category budgets (budget vs actual reporting).
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('category', 100);
            $table->string('fund', 20)->default('Operating');      // Operating | Reserve
            $table->decimal('amount', 10, 2);
            $table->string('period', 7)->nullable();               // YYYY-MM, or null = recurring
            $table->timestamps();
        });

        // Expense fund tagging (Operating / Reserve).
        Schema::table('hoa_expenses', function (Blueprint $table) {
            $table->string('fund', 20)->default('Operating');
        });

        // Ledger metadata on financial_records.
        Schema::table('financial_records', function (Blueprint $table) {
            $table->foreignId('assessment_id')->nullable()->constrained('assessments')->nullOnDelete();
            $table->string('period', 7)->nullable();   // YYYY-MM
            $table->date('due_date')->nullable();
            $table->string('source', 20)->nullable();  // assessment | payment | penalty | credit | opening | manual
        });

        // Opening balances: turn each resident's existing current_balance into an opening "Due" entry
        // so the stored balance stays reconciled with the ledger.
        $now = now()->toDateString();
        foreach (DB::table('residents')->where('current_balance', '>', 0)->get(['id', 'current_balance']) as $r) {
            DB::table('financial_records')->insert([
                'resident_id' => $r->id,
                'admin_id' => null,
                'record_type' => 'Due',
                'description' => 'Opening balance',
                'amount' => $r->current_balance,
                'record_date' => $now,
                'source' => 'opening',
                'created_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('financial_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assessment_id');
            $table->dropColumn(['period', 'due_date', 'source']);
        });

        Schema::table('hoa_expenses', function (Blueprint $table) {
            $table->dropColumn('fund');
        });

        Schema::dropIfExists('budgets');
        Schema::dropIfExists('assessments');
    }
};
