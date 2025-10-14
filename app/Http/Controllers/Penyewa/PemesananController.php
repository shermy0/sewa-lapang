<?php

namespace App\Http\Controllers\Penyewa;

use App\Http\Controllers\Controller;
use App\Models\Lapangan;
use App\Models\Pemesanan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PemesananController extends Controller
{
    /**
     * Simpan pemesanan baru untuk penyewa.
     */
    public function store(Request $request, Lapangan $lapangan): RedirectResponse
    {
        if (! Schema::hasTable('pemesanan')) {
            return back()->with('error', 'Fitur pemesanan belum tersedia. Hubungi admin.');
        }

        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i'],
        ]);

        $mulai = Carbon::createFromFormat('H:i', $validated['jam_mulai']);
        $selesai = Carbon::createFromFormat('H:i', $validated['jam_selesai']);

        if ($selesai->lessThanOrEqualTo($mulai)) {
            return back()->with('error', 'Jam selesai harus lebih besar dari jam mulai.');
        }

        $durasiJam = $mulai->floatDiffInHours($selesai);
        $totalHarga = round($durasiJam * (float) $lapangan->harga_per_jam, 2);

        Pemesanan::create([
            'lapangan_id' => $lapangan->getKey(),
            'penyewa_id' => $request->user()->getKey(),
            'tanggal' => $validated['tanggal'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
            'status' => 'menunggu',
            'total_harga' => $totalHarga,
        ]);

        return redirect()
            ->route('penyewa.detail', $lapangan)
            ->with('success', 'Pemesanan berhasil dibuat. Silakan tunggu konfirmasi.');
    }
}
