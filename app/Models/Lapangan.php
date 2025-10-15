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
        'harga_sewa',
        'durasi_sewa',
        'status',
        'rating',
        'tiket_tersedia',  // Ganti dari kapasitas
        'fasilitas',
        'deskripsi',
        'foto',
    ];

    protected $casts = [
        'harga_per_jam' => 'decimal:2',
        'harga_sewa' => 'decimal:2',
        'rating' => 'decimal:1',
        'durasi_sewa' => 'integer',
        'tiket_tersedia' => 'integer',  // Ganti dari kapasitas
    ];

    /**
     * Relasi ke User (Pemilik)
     */
    public function pemilik()
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    /**
     * Relasi ke JadwalLapangan
     */
    public function jadwal()
    {
        return $this->hasMany(JadwalLapangan::class, 'lapangan_id');
    }

    /**
     * Relasi ke Booking
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'lapangan_id');
    }

    /**
     * Accessor untuk mendapatkan array foto
     */
    public function getFotoArrayAttribute()
    {
        return json_decode($this->foto, true) ?? [];
    }

    /**
     * Accessor untuk mendapatkan foto pertama
     */
    public function getFotoPertamaAttribute()
    {
        $fotos = $this->foto_array;
        return !empty($fotos) ? $fotos[0] : null;
    }

    /**
     * Scope untuk filter kategori
     */
    public function scopeKategori($query, $kategori)
    {
        return $query->where('kategori', 'like', '%' . $kategori . '%');
    }

    /**
     * Scope untuk filter status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk lapangan yang memiliki tiket tersedia
     */
    public function scopeTiketTersedia($query)
    {
        return $query->where('tiket_tersedia', '>', 0);
    }

    /**
     * Scope untuk lapangan dengan rating minimal
     */
    public function scopeMinRating($query, $rating)
    {
        return $query->where('rating', '>=', $rating);
    }

    /**
     * Method untuk mengurangi tiket
     */
    public function kurangiTiket($jumlah = 1)
    {
        if ($this->tiket_tersedia >= $jumlah) {
            $this->tiket_tersedia -= $jumlah;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Method untuk menambah tiket
     */
    public function tambahTiket($jumlah = 1)
    {
        $this->tiket_tersedia += $jumlah;
        $this->save();
        return true;
    }

    /**
     * Method untuk cek apakah tiket masih tersedia
     */
    public function isTiketTersedia()
    {
        return $this->tiket_tersedia > 0;
    }

    /**
     * Method untuk mendapatkan persentase tiket tersedia
     */
    public function getPersentaseTiket($maxTiket = 100)
    {
        if ($this->tiket_tersedia <= 0) {
            return 0;
        }
        return min(($this->tiket_tersedia / $maxTiket) * 100, 100);
    }

    /**
     * Method untuk mendapatkan warna progress bar tiket
     */
    public function getWarnaTiket()
    {
        $persentase = $this->getPersentaseTiket();
        
        if ($persentase > 50) {
            return 'success';
        } elseif ($persentase > 20) {
            return 'warning';
        } else {
            return 'danger';
        }
    }
}
