<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('code_client')->nullable()->unique()->after('id');
            $table->string('siret')->nullable()->after('city');
            $table->boolean('actif')->default(true)->after('siret');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['code_client', 'siret', 'actif']);
        });
    }
};
