<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Household members indexes
        Schema::table('household_members', function (Blueprint $table) {
            $table->index('house_id');
        });

        // Payment statuses indexes
        Schema::table('payment_statuses', function (Blueprint $table) {
            $table->index('house_id');
            $table->index('date_flagged');
        });

        // Conversations indexes
        Schema::table('conversations', function (Blueprint $table) {
            $table->index('created_at');
        });

        // Conv participants indexes
        Schema::table('conv_participants', function (Blueprint $table) {
            $table->index('resident_id');
            $table->index('officer_id');
            $table->index('conversation_id');
        });

        // Messages indexes
        Schema::table('messages', function (Blueprint $table) {
            $table->index('conversation_id');
            $table->index('created_at');
        });

        // Media indexes
        Schema::table('media', function (Blueprint $table) {
            $table->index(['entity_type', 'entity_id']);
            $table->index('uploader_id');
        });

        // Hoa expenses indexes
        Schema::table('hoa_expenses', function (Blueprint $table) {
            $table->index('category');
            $table->index('expense_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('household_members', function (Blueprint $table) {
            $table->dropIndex(['house_id']);
        });

        Schema::table('payment_statuses', function (Blueprint $table) {
            $table->dropIndex(['house_id']);
            $table->dropIndex(['date_flagged']);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('conv_participants', function (Blueprint $table) {
            $table->dropIndex(['resident_id']);
            $table->dropIndex(['officer_id']);
            $table->dropIndex(['conversation_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['conversation_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex(['entity_type', 'entity_id']);
            $table->dropIndex(['uploader_id']);
        });

        Schema::table('hoa_expenses', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropIndex(['expense_date']);
            $table->dropIndex(['status']);
        });
    }
};