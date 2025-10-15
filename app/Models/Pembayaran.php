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
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'tanggal_pembayaran' => 'datetime',
    ];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class);
    }
}

