<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE client_notifications MODIFY status ENUM('queued', 'sent', 'failed') NOT NULL DEFAULT 'queued'");
    }

    public function down(): void
    {
        DB::statement("UPDATE client_notifications SET status = 'failed' WHERE status = 'queued'");
        DB::statement("ALTER TABLE client_notifications MODIFY status ENUM('sent', 'failed') NOT NULL DEFAULT 'sent'");
    }
};
