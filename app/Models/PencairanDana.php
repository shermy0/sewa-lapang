<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PencairanDana extends Model
{
    use HasFactory;

    protected $table = 'pencairan_dana';

    protected $fillable = [
        'pembayaran_id',
        'pemilik_id',
        'bank_tujuan',
        'nomor_rekening',
        'atas_nama',
        'jumlah',
        'status',
        'disbursement_id',
    ];

    // Relasi ke pemilik
    public function pemilik()
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    // Relasi ke pembayaran
    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'pembayaran_id');
    }
    
}
