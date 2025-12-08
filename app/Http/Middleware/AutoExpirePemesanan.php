<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Pemesanan;
use App\Models\Pembayaran;
use Carbon\Carbon;

class AutoExpirePemesanan
{
    public function handle($request, Closure $next)
    {
        // semua pemesanan yang sudah expired dan masih menunggu → set kadaluarsa
        Pemesanan::where('status', 'menunggu')
            ->where('expires_at', '<', Carbon::now())
            ->update(['status' => 'kadaluarsa']);

        // update pembayaran terkait
        Pembayaran::where('status', 'pending')
            ->whereHas('pemesanan', function($q) {
                $q->where('status', 'kadaluarsa');
            })
            ->update(['status' => 'kadaluarsa']);

        return $next($request);
    }
}
