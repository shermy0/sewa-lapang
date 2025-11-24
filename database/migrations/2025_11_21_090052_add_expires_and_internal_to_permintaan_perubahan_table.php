<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpiresAndInternalToPermintaanPerubahanTable extends Migration
{
    public function up()
    {
        Schema::table('permintaan_perubahan', function (Blueprint $table) {
            if (! Schema::hasColumn('permintaan_perubahan', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('permintaan_perubahan', 'alasan_internal')) {
                $table->text('alasan_internal')->nullable()->after('alasan');
            }
        });
    }

    public function down()
    {
        Schema::table('permintaan_perubahan', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'alasan_internal']);
        });
    }
}