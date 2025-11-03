<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanPerubahan extends Model
{
    use HasFactory;

    protected $table = 'permintaan_perubahan';
    protected $fillable = [
        'pemesanan_id',
        'jadwal_lama_id',
        'jadwal_baru_id',
        'status',
        'alasan',
    ];

    // 🔗 Relasi ke pemesanan
    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'pemesanan_id');
    }

    // 🔗 Relasi ke jadwal lama
    public function jadwalLama()
    {
        return $this->belongsTo(JadwalLapangan::class, 'jadwal_lama_id');
    }

    // 🔗 Relasi ke jadwal baru
    public function jadwalBaru()
    {
        return $this->belongsTo(JadwalLapangan::class, 'jadwal_baru_id');
    }
}
