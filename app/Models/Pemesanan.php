<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    use HasFactory;

    protected $table = 'pemesanan'; // <-- pastikan ini sesuai migration
    protected $fillable = [
        'penyewa_id',
        'lapangan_id',
        'jadwal_id',
        'status',
        'kode_tiket', // jangan lupa tambahkan
        'status_scan',
        'waktu_scan',
    ];

    public function penyewa()
    {
        return $this->belongsTo(User::class, 'penyewa_id');
    }

    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }

    public function jadwal()
    {
        return $this->belongsTo(JadwalLapangan::class, 'jadwal_id');
    }

    public function pembayaran() {
        return $this->hasOne(Pembayaran::class);
    }

    public function ulasan()
    {
        return $this->hasOne(Ulasan::class);
    }
}