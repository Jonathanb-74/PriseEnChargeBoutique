<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intake_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intake_id')->constrained()->cascadeOnDelete();
            $table->foreignId('status_id')->constrained()->restrictOnDelete();
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('changed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intake_status_histories');
    }
};
