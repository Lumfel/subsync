<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('media');
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uploader_id');
            $table->string('uploader_type', 50); // Admin | Officer | Resident
            $table->string('entity_type', 50)->nullable(); // Announcement | IssueReport | etc.
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
