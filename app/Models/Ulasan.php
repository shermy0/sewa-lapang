<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ulasan extends Model
{
    use HasFactory;

    protected $table = 'ulasan'; // nama tabel
    protected $fillable = ['pemesanan_id', 'rating', 'komentar'];

    // Relasi ke pemesanan
    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class);
    }

    // Relasi ke user melalui pemesanan
    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            Pemesanan::class,
            'id',
            'id', 
            'pemesanan_id', 
            'penyewa_id' 
        );
    }
}