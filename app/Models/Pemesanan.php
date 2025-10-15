<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    use HasFactory;

    protected $table = 'pemesanan';

    protected $fillable = [
        'penyewa_id',
        'lapangan_id',
        'jadwal_id',
        'status',
    ];

    public function penyewa()
    {
        return $this->belongsTo(User::class, 'penyewa_id');
    }

    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'pemesanan_id');
    }
}

