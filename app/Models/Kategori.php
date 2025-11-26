<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategori'; // Nama tabel di database

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
        'pemilik_id',
    ];

    // Relasi ke tabel lapangan (satu kategori punya banyak lapangan)
    public function lapangan()
    {
        return $this->hasMany(Lapangan::class, 'id_kategori');
    }

    public function pemilik()
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }
}
