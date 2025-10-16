<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lapangan extends Model
{
    use HasFactory;
    protected $table = 'lapangan';

    protected $fillable = [
        'pemilik_id',
        'nama_lapangan',
        'kategori',
        'lokasi',
        'harga_sewa',
        'durasi_sewa',
        'status',
        'tiket_tersedia',
        'deskripsi',
        'foto',
    ];

    protected $casts = [
        'foto' => 'array', // otomatis decode JSON ke array
        'harga_sewa' => 'decimal:2',
    ];

    // Relasi ke jadwal
    public function jadwal()
    {
        return $this->hasMany(JadwalLapangan::class);
    }
}
