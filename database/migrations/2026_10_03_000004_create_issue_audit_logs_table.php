<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issue_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained('issue_reports')->cascadeOnDelete();
            $table->string('actor_type')->nullable();  // Admin | Officer | System
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action');                  // status | priority | archive
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_audit_logs');
    }
};
