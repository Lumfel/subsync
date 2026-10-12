<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->string('user_type', 20); // admin | officer | resident
            $table->unsignedBigInteger('user_id');

            // Appearance
            $table->string('theme', 20)->default('light');       // light | dark | system
            $table->string('density', 20)->default('comfortable'); // comfortable | compact
            $table->string('font_scale', 20)->default('default');  // small | default | large

            // Notification preferences
            $table->boolean('notify_announcements')->default(true);
            $table->boolean('notify_payments')->default(true);
            $table->boolean('notify_issues')->default(true);
            $table->boolean('notify_messages')->default(true);
            $table->boolean('email_notifications')->default(false);

            $table->timestamps();

            $table->unique(['user_type', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
