<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Balance/dues reminders are transactional notices about the resident's own
 * account, so email copies become opt-out (default on) instead of opt-in.
 * The Settings toggle still lets a resident disable them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->boolean('email_notifications')->default(true)->change();
        });

        // Existing rows carried the old opt-in default (false) — treat them as
        // opt-out, matching the new default. Anyone who truly wants none can
        // turn the toggle off in Settings.
        DB::table('user_settings')->update(['email_notifications' => true]);
    }

    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->boolean('email_notifications')->default(false)->change();
        });
    }
};
