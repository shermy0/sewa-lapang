<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Lapangan extends Model
{
    use HasFactory;

    protected $table = 'lapangan';
protected $fillable = [
    'pemilik_id',
    'id_kategori',   // ⬅️ ini wajib ada
    'nama_lapangan',
    'kategori',
    'tiket_tersedia',
    'deskripsi',
    'lokasi',
    'rating',
    'foto',
    'status',
];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }

    public function pemilik()
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    public function jadwal()
    {
        return $this->hasMany(JadwalLapangan::class, 'lapangan_id');
    }
    public function pemesanan()
    {
        return $this->hasMany(Pemesanan::class, 'lapangan_id');
    }
    protected $guarded = [];

    // Relasi ke ulasan
    public function ulasans()
    {
        return $this->hasMany(Ulasan::class, 'lapangan_id', 'id');
    }

    // Relasi ke pemesanan (opsional)
    public function pemesanans()
    {
        return $this->hasMany(Pemesanan::class, 'lapangan_id', 'id');
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorit_lapangan', 'lapangan_id', 'penyewa_id')
                    ->withTimestamps();
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

    /**
     * Get an array of public URLs for the stored photos.
     */
    public function getFotoUrlsAttribute(): array
    {
        $raw = $this->getRawOriginal('foto');

        if (is_null($raw) || $raw === '') {
            return [];
        }

        if (is_array($raw)) {
            $files = $raw;
        } else {
            $files = json_decode($raw, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $files = [$raw];
            }
        }

        if (!is_array($files)) {
            $files = [$files];
        }

        return collect($files)
            ->map(function ($file) {
                if (empty($file)) {
                    return null;
                }

                if (filter_var($file, FILTER_VALIDATE_URL)) {
                    return $file;
                }

                if (Str::startsWith($file, ['http://', 'https://'])) {
                    return $file;
                }

                if (Str::startsWith($file, ['poto/', 'storage/'])) {
                    return asset($file);
                }

                if (Str::startsWith($file, ['/'])) {
                    return asset(ltrim($file, '/'));
                }

                if (file_exists(public_path('poto/'.$file))) {
                    return asset('poto/'.$file);
                }

                if (file_exists(public_path($file))) {
                    return asset($file);
                }

                return asset('storage/'.$file);
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Convenience accessor for the first photo URL.
     */
    public function getFotoUtamaAttribute(): ?string
    {
        return $this->foto_urls[0] ?? null;
    }
}
