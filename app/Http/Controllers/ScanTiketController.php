<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pemesanan;

class ScanTiketController extends Controller
{
    // Halaman untuk menampilkan scanner
    public function index()
    {
        return view('pemilik.scan');
    }

    // Proses verifikasi kode tiket hasil scan
    public function verifyTiket($kode)
    {
        $pemesanan = Pemesanan::where('kode_tiket', $kode)->first();
        if (!$pemesanan) {
            return redirect()->back()->with('error', 'Tiket tidak ditemukan!');
        }

        // update status scan
        $pemesanan->status_scan = 'sudah_scan';
        $pemesanan->waktu_scan = now();
        $pemesanan->save();

        return redirect()->back()->with('success', 'Tiket berhasil discan!');
    }
}
