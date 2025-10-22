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
        'tersedia',
    ];

    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }

    public function pemesanan()
    {
        return $this->hasOne(Pemesanan::class, 'jadwal_id');
    }
}

