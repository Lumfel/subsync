<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['residents', 'officers'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('google_id')->nullable()->unique();
                $t->string('avatar')->nullable();
            });

            // SSO-only accounts have no password.
            Schema::table($table, function (Blueprint $t) {
                $t->string('password')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        foreach (['residents', 'officers'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropUnique(['google_id']);
            });

            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['google_id', 'avatar']);
            });
        }
    }
};
