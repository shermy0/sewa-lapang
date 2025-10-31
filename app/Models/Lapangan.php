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
        'id_kategori',
        'nama_lapangan',
        'kategori',
        'tiket_tersedia',
        'deskripsi',
        'lokasi',
        'rating',
        'foto',
        'status',
        'is_verified'
    ];

    protected $casts = [
        'foto' => 'array',
        'rating' => 'double',
        'is_verified' => 'boolean',
        'tiket_tersedia' => 'integer'
    ];

    // Relationship ke kategori
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }

    // Relationship ke pemilik
    public function pemilik()
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    // Relationship ke sections (yang benar berdasarkan struktur database)
    public function sections()
    {
        return $this->hasMany(SectionLapangan::class, 'lapangan_id');
    }

    // Relationship ke jadwal melalui sections
    public function jadwal()
    {
        return $this->hasManyThrough(
            JadwalLapangan::class,
            SectionLapangan::class,
            'lapangan_id', // Foreign key di section_lapangan
            'section_id',  // Foreign key di jadwal_lapangan
            'id',          // Local key di lapangan
            'id'           // Local key di section_lapangan
        );
    }

    // Relationship ke pemesanan
    public function pemesanan()
    {
        return $this->hasMany(Pemesanan::class, 'lapangan_id');
    }

    // Relasi ke ulasan
    public function ulasans()
    {
        return $this->hasMany(Ulasan::class, 'lapangan_id');
    }

    // Relasi favorit
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorit_lapangan', 'lapangan_id', 'penyewa_id')
                    ->withTimestamps();
    }

    // Accessor untuk foto (tetap seperti sebelumnya)
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
        $fotos = $this->foto;

        if (empty($fotos)) {
            return [];
        }

        return collect($fotos)
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