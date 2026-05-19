<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conv_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('resident_id')->nullable()->constrained('residents')->cascadeOnDelete();
            $table->foreignId('officer_id')->nullable()->constrained('officers')->cascadeOnDelete();
            $table->string('participant_type', 50); // Resident | Officer | Admin
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conv_participants');
    }
};
