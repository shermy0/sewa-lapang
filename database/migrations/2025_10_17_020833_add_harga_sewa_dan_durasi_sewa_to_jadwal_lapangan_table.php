<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('jadwal_lapangan', function (Blueprint $table) {
            $table->decimal('harga_sewa', 12, 2)->default(0);
            $table->integer('durasi_sewa')->default(60);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('jadwal_lapangan', function (Blueprint $table) {
            $table->dropColumn(['harga_sewa', 'durasi_sewa']);
        });
    }
};