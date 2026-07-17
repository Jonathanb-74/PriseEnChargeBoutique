<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('signature_path')->nullable()->after('is_assignable');
            $table->string('signature_type')->nullable()->after('signature_path');
            $table->timestamp('signature_updated_at')->nullable()->after('signature_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['signature_path', 'signature_type', 'signature_updated_at']);
        });
    }
};
