<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop the old households table (created via SQL dump with wrong schema)
        Schema::dropIfExists('households');

        Schema::create('households', function (Blueprint $table) {
            $table->id();
            $table->string('block_lot_number', 50);
            $table->string('status', 50)->default('Occupied');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('households');
    }
};
