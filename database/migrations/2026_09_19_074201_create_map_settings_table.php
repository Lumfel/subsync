<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('map_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_lat', 10, 7)->default(10.6195);
            $table->decimal('max_lat', 10, 7)->default(10.6260);
            $table->decimal('min_lng', 10, 7)->default(122.9580);
            $table->decimal('max_lng', 10, 7)->default(122.9650);
            $table->decimal('center_lat', 10, 7)->default(10.62269);
            $table->decimal('center_lng', 10, 7)->default(122.96134);
            $table->tinyInteger('zoom')->default(17);
            $table->timestamps();
        });

        DB::table('map_settings')->insert([
            'min_lat' => 10.6195,
            'max_lat' => 10.6260,
            'min_lng' => 122.9580,
            'max_lng' => 122.9650,
            'center_lat' => 10.62269,
            'center_lng' => 122.96134,
            'zoom' => 17,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('map_settings');
    }
};