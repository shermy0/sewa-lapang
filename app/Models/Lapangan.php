<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lapangan extends Model
{
    use HasFactory;

    protected $table = 'lapangan';
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

    /**
     * Pemilik lapangan (relasi ke users).
     */
    public function pemilik()
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }
}
