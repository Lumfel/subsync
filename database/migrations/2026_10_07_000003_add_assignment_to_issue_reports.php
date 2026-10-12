<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issue_reports', function (Blueprint $table) {
            $table->foreignId('assigned_officer_id')->nullable()->constrained('officers')->nullOnDelete();
            $table->date('due_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('issue_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_officer_id');
            $table->dropColumn('due_date');
        });
    }
};
