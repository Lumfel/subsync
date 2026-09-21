<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('officer_files', function (Blueprint $table) {
            $table->boolean('archived')->default(false)->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('officer_files', function (Blueprint $table) {
            $table->dropColumn('archived');
        });
    }
};
