<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('officer_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('officer_id')->nullable()->constrained('officers')->nullOnDelete();
            $table->string('officer_name');
            $table->string('original_name');
            $table->string('category');
            $table->string('period')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('officer_files');
    }
};
