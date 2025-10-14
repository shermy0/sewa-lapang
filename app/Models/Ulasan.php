<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ulasan extends Model
{
    use HasFactory;

    protected $table = 'ulasan';

    protected $fillable = [
        'pemesanan_id',
        'penyewa_id',
        'rating',
        'komentar',
    ];

    /**
     * Relasi ke pemesanan.
     */
    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class);
    }

    /**
     * Relasi ke penyewa pemberi ulasan.
     */
    public function penyewa()
    {
        return $this->belongsTo(User::class, 'penyewa_id');
    }

    /**
     * Relasi ke lapangan melalui pemesanan.
     */
    public function lapangan()
    {
        return $this->hasOneThrough(
            Lapangan::class,
            Pemesanan::class,
            'id',
            'id',
            'pemesanan_id',
            'lapangan_id'
        );
    }
}
