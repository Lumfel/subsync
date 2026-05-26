<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->unsignedBigInteger('family_id')->nullable()->after('block_lot_number');
            $table->foreign('family_id')->references('id')->on('families')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->dropForeign(['family_id']);
            $table->dropColumn('family_id');
        });
    }
};
