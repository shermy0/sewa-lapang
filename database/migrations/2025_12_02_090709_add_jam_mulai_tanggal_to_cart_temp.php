<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cart_temp', function (Blueprint $table) {
            $table->time('jam_mulai')->nullable()->after('harga');
            $table->date('tanggal')->nullable()->after('jam_mulai');
        });
    }

    public function down()
    {
        Schema::table('cart_temp', function (Blueprint $table) {
            $table->dropColumn(['jam_mulai', 'tanggal']);
        });
    }
};
