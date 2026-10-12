<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained('residents')->cascadeOnDelete();

            $table->decimal('amount', 10, 2);
            $table->string('method', 30)->default('gcash');

            // Data extracted from / entered alongside the GCash receipt
            $table->string('reference_no', 100)->nullable();
            $table->string('gcash_sender_name', 150)->nullable();
            $table->string('gcash_number', 40)->nullable();
            $table->date('paid_at')->nullable();

            // Resident-uploaded proof of payment (side 1)
            $table->string('receipt_path')->nullable();

            // Validation workflow
            $table->string('status', 30)->default('Pending'); // Pending, Validated, Rejected, Needs Info
            $table->text('admin_response')->nullable();
            $table->string('rejection_reason')->nullable();

            // Official acknowledgement receipt (side 2)
            $table->string('official_receipt_path')->nullable();
            $table->timestamp('acknowledged_at')->nullable();

            $table->foreignId('validated_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('financial_record_id')->nullable()->constrained('financial_records')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_submissions');
    }
};
