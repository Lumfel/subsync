<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Composite indexes for the hot query paths found in the system review:
 * issue working-lists, approval queues, ledger statements, message threads,
 * payment queues, calendars, officer files and suggestion/policy lists.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issue_reports', function (Blueprint $table) {
            $table->index('status', 'issue_reports_status_index');
            $table->index(['archived_at', 'created_at'], 'issue_reports_archived_created_index');
            $table->index(['priority', 'status'], 'issue_reports_priority_status_index');
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->index('status', 'residents_status_index');
        });

        Schema::table('officers', function (Blueprint $table) {
            $table->index('status', 'officers_status_index');
        });

        Schema::table('financial_records', function (Blueprint $table) {
            $table->index(['resident_id', 'record_date'], 'financial_records_resident_date_index');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index(['conversation_id', 'created_at'], 'messages_conversation_created_index');
        });

        Schema::table('payment_submissions', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'payment_submissions_status_created_index');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->index('starts_at', 'events_starts_at_index');
        });

        Schema::table('officer_files', function (Blueprint $table) {
            $table->index(['archived', 'created_at'], 'officer_files_archived_created_index');
        });

        Schema::table('recommendations', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'recommendations_status_created_index');
        });

        Schema::table('hoa_policies', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'hoa_policies_status_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('issue_reports', function (Blueprint $table) {
            $table->dropIndex('issue_reports_status_index');
            $table->dropIndex('issue_reports_archived_created_index');
            $table->dropIndex('issue_reports_priority_status_index');
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->dropIndex('residents_status_index');
        });

        Schema::table('officers', function (Blueprint $table) {
            $table->dropIndex('officers_status_index');
        });

        Schema::table('financial_records', function (Blueprint $table) {
            $table->dropIndex('financial_records_resident_date_index');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_conversation_created_index');
        });

        Schema::table('payment_submissions', function (Blueprint $table) {
            $table->dropIndex('payment_submissions_status_created_index');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('events_starts_at_index');
        });

        Schema::table('officer_files', function (Blueprint $table) {
            $table->dropIndex('officer_files_archived_created_index');
        });

        Schema::table('recommendations', function (Blueprint $table) {
            $table->dropIndex('recommendations_status_created_index');
        });

        Schema::table('hoa_policies', function (Blueprint $table) {
            $table->dropIndex('hoa_policies_status_created_index');
        });
    }
};
