<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issue_reports', function (Blueprint $table) {
            $table->timestamp('resolved_at')->nullable()->index();
        });

        // Backfill: any issue currently Resolved gets resolved_at from its last update.
        DB::table('issue_reports')->where('status', 'Resolved')->update([
            'resolved_at' => DB::raw('updated_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('issue_reports', function (Blueprint $table) {
            $table->dropColumn('resolved_at');
        });
    }
};