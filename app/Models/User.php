<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Lapangan;
use App\Models\Favorit;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, MustVerifyEmailTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'no_hp',
        'foto_profil',
        'pemilik_id',
    ];    

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Lapangan yang difavoritkan oleh penyewa.
     */
    public function favoritLapangan(): BelongsToMany
    {
        return $this->belongsToMany(Lapangan::class, 'favorit_lapangan', 'penyewa_id', 'lapangan_id')
            ->withTimestamps();
    }

    public function lapangan()
    {
        return $this->hasMany(Lapangan::class, 'pemilik_id');
    }

    /**
     * Koleksi favorit sebagai model pivot.
     */
    public function favorit(): HasMany
    {
        return $this->hasMany(Favorit::class, 'penyewa_id');
    }

    protected static function booted(): void
    {
        static::updated(function (self $user) {
            if (
                $user->role === 'pemilik'
                && $user->wasChanged('status')
                && Schema::hasColumn('lapangan', 'is_suspended')
            ) {
                Lapangan::where('pemilik_id', $user->id)
                    ->update(['is_suspended' => $user->status === 'nonaktif']);
            }
        });
    }


public function rekening()
{
    return $this->hasOne(RekeningPemilik::class, 'pemilik_id');
}

public function pencairan()
{
    return $this->hasMany(PencairanDana::class, 'pemilik_id');
}

public function banding()
{
    return $this->hasMany(BandingPemilik::class, 'pemilik_id');
}

public function kategori()
{
    return $this->hasMany(Kategori::class, 'pemilik_id');
}

    // Relasi ke pemilik
    public function pemilik()
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    // Relasi ke penyewa (jika user ini pemilik)
    public function penyewa()
    {
        return $this->hasMany(User::class, 'pemilik_id')
                    ->where('role', 'penyewa');
    }
}