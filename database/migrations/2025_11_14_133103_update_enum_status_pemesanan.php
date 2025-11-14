<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            ALTER TABLE pemesanan 
            MODIFY status ENUM('menunggu','dibayar','batal','kadaluarsa','di-scan')
            NOT NULL
        ");
    }
    
    public function down()
    {
        DB::statement("
            ALTER TABLE pemesanan 
            MODIFY status ENUM('menunggu','dibayar','batal')
            NOT NULL
        ");
    }
    
};
