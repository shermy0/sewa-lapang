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
        'deskripsi',
        'lokasi',
        'kategori',
        'foto',
        'status',
        'harga_sewa',
        'durasi_sewa'
    ];

    protected $casts = [
        'foto' => 'array'
    ];

    public function jadwals()
    {
        return $this->hasMany(JadwalLapangan::class, 'lapangan_id');
    }
}
