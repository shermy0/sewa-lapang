<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartTemp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'nama_penyewa', 'lapangan_id', 'lapangan_name', 'qty', 'harga'
    ];
}
