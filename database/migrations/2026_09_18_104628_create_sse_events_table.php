<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sse_events', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 50)->index();
            $table->string('event', 50);
            $table->text('data')->nullable();
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sse_events');
    }
};