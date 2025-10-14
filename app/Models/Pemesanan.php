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

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Penyewa yang melakukan pemesanan.
     */
    public function penyewa()
    {
        return $this->belongsTo(User::class, 'penyewa_id');
    }

    /**
     * Lapangan yang dipesan.
     */
    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }

    /**
     * Jadwal lapangan yang dipilih.
     */
    public function jadwal()
    {
        return $this->belongsTo(JadwalLapangan::class, 'jadwal_id');
    }

    /**
     * Pembayaran yang terkait dengan pemesanan.
     */
    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'pemesanan_id');
    }
}

