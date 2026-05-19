<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private array $staleTables = [
        'admin',
        'announcement',
        'announcement_view',
        'conv_participant',
        'conversation',
        'facility',
        'financial_record',
        'household_member',
        'issue_report',
        'issue_response',
        'message',
        'officer',
        'recommendation',
        'resident',
    ];

    public function up(): void
    {
        // Disable FK checks so drops work regardless of order
        Schema::disableForeignKeyConstraints();

        foreach ($this->staleTables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // These tables were pre-migration leftovers; no rollback needed
    }
};
