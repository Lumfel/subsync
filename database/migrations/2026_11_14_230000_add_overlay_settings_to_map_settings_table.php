<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('map_settings', function (Blueprint $table) {
            $table->string('overlay_url')->nullable()->default('/images/subdivision_vector_plan.svg')->after('zoom');
            $table->decimal('overlay_min_lat', 10, 7)->nullable()->after('overlay_url');
            $table->decimal('overlay_max_lat', 10, 7)->nullable()->after('overlay_min_lat');
            $table->decimal('overlay_min_lng', 10, 7)->nullable()->after('overlay_max_lat');
            $table->decimal('overlay_max_lng', 10, 7)->nullable()->after('overlay_min_lng');
            $table->decimal('overlay_opacity', 3, 2)->default(0.65)->after('overlay_max_lng');
            $table->decimal('overlay_rotation', 5, 2)->default(0.00)->after('overlay_opacity');
            $table->boolean('overlay_enabled')->default(true)->after('overlay_rotation');
        });
    }

    public function down(): void
    {
        Schema::table('map_settings', function (Blueprint $table) {
            $table->dropColumn([
                'overlay_url',
                'overlay_min_lat',
                'overlay_max_lat',
                'overlay_min_lng',
                'overlay_max_lng',
                'overlay_opacity',
                'overlay_rotation',
                'overlay_enabled',
            ]);
        });
    }
};
