<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_notifications', function (Blueprint $table) {
            $table->json('bcc')->nullable()->after('cc');
        });
    }

    public function down(): void
    {
        Schema::table('client_notifications', function (Blueprint $table) {
            $table->dropColumn('bcc');
        });
    }
};
