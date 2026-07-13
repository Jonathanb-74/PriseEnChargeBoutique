<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intakes', function (Blueprint $table) {
            $table->string('client_signature_path')->nullable()->after('reported_issue');
            $table->string('client_signature_name')->nullable()->after('client_signature_path');
            $table->timestamp('client_signed_at')->nullable()->after('client_signature_name');

            $table->string('staff_signature_path')->nullable()->after('client_signed_at');
            $table->foreignId('staff_signed_by')->nullable()->constrained('users')->nullOnDelete()->after('staff_signature_path');
            $table->timestamp('staff_signed_at')->nullable()->after('staff_signed_by');
        });
    }

    public function down(): void
    {
        Schema::table('intakes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('staff_signed_by');
            $table->dropColumn([
                'client_signature_path',
                'client_signature_name',
                'client_signed_at',
                'staff_signature_path',
                'staff_signed_at',
            ]);
        });
    }
};
