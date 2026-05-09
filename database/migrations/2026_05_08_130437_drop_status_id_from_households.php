<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('households', function (Blueprint $table) {
        $table->dropColumn('status_id');
    });
}

public function down()
{
    Schema::table('households', function (Blueprint $table) {
        $table->unsignedBigInteger('status_id')->nullable();
    });
}
};
