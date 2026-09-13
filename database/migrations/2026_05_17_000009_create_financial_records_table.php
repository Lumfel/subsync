<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('financial_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained('residents')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('record_type', 100); // Payment | Due | Penalty
            $table->string('description', 255)->nullable();
            $table->decimal('amount', 10, 2);
            $table->date('record_date');
            // Gcash Payment Integration Tracking
            $table->string('gcash_transaction_id', 100)->nullable()->unique();
            $table->string('gcash_reference', 100)->nullable();
            $table->string('payment_gateway', 50)->nullable()->default('manual'); // manual, gcash, bank_transfer, etc.
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_records');
    }
};
