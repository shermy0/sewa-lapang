<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalLapangan extends Model
{
    use HasFactory;

    protected $table = 'jadwal_lapangan';
    protected $fillable = [
        'lapangan_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'harga_sewa', // Ditambahkan
        'durasi_sewa', // Ditambahkan
        'tersedia'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tersedia' => 'boolean',
        'harga_sewa' => 'decimal:2',
    ];

    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }
}