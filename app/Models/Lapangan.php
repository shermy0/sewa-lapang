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
        'status'
        // harga_sewa dan durasi_sewa dihapus
    ];

    public function jadwal()
    {
        return $this->hasMany(JadwalLapangan::class, 'lapangan_id');
    }

    public function getFotoAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return [$value];
    }

    public function setFotoAttribute($value)
    {
        if (is_null($value)) {
            $this->attributes['foto'] = null;
            return;
        }

        if (is_array($value)) {
            $cleaned = array_values(array_filter($value, fn ($item) => !is_null($item) && $item !== ''));
            $this->attributes['foto'] = $cleaned ? json_encode($cleaned) : null;
            return;
        }

        $this->attributes['foto'] = json_encode([$value]);
    }
}