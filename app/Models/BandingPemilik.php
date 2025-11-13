<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BandingPemilik extends Model
{
    use HasFactory;

    protected $table = 'banding_pemilik';

    protected $fillable = [
        'pemilik_id',
        'alasan',
        'status',
        'tanggapan_admin',
        'ditangani_oleh',
        'ditangani_pada',
        'lampiran_path',
    ];

    protected $casts = [
        'ditangani_pada' => 'datetime',
    ];

    public const STATUSES = ['pending', 'diterima', 'ditolak'];

    public function pemilik()
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    public function penangan()
    {
        return $this->belongsTo(User::class, 'ditangani_oleh');
    }
}
