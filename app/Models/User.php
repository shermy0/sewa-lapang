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
        'no_hp',
        'foto_profil',
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

    /**
     * Koleksi favorit sebagai model pivot.
     */
    public function favorit(): HasMany
    {
        return $this->hasMany(Favorit::class, 'penyewa_id');
    }


public function rekening()
{
    return $this->hasOne(RekeningPemilik::class, 'pemilik_id');
}

public function pencairan()
{
    return $this->hasMany(PencairanDana::class, 'pemilik_id');
}

}
