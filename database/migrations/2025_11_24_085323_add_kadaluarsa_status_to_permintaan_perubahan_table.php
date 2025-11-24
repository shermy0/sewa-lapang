<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddKadaluarsaStatusToPermintaanPerubahanTable extends Migration
{
    public function up()
    {
        // Tambahkan value 'kadaluarsa' ke ENUM status
        DB::statement("
            ALTER TABLE permintaan_perubahan 
            MODIFY COLUMN status 
            ENUM('menunggu', 'disetujui', 'ditolak', 'kadaluarsa') 
            DEFAULT 'menunggu'
        ");
    }

    public function down()
    {
        // Kembalikan ke ENUM awal tanpa 'kadaluarsa'
        DB::statement("
            ALTER TABLE permintaan_perubahan 
            MODIFY COLUMN status 
            ENUM('menunggu', 'disetujui', 'ditolak') 
            DEFAULT 'menunggu'
        ");
    }
}
