<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpiresAtToPemesananTable extends Migration
{
    public function up()
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('created_at');
        });
    }

    public function down()
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
}
