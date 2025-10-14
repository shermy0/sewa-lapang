<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    use HasFactory;

    protected $table = 'pemesanan';

    protected $fillable = [
        'lapangan_id',
        'penyewa_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'status',
        'total_harga'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'penyewa_id');
    }

    public function ulasan()
    {
        return $this->hasOne(Ulasan::class);
    }
    
    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }

    public function jadwal()
    {
        return $this->belongsTo(JadwalLapangan::class, 'jadwal_id');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'pemesanan_id');
    }

    public function penyewa()
    {
        return $this->belongsTo(User::class, 'penyewa_id');
    }
}
