<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalLapangan extends Model
{
    use HasFactory;

    protected $table = 'jadwal_lapangan';
    
    protected $fillable = [
        'section_id', // ⬅️ Diubah dari lapangan_id ke section_id
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'tersedia',
        'harga_sewa',
        'durasi_sewa'
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

    // Relationship ke section (bukan langsung ke lapangan)
    public function section()
    {
        return $this->belongsTo(SectionLapangan::class, 'section_id');
    }

    // Relationship ke lapangan melalui section
    public function lapangan()
    {
        return $this->hasOneThrough(
            Lapangan::class,
            SectionLapangan::class,
            'id',           // Local key di section_lapangan
            'id',           // Local key di lapangan
            'section_id',   // Foreign key di jadwal_lapangan
            'lapangan_id'   // Foreign key di section_lapangan
        );
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