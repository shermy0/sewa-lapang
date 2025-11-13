<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banding_pemilik', function (Blueprint $table) {
            if (! Schema::hasColumn('banding_pemilik', 'lampiran_path')) {
                $table->string('lampiran_path')->nullable()->after('alasan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('banding_pemilik', function (Blueprint $table) {
            if (Schema::hasColumn('banding_pemilik', 'lampiran_path')) {
                $table->dropColumn('lampiran_path');
            }
        });
    }
};
