<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $fillable = [
        'pemesanan_id',
        'metode',
        'jumlah',
        'status',
        'order_id',
        'payment_url',
        'tanggal_pembayaran',
        'pajak_admin',
        'jumlah_bersih',
        'status_pencairan',
    ];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'pemesanan_id');
    }
    public function pencairan()
{
    return $this->hasOne(PencairanDana::class, 'pembayaran_id');
}

}
