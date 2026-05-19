<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('issue_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained('issue_reports')->cascadeOnDelete();
            $table->foreignId('officer_id')->nullable()->constrained('officers')->nullOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('responder_type', 50); // Officer | Admin
            $table->text('response_content');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_responses');
    }
};
