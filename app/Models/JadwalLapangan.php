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
        'harga_sewa', // Ditambahkan
        'durasi_sewa', // Ditambahkan
        'tersedia'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tersedia' => 'boolean',
        'harga_sewa' => 'decimal:2',
        'durasi_sewa' => 'integer',
    ];

    protected $appends = [
        'harga_total',
    ];

    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }

    public function pemesanan()
    {
        return $this->hasOne(Pemesanan::class, 'jadwal_id');
    }

        public function getHargaTotalAttribute(): float
    {
        $durasiMenit = (int) $this->durasi_sewa;
        $durasiJam = $durasiMenit > 0 ? $durasiMenit / 60 : 0;

        $hargaPerJam = (float) $this->harga_sewa;

        return round($hargaPerJam * $durasiJam, 2);
    }
}
