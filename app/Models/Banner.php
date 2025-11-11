<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $table = 'banner'; // nama tabel di database

    protected $fillable = [
        'judul',
        'gambar',
        'status',
    ];
}
