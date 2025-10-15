<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Lapangan;

class Favorit extends Model
{
    use HasFactory;

    protected $table = 'favorit_lapangan';

    protected $fillable = [
        'penyewa_id',
        'lapangan_id',
    ];

    /**
     * Penyewa yang menambahkan favorit.
     */
    public function penyewa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penyewa_id');
    }

    /**
     * Lapangan yang difavoritkan.
     */
    public function lapangan(): BelongsTo
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }
}
