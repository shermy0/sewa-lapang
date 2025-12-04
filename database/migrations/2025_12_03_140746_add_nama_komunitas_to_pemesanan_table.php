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
        if (! Schema::hasColumn('pemesanan', 'nama_komunitas')) {
            Schema::table('pemesanan', function (Blueprint $table) {
                $table->string('nama_komunitas')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasColumn('pemesanan', 'nama_komunitas')) {
            Schema::table('pemesanan', function (Blueprint $table) {
                $table->dropColumn('nama_komunitas');
            });
        }
    }
};
