<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intake_id')->constrained()->cascadeOnDelete();
            $table->string('template_key')->nullable();
            $table->string('subject');
            $table->string('recipient_email');
            $table->json('cc')->nullable();
            $table->foreignId('sent_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_notifications');
    }
};
