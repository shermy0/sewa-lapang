<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PEMESANAN
        Schema::table('pemesanan', function (Blueprint $table) {
            if (! Schema::hasColumn('pemesanan', 'order_id')) {
                $table->string('order_id')->nullable()->after('id')->index();
            }
            if (! Schema::hasColumn('pemesanan', 'section_id')) {
                $table->unsignedBigInteger('section_id')->nullable()->after('jadwal_id')->index();
            }

            // optional: unique index to reduce duplicate pemesanan per jadwal by same penyewa
            // jangan paksa unique pada jadwal_id sendiri (karena multi-penyewa), tapi kita akan cek lewat logic
            // jika ingin menambahkan constraint unik per (penyewa_id, jadwal_id) uncomment berikut:
            // $table->unique(['penyewa_id', 'jadwal_id'], 'u_penyewa_jadwal');
        });

        // PEMBAYARAN (tambahan order_id di pembayaran agar mudah check)
        Schema::table('pembayaran', function (Blueprint $table) {
            if (! Schema::hasColumn('pembayaran', 'order_id')) {
                $table->string('order_id')->nullable()->after('id')->index();
            }
            if (! Schema::hasColumn('pembayaran', 'snap_token')) {
                $table->string('snap_token')->nullable()->after('order_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            if (Schema::hasColumn('pemesanan', 'order_id')) {
                $table->dropColumn('order_id');
            }
            if (Schema::hasColumn('pemesanan', 'section_id')) {
                $table->dropColumn('section_id');
            }
            // if want to drop unique uncomment if used
            // $table->dropUnique('u_penyewa_jadwal');
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            if (Schema::hasColumn('pembayaran', 'order_id')) {
                $table->dropColumn('order_id');
            }
            if (Schema::hasColumn('pembayaran', 'snap_token')) {
                $table->dropColumn('snap_token');
            }
        });
    }
};
