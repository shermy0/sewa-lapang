<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Lapangan extends Model
{
    use HasFactory;
    protected $table = 'lapangan';

    protected $fillable = [
        'pemilik_id',
        'nama_lapangan',
        'kategori',
        'lokasi',
        'harga_sewa',
        'durasi_sewa',
        'status',
        'tiket_tersedia',
        'deskripsi',
        'foto',
        'is_verified',
    ];

    protected $casts = [
        'harga_sewa' => 'decimal:2',
        'is_verified' => 'boolean',
    ];

    public function getFotoAttribute($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (blank($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return [$value];
    }

    public function setFotoAttribute($value): void
    {
        if ($value instanceof \Illuminate\Support\Collection) {
            $value = $value->all();
        }

        if (is_array($value)) {
            $cleaned = array_values(array_filter($value, static fn ($path) => filled($path)));
            $this->attributes['foto'] = $cleaned ? json_encode($cleaned) : null;

            return;
        }

        if (blank($value)) {
            $this->attributes['foto'] = null;

            return;
        }

        $this->attributes['foto'] = json_encode([$value]);
    }

    protected function hargaPerJam(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $attributes['harga_sewa'] ?? null,
            set: fn ($value) => ['harga_sewa' => $value],
        );
    }

    // Relasi ke jadwal
    public function jadwal()
    {
        return $this->hasMany(JadwalLapangan::class);
    }
}
