<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TiketController extends Controller
{
    public function index()
    {
        return view('scan.tiket');
    }

    public function scan(Request $request)
    {
        $request->validate([
            'qr_image' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        // Simpan sementara
        $path = $request->file('qr_image')->store('temp');
        $imagePath = storage_path('app/' . $path);
        $decodedText = $this->decodeQr($imagePath);
        Storage::delete($path);

        if (!$decodedText) {
            return back()->with('error', 'QR tidak terbaca. Pastikan foto jelas.');
        }

        // ✅ DATA DUMMY (tanpa database)
        $dummyTiket = [
            'ABC123' => [
                'kode_pemesanan' => 'ABC123',
                'nama_penyewa' => 'Irma RPL 1',
                'tanggal_main' => '2025-10-15',
                'status' => 'Lunas'
            ],
            'XYZ999' => [
                'kode_pemesanan' => 'XYZ999',
                'nama_penyewa' => 'Sherly RPL 2',
                'tanggal_main' => '2025-10-18',
                'status' => 'Belum Bayar'
            ]
        ];

        if (!isset($dummyTiket[$decodedText])) {
            return back()->with('error', '❌ Tiket tidak ditemukan.');
        }

        $tiket = $dummyTiket[$decodedText];

        if ($tiket['status'] !== 'Lunas') {
            return back()->with('error', "❌ Tiket ditemukan tapi belum dibayar.<br>Kode: {$tiket['kode_pemesanan']}");
        }

        return back()->with('success', "✅ Tiket valid!<br>
            Kode: {$tiket['kode_pemesanan']}<br>
            Nama: {$tiket['nama_penyewa']}<br>
            Tanggal Main: {$tiket['tanggal_main']}");
    }

    private function decodeQr($path)
    {
        try {
            // Ganti ini dengan QR decoder sungguhan kalau mau nanti
            // Sekarang kita dummy aja: ambil nama file tanpa ekstensi
            return pathinfo($path, PATHINFO_FILENAME);
        } catch (\Exception $e) {
            return null;
        }
    }
}
