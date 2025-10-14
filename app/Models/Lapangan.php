<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;

class Lapangan extends Model
{
    use HasFactory;

    protected $table = 'lapangan';

    protected $fillable = [
        'pemilik_id',
        'nama_lapangan',
        'deskripsi',
        'lokasi',
        'harga_per_jam',
        'foto',
        'kategori',
    ];

    /**
     * Pemilik lapangan.
     */
    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    /**
     * Penyewa yang menandai lapangan ini sebagai favorit.
     */
    public function difavoritkanOleh(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorit_lapangan', 'lapangan_id', 'penyewa_id')
            ->withTimestamps();
    }
}
