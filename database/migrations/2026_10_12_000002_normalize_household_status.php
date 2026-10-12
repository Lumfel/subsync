<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Household status vocabulary per the capstone ERD is Occupied/Vacant.
     * Older rows used the resident-style Active/Inactive/Non-Compliant.
     */
    public function up(): void
    {
        DB::table('households')->where('status', 'Active')->update(['status' => 'Occupied']);
        DB::table('households')->whereIn('status', ['Inactive', 'Vacant'])->update(['status' => 'Vacant']);
        DB::table('households')->whereNotIn('status', ['Occupied', 'Vacant'])->update(['status' => 'Occupied']);
    }

    public function down(): void
    {
        // No-op: the vocabulary is intentionally one-way.
    }
};
