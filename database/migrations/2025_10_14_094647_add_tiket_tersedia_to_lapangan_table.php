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
        Schema::table('lapangan', function (Blueprint $table) {
            $table->integer('tiket_tersedia')
                  ->default(0)
                  ->after('harga_per_jam')
                  ->comment('Jumlah tiket yang tersedia untuk booking');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('lapangan', function (Blueprint $table) {
            $table->dropColumn('tiket_tersedia');
        });
    }
};