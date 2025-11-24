<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    use HasFactory;

    protected $table = 'pemesanan'; // <-- pastikan ini sesuai migration
    protected $fillable = [
        'penyewa_id',
        'lapangan_id',
        'jadwal_id',
        'status',
        'kode_tiket', // jangan lupa tambahkan
        'status_scan',
        'waktu_scan',
    ];

    // Pemesanan.php
protected static function boot()
{
    parent::boot();

    static::updated(function ($p) {
        if ($p->status === 'kadaluarsa') {
            if ($p->jadwal && $p->jadwal->tersedia == false) {
                $p->jadwal->update(['tersedia' => true]);
            }
        }
    });
}

    protected $appends = ['booking_status'];

    public function casts()
    {
        return [
            'waktu_scan' => 'datetime'
        ];
    }

    public function user()
{
    return $this->belongsTo(User::class, 'penyewa_id');
}


    public function penyewa()
    {
        return $this->belongsTo(User::class, 'penyewa_id');
    }

    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class, 'lapangan_id');
    }

    public function jadwal()
    {
        return $this->belongsTo(JadwalLapangan::class, 'jadwal_id');
    }

    // public function pembayaran()
    // {
    //     return $this->hasOne(Pembayaran::class, 'pemesanan_id');
    // }
    public function pembayaran() {
        return $this->hasOne(Pembayaran::class);
    }
    
    public function ulasan()
    {
        return $this->hasOne(Ulasan::class);
    }
public function permintaanPerubahan()
{
    return $this->hasOne(PermintaanPerubahan::class)->latestOfMany();
}

public function isExpired()
{
    if ($this->status !== 'menunggu') return false;

    $expiredAt = Carbon::parse($this->created_at)->addMinutes(15);

    return now()->greaterThan($expiredAt);
}


public function getBookingStatusAttribute()
{
    if ($this->status === 'menunggu') {

        // batas otomatis 15 menit
        if ($this->created_at && now()->greaterThan($this->created_at->addMinutes(15))) {
            return 'kadaluarsa';
        }
        return 'menunggu';
    }

    if ($this->status === 'dibayar') {
        return 'dibayar';
    }

    if ($this->status === 'kadaluarsa') {
        return 'kadaluarsa';
    }

    return $this->status;
}


}