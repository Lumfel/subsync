<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Issued documents (certificates/clearances) need a reference number and issuer. */
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('reference_no', 40)->nullable()->unique()->after('id');
            $table->string('issued_by', 150)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropUnique(['reference_no']);
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['reference_no', 'issued_by']);
        });
    }
};
