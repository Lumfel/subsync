<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subdivision_blocks', function (Blueprint $table) {
            $table->decimal('label_lat', 10, 7)->nullable()->after('start_lng');
            $table->decimal('label_lng', 10, 7)->nullable()->after('label_lat');
        });
    }

    public function down(): void
    {
        Schema::table('subdivision_blocks', function (Blueprint $table) {
            $table->dropColumn(['label_lat', 'label_lng']);
        });
    }
};
