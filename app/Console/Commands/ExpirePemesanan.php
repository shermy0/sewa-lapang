<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pemesanan;
use Carbon\Carbon;

class ExpirePemesanan extends Command
{
    protected $signature = 'pemesanan:expire';
    protected $description = 'Set pemesanan yang melewati batas waktu menjadi kadaluarsa';

    public function handle()
    {
        $now = Carbon::now('Asia/Jakarta');

        $expired = Pemesanan::where('status', 'menunggu')
            ->where('expires_at', '<', $now)
            ->get();

        foreach ($expired as $p) {
            $p->update(['status' => 'kadaluarsa']);

            // buka jadwal kembali
            if ($p->jadwal) {
                $p->jadwal->update(['tersedia' => true]);
            }

            // update pembayaran
            if ($p->pembayaran) {
                $p->pembayaran->update(['status' => 'kadaluarsa']);
            }
        }

        $this->info("Pemesanan kadaluarsa sudah diproses.");
    }
}
