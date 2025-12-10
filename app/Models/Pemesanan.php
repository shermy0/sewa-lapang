<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Pemesanan extends Model
{
    use HasFactory;

    protected $table = 'pemesanan';

    protected $fillable = [
        'penyewa_id',
        'lapangan_id',
        'jadwal_id',
        'status',
        'kode_tiket',
        'status_scan',
        'waktu_scan',
        'expires_at',
        'nama_komunitas',
    ];

    protected $casts = [
        'waktu_scan' => 'datetime',
    ];

    protected $appends = ['booking_status'];

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

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class);
    }

    public function ulasan()
    {
        return $this->hasOne(Ulasan::class);
    }

public function permintaanPerubahan()
{
    return $this->hasOne(PermintaanPerubahan::class);
}


    public function isExpired()
    {
        if ($this->status !== 'menunggu') return false;

        $expiredAt = now()->parse($this->created_at)->addMinutes(15);

        return now()->greaterThan($expiredAt);
    }

    public function getBookingStatusAttribute()
    {
        if ($this->status === 'menunggu') {
            if ($this->created_at && now()->greaterThan($this->created_at->addMinutes(15))) {
                return 'kadaluarsa';
            }
            return 'menunggu';
        }

        return $this->status;
    }

    public function checkExpired()
    {
        if ($this->status === 'menunggu' && $this->expires_at && $this->expires_at < now()) {

            $this->update(['status' => 'kadaluarsa']);

            if ($this->jadwal) {
                $this->jadwal->update(['tersedia' => true]);
            }

            if ($this->pembayaran) {
                $this->pembayaran->update(['status' => 'kadaluarsa']);
            }
        }
    }
}
