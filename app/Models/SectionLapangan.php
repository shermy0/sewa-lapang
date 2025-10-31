<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionLapangan extends Model
{
    use HasFactory;

    protected $table = 'section_lapangan';
    
    protected $fillable = [
        'lapangan_id',
        'nama_section',
        'deskripsi'
    ];

    // Relationship ke lapangan
    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }

    // Relationship ke jadwal
    public function jadwal()
    {
        return $this->hasMany(JadwalLapangan::class, 'section_id');
    }

    // Accessor untuk mendapatkan nama lapangan melalui section
    public function getNamaLapanganAttribute()
    {
        return $this->lapangan->nama_lapangan ?? null;
    }
}