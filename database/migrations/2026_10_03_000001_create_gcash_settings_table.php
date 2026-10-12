<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gcash_settings', function (Blueprint $table) {
            $table->id();
            $table->string('account_name')->nullable();
            $table->string('account_number', 40)->nullable();
            $table->text('instructions')->nullable();
            $table->string('qr_image_path')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('gcash_settings')->insert([
            'account_name' => 'Terra Nova Homeowners Association',
            'account_number' => null,
            'instructions' => 'Scan the GCash QR code or send your payment to the account above, then upload a screenshot of your receipt for validation.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('gcash_settings');
    }
};
