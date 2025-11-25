<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permintaan_perubahan', function (Blueprint $table) {
            if (! Schema::hasColumn('permintaan_perubahan', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('permintaan_perubahan', 'alasan_internal')) {
                $table->string('alasan_internal')->nullable()->after('alasan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permintaan_perubahan', function (Blueprint $table) {
            if (Schema::hasColumn('permintaan_perubahan', 'alasan_internal')) {
                $table->dropColumn('alasan_internal');
            }

            if (Schema::hasColumn('permintaan_perubahan', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });
    }
};
