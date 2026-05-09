<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE members
            MODIFY member_type ENUM(
                'Head',
                'Tenant',
                'Family_member',
                'Spouse',
                'Child'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE members
            MODIFY member_type ENUM(
                'Tenant',
                'Family_member'
            ) NOT NULL
        ");
    }
};