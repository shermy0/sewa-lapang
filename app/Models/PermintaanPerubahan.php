<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanPerubahan extends Model
{
    use HasFactory;

    protected $table = 'permintaan_perubahan';

    protected $fillable = [
        'pemesanan_id',
        'section_lama_id',
        'section_baru_id',
        'jadwal_lama_id',
        'jadwal_baru_id',
        'alasan',
        'status',
    ];
public function pemesanan()
{
    return $this->belongsTo(Pemesanan::class, 'pemesanan_id');
}

public function jadwalBaru()
{
    return $this->belongsTo(JadwalLapangan::class, 'jadwal_baru_id');
}

public function jadwalLama()
{
    return $this->belongsTo(JadwalLapangan::class, 'jadwal_lama_id');
}

public function sectionBaru()
{
    return $this->belongsTo(SectionLapangan::class, 'section_baru_id');
}

}
