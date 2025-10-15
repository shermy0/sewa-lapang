<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ulasan extends Model
{
    use HasFactory;

    protected $table = 'ulasan';
    protected $guarded = [];

    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'penyewa_id');
    }

    public function pemesanan()
    {
        return $this->belongsTo(\App\Models\Pemesanan::class, 'pemesanan_id');
    }
}
