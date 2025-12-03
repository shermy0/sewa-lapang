<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_temp', function (Blueprint $table) {
            if (!Schema::hasColumn('cart_temp', 'jadwal_id')) {
                $table->unsignedBigInteger('jadwal_id')->nullable()->after('lapangan_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cart_temp', function (Blueprint $table) {
            if (Schema::hasColumn('cart_temp', 'jadwal_id')) {
                $table->dropColumn('jadwal_id');
            }
        });
    }
};
