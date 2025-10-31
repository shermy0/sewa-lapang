<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanPenyalahgunaan extends Model
{
    use HasFactory;

    public const STATUSES = ['pending', 'diproses', 'ditutup'];

    protected $table = 'laporan_penyalahgunaan';

    protected $fillable = [
        'pelapor_id',
        'terlapor_id',
        'lapangan_id',
        'kategori',
        'deskripsi',
        'status',
        'catatan_admin',
        'ditangani_oleh',
        'ditangani_pada',
    ];

    protected $casts = [
        'ditangani_pada' => 'datetime',
    ];

    public function pelapor()
    {
        return $this->belongsTo(User::class, 'pelapor_id');
    }

    public function terlapor()
    {
        return $this->belongsTo(User::class, 'terlapor_id');
    }

    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }

    public function penangan()
    {
        return $this->belongsTo(User::class, 'ditangani_oleh');
    }

    public function scopeStatus($query, ?string $status)
    {
        if (! $status || ! in_array($status, self::STATUSES, true)) {
            return $query;
        }

        return $query->where('status', $status);
    }
}
