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
}
