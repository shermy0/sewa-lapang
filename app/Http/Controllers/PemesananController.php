<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lapangan;
use App\Models\Pemesanan;
use App\Models\JadwalLapangan;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Auth;
use Midtrans\Snap;

class PemesananController extends Controller
{
    public function create($lapangan_id)
    {
        $lapangan = Lapangan::findOrFail($lapangan_id);
        $jadwalTersedia = JadwalLapangan::where('lapangan_id', $lapangan_id)
            ->where('tersedia', true)
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        return view('pemesanan.create', compact('lapangan', 'jadwalTersedia'));
    }

    public function riwayat()
    {
        $userId = auth()->id();

        $belumDibayar = Pemesanan::where('penyewa_id', $userId)
            ->whereIn('status', ['menunggu', 'belum_dibayar'])
            ->with(['lapangan', 'jadwal'])
            ->get();

        $sudahDibayar = Pemesanan::where('penyewa_id', $userId)
            ->where('status', 'dibayar')
            ->with(['lapangan', 'jadwal'])
            ->get();

        return view('penyewa.riwayat', compact('belumDibayar', 'sudahDibayar'));
    }

    public function getSnapToken(Request $request)
    {
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $lapanganId = $request->lapangan_id;
        $jadwalId = $request->jadwal_id;

        $lapangan = Lapangan::findOrFail($lapanganId);
        $harga = $lapangan->harga_per_jam;

        // Simpan pemesanan sementara (pakai penyewa_id, bukan user_id)
        $pemesanan = Pemesanan::create([
            'penyewa_id' => Auth::id(),
            'lapangan_id' => $lapanganId,
            'jadwal_id' => $jadwalId,
            'status' => 'belum_dibayar'
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => 'A0-' . uniqid(),
                'gross_amount' => $harga,
            ],
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ]
        ];

        $snapToken = Snap::getSnapToken($params);

        return response()->json([
            'snap_token' => $snapToken,
            'pemesanan_id' => $pemesanan->id
        ]);
    }

    public function updateSuccess(Request $request, $id)
    {
        $pemesanan = Pemesanan::findOrFail($id);

        $pemesanan->update([
            'status' => 'dibayar',
            'kode_tiket' => 'A0' . strtoupper(substr(uniqid(), -5)), // kode pendek
        ]);

        if ($pemesanan->pembayaran) {
            $pemesanan->pembayaran->update([
                'status' => 'berhasil',
            ]);
        }

        return response()->json(['success' => true]);
    }
}

