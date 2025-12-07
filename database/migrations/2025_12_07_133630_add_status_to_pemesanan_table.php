<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            $table->enum('status', ['keranjang', 'menunggu', 'dibayar', 'selesai', 'batal', 'kadaluarsa'])
                ->default('keranjang')
                ->change();
        });
    }

    public function down()
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            $table->enum('status', ['menunggu', 'dibayar', 'selesai', 'batal', 'kadaluarsa'])
                ->default('menunggu')
                ->change();
        });
    }
};
