<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL's ENUM is altered via raw SQL; SQLite (used in the test suite) has no
        // native ENUM type, so the column is just a plain string there — widen it the
        // portable way instead of running MySQL-only DDL against it.
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('client_notifications', function (Blueprint $table) {
                $table->string('status')->default('queued')->change();
            });
        } else {
            DB::statement("ALTER TABLE client_notifications MODIFY status ENUM('queued', 'sent', 'failed') NOT NULL DEFAULT 'queued'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('client_notifications', function (Blueprint $table) {
                $table->string('status')->default('sent')->change();
            });
        } else {
            DB::statement("UPDATE client_notifications SET status = 'failed' WHERE status = 'queued'");
            DB::statement("ALTER TABLE client_notifications MODIFY status ENUM('sent', 'failed') NOT NULL DEFAULT 'sent'");
        }
    }
};
