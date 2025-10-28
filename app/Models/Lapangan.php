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
        'id_kategori',   // relasi kategori
        'nama_lapangan',
        'tiket_tersedia',
        'deskripsi',
        'lokasi',
        'rating',
        'foto',
        'status',
    ];

    // 🔗 Relasi ke Kategori
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }

    // 🔗 Relasi ke Pemilik (User)
    public function pemilik()
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    // 🔗 Relasi ke Jadwal Lapangan
    public function jadwal()
    {
        return $this->hasMany(JadwalLapangan::class, 'lapangan_id');
    }

    // 🔗 Relasi ke Pemesanan
    public function pemesanan()
    {
        return $this->hasMany(Pemesanan::class, 'lapangan_id');
    }

    // 🔗 Relasi ke Ulasan
    public function ulasans()
    {
        return $this->hasMany(Ulasan::class, 'lapangan_id');
    }

    // 🔗 Relasi ke Favorit (Many to Many)
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorit_lapangan', 'lapangan_id', 'penyewa_id')
                    ->withTimestamps();
    }

    // 🖼️ Mutator: handle foto dalam format JSON
    public function getFotoAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : [$value];
    }

    public function setFotoAttribute($value)
    {
        if (is_null($value)) {
            $this->attributes['foto'] = null;
        } elseif (is_array($value)) {
            $this->attributes['foto'] = json_encode(array_values(array_filter($value)));
        } else {
            $this->attributes['foto'] = json_encode([$value]);
        }
    }
}
