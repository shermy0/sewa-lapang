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

    public function verifyTiket($kode)
    {
        $pemesanan = \App\Models\Pemesanan::with('penyewa')
            ->where('kode_tiket', $kode)
            ->first();

        if(!$pemesanan){
            return response()->json(['status' => 'error', 'message' => 'Tiket tidak ditemukan']);
        }

        // Update status scan kalau perlu
        if($pemesanan->status_scan === 'belum_scan'){
            $pemesanan->update([
                'status_scan' => 'sudah_scan',
                'waktu_scan' => now()
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'nama_penyewa' => $pemesanan->penyewa->name,
                'status_scan' => $pemesanan->status_scan,
                'status_pembayaran' => $pemesanan->status,
                'tanggal_main' => $pemesanan->created_at->format('d M Y H:i'),
                'waktu_scan' => $pemesanan->waktu_scan ? $pemesanan->waktu_scan->format('d M Y H:i') : '-',
            ]
        ]);
    }
}