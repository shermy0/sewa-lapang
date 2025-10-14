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
        'harga_per_jam',
        'status',
        'rating',
        'deskripsi',
        'foto'
    ];

    // Otomatis casting JSON ke array
    protected $casts = [
        'foto' => 'array',
        'harga_per_jam' => 'decimal:2',
        'rating' => 'float',
    ];

    /**
     * Accessor untuk mengambil URL lengkap foto.
     */
    public function getFotoUrlsAttribute()
    {
        // Jika kolom foto kosong/null
        if (empty($this->foto)) {
            return [];
        }

        // Pastikan tetap array walau kadang string
        $fotoArray = is_array($this->foto) ? $this->foto : json_decode($this->foto, true);

        // Kalau ternyata hasil decode gagal, jadikan array kosong
        if (!is_array($fotoArray)) {
            return [];
        }

        // Tambahkan URL lengkap untuk setiap foto
        return array_map(fn($path) => asset('storage/' . $path), $fotoArray);
    }

    /**
     * Relasi ke Pemilik (jika ada)
     */
    public function pemilik()
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    /**
     * Semua jadwal yang dimiliki lapangan.
     */
    public function jadwal()
    {
        return $this->hasMany(JadwalLapangan::class);
    }

    /**
     * Semua pemesanan yang terkait ke lapangan ini.
     */
    public function pemesanan()
    {
        return $this->hasMany(Pemesanan::class);
    }
}
