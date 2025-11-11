<?php

namespace App\Http\Controllers;

use App\Models\Pemesanan;
use Carbon\Carbon;

class ScanTiketController extends Controller
{
    // Halaman untuk menampilkan scanner
    public function index()
    {
        return view('pemilik.scan');
    }

    public function verifyTiket($kode)
    {
        $pemesanan = Pemesanan::with(['penyewa', 'jadwal', 'lapangan'])
            ->where('kode_tiket', $kode)
            ->first();

        if (!$pemesanan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tiket tidak ditemukan'
            ]);
        }

        $jadwal = $pemesanan->jadwal;
        $lapangan = $pemesanan->lapangan;

        // Ambil tanggal dan jam jadwal main
        $tanggalMain = $jadwal?->tanggal;
        $jamMulai = $jadwal?->jam_mulai;
        $jamSelesai = $jadwal?->jam_selesai;

        // Gabungkan tanggal dan jam mulai ke dalam satu waktu untuk validasi
$tanggalOnly = Carbon::parse($tanggalMain)->format('Y-m-d');

$waktuMainMulai = $tanggalMain && $jamMulai ? Carbon::parse("$tanggalOnly $jamMulai") : null;
$waktuMainSelesai = $tanggalMain && $jamSelesai ? Carbon::parse("$tanggalOnly $jamSelesai") : null;


        $now = Carbon::now();

        // 💡 Cek kondisi kadaluwarsa
        if ($waktuMainSelesai && $waktuMainSelesai->lt($now)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tiket sudah kadaluwarsa',
                'data' => [
                    'kode_tiket' => $pemesanan->kode_tiket,
                    'nama_penyewa' => $pemesanan->penyewa->name,
                    'lapangan' => $lapangan?->nama_lapangan ?? '-',
                    'tanggal_main' => $waktuMainMulai ? $waktuMainMulai->format('d M Y') : '-',
                    'jam_main' => $waktuMainMulai && $waktuMainSelesai
                        ? $waktuMainMulai->format('H:i') . ' - ' . $waktuMainSelesai->format('H:i')
                        : '-',
                    'durasi' => $jadwal?->durasi_sewa ? $jadwal->durasi_sewa . ' menit' : '-',
                    'status_scan' => 'belum_scan',
                    'status_scan_label' => 'Belum Scan',
                    'status_pembayaran' => $pemesanan->status,
                    'status_pembayaran_label' => ucfirst($pemesanan->status ?? '-'),
                    'waktu_scan' => $pemesanan->waktu_scan ? $pemesanan->waktu_scan->format('d M Y H:i') : '-',
                ]
            ]);
        }

        // 💡 Jika tiket sudah pernah di-scan sebelumnya
        if ($pemesanan->status_scan === 'sudah_scan') {
            return response()->json([
                'status' => 'error',
                'message' => 'Tiket sudah di-scan sebelumnya',
                'data' => [
                    'kode_tiket' => $pemesanan->kode_tiket,
                    'nama_penyewa' => $pemesanan->penyewa->name,
                    'lapangan' => $lapangan?->nama_lapangan ?? '-',
                    'tanggal_main' => $waktuMainMulai ? $waktuMainMulai->format('d M Y') : '-',
                    'jam_main' => $waktuMainMulai && $waktuMainSelesai
                        ? $waktuMainMulai->format('H:i') . ' - ' . $waktuMainSelesai->format('H:i')
                        : '-',
                    'durasi' => $jadwal?->durasi_sewa ? $jadwal->durasi_sewa . ' menit' : '-',
                    'status_scan' => $pemesanan->status_scan,
                    'status_scan_label' => 'Sudah Scan',
                    'status_pembayaran' => $pemesanan->status,
                    'status_pembayaran_label' => ucfirst($pemesanan->status ?? '-'),
                    'waktu_scan' => $pemesanan->waktu_scan ? $pemesanan->waktu_scan->format('d M Y H:i') : '-',
                ]
            ]);
        }

        // ✅ Kalau belum pernah di-scan dan belum kadaluwarsa, update status jadi sudah di-scan
        $pemesanan->update([
            'status_scan' => 'sudah_scan',
            'waktu_scan' => $now,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'kode_tiket' => $pemesanan->kode_tiket,
                'nama_penyewa' => $pemesanan->penyewa->name,
                'lapangan' => $lapangan?->nama_lapangan ?? '-',
                'tanggal_main' => $waktuMainMulai ? $waktuMainMulai->format('d M Y') : '-',
                'jam_main' => $waktuMainMulai && $waktuMainSelesai
                    ? $waktuMainMulai->format('H:i') . ' - ' . $waktuMainSelesai->format('H:i')
                    : '-',
                'durasi' => $jadwal?->durasi_sewa ? $jadwal->durasi_sewa . ' menit' : '-',
                'status_scan' => 'sudah_scan',
                'status_scan_label' => 'Sudah Scan',
                'status_pembayaran' => $pemesanan->status,
                'status_pembayaran_label' => ucfirst($pemesanan->status ?? '-'),
                'waktu_scan' => $now->format('d M Y H:i'),
            ]
        ]);
    }
}
