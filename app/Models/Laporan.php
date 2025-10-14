<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    use HasFactory;

    protected $table = 'laporan'; // supaya tidak otomatis jadi 'laporans'

    protected $fillable = [
        'user_id',
        'pemesanan_id',
        'total_harga',
        'status',
        'tanggal_laporan',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
