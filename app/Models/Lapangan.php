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
        'deskripsi',
        'lokasi',
        'harga_per_jam',
        'foto',
    ];

    public function pemilik()
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    public function jadwal()
    {
        return $this->hasMany(JadwalLapangan::class, 'lapangan_id');
    }

    public function pemesanan()
    {
        return $this->hasMany(Pemesanan::class, 'lapangan_id');
    }
    protected $guarded = [];

    // Relasi ke ulasan
    public function ulasans()
    {
        return $this->hasMany(Ulasan::class, 'lapangan_id', 'id');
    }

    // Relasi ke pemesanan (opsional)
    public function pemesanans()
    {
        return $this->hasMany(Pemesanan::class, 'lapangan_id', 'id');
    }
}
