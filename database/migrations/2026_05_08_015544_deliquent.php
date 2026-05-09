<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delinquents', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('house_id');
            $table->string('reason');
            $table->date('date_flagged')->nullable();

            $table->timestamps();

            $table->foreign('house_id')
                ->references('id')
                ->on('households')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delinquents');
    }
};