<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('target', 50)->default('All Residents')->after('tag');
            $table->string('priority', 20)->default('Normal')->after('target');
            $table->date('event_date')->nullable()->after('priority');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['target', 'priority', 'event_date']);
        });
    }
};
