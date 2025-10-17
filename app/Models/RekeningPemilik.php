<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekeningPemilik extends Model
{
    use HasFactory;

    protected $table = 'rekening_pemilik';

    protected $fillable = [
        'pemilik_id',
        'nama_bank',
        'nomor_rekening',
        'atas_nama',
    ];

    // Relasi ke User (pemilik)
    public function pemilik()
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }
}
