<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Message requests: a participant added by someone else starts as
     * 'pending' until they accept. Existing rows default to 'accepted'.
     */
    public function up(): void
    {
        Schema::table('conv_participants', function (Blueprint $table) {
            $table->string('status', 20)->default('accepted')->index();
        });
    }

    public function down(): void
    {
        Schema::table('conv_participants', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('conv_participants', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
