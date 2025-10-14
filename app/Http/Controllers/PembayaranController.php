<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pemesanan;
use App\Models\Pembayaran;

class PembayaranController extends Controller
{
    public function show($pemesanan_id)
    {
        $pemesanan = Pemesanan::with(['lapangan', 'jadwal'])->findOrFail($pemesanan_id);

        return view('pembayaran.show', compact('pemesanan'));
    }

    public function process(Request $request, $pemesanan_id)
    {
        $pemesanan = Pemesanan::findOrFail($pemesanan_id);

        $request->validate([
            'metode' => 'required',
        ]);

        // Simulasi pembayaran berhasil
        Pembayaran::create([
            'pemesanan_id' => $pemesanan->id,
            'metode' => $request->metode,
            'jumlah' => $pemesanan->lapangan->harga_per_jam,
            'status' => 'berhasil',
            'order_id' => 'ORDER-' . time(),
            'tanggal_pembayaran' => now(),
        ]);

        $pemesanan->update(['status' => 'dibayar']);

        return redirect()->route('penyewa.dashboard')
                         ->with('success', 'Pembayaran berhasil dilakukan!');
    }

public function store(Request $request)
{
    $request->validate([
        'lapangan_id' => 'required|exists:lapangan,id',
        'jadwal_id' => 'required|exists:jadwal_lapangan,id',
        'snap_token' => 'required',
    ]);

    $jadwal = JadwalLapangan::findOrFail($request->jadwal_id);
    if (!$jadwal->tersedia) {
        return back()->with('error', 'Jadwal sudah dipesan!');
    }

    $lapangan = Lapangan::findOrFail($request->lapangan_id);

    // === Tentukan status pemesanan ===
    $statusPemesanan = config('app.env') === 'local' ? 'simulasi' : 'dibayar';
    $statusPembayaran = config('app.env') === 'local' ? 'simulasi' : 'berhasil';

    $pemesanan = Pemesanan::create([
        'penyewa_id' => Auth::id(),
        'lapangan_id' => $lapangan->id,
        'jadwal_id' => $jadwal->id,
        'status' => $statusPemesanan,
    ]);

    $jadwal->update(['tersedia' => false]);

    Pembayaran::create([
        'pemesanan_id' => $pemesanan->id,
        'metode' => 'midtrans',
        'jumlah' => $lapangan->harga_per_jam,
        'status' => $statusPembayaran,
        'order_id' => 'ORDER-' . time(),
        'tanggal_pembayaran' => now(),
    ]);

    $msg = config('app.env') === 'local' ? 
        'Simulasi pemesanan berhasil!' : 
        'Pemesanan dan pembayaran berhasil!';

    return redirect()->route('penyewa.dashboard')->with('success', $msg);
}


}