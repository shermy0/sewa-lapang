<?php

namespace App\Http\Controllers;

use App\Models\Pemesanan;
use Carbon\Carbon;

class ScanTiketController extends Controller
{
    // Halaman untuk menampilkan scanner
    public function index()
    {
        $user = auth()->user();

        if ($user && $user->role === 'petugas') {
            return view('petugas.scan');
        }

        return view('pemilik.scan');
    }

    public function verifyTiket($kode)
    {
        $checkpoint = request('checkpoint') === 'lapang' ? 'lapang' : 'gor';
        $pemesanan = Pemesanan::with(['penyewa', 'jadwal', 'lapangan'])
            ->where('kode_tiket', $kode)
            ->first();

        if (!$pemesanan) {
            return response()->json([
                'status' => 'error',
                'status_flag' => 'not_found',
                'message' => 'Tiket tidak ditemukan'
            ]);
        }

        $jadwal = $pemesanan->jadwal;
        $lapangan = $pemesanan->lapangan;
        $statusScan = $pemesanan->status_scan ?? 'belum_scan';

        $tanggalMain = $jadwal?->tanggal;
        $jamMulai = $jadwal?->jam_mulai;
        $jamSelesai = $jadwal?->jam_selesai;

        $tanggalOnly = $tanggalMain ? Carbon::parse($tanggalMain)->format('Y-m-d') : null;
        $waktuMainMulai = $tanggalOnly && $jamMulai ? Carbon::parse("$tanggalOnly $jamMulai", 'Asia/Jakarta') : null;
        $waktuMainSelesai = $tanggalOnly && $jamSelesai ? Carbon::parse("$tanggalOnly $jamSelesai", 'Asia/Jakarta') : null;


        $now = Carbon::now('Asia/Jakarta');
        $earlyLimit = $waktuMainMulai ? $waktuMainMulai->copy()->subMinutes(15) : null;

        if ($pemesanan->status !== 'dibayar') {
            return response()->json([
                'status' => 'error',
                'status_flag' => 'unpaid',
                'message' => 'Tiket belum dibayar atau tidak aktif',
            ]);
        }

        // 💡 Cek kondisi kadaluwarsa
        if ($waktuMainSelesai && $waktuMainSelesai->lt($now)) {
            return response()->json([
                'status' => 'error',
                'status_flag' => 'expired',
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
                    'status_scan' => $statusScan,
                    'status_scan_label' => 'Belum Scan',
                    'checkpoint' => $checkpoint,
                    'status_pembayaran' => $pemesanan->status,
                    'status_pembayaran_label' => ucfirst($pemesanan->status ?? '-'),
                    'waktu_scan' => $pemesanan->waktu_scan ? $pemesanan->waktu_scan->format('d M Y H:i') : '-',
                ]
            ]);
        }

        $hasScanLobby = in_array($statusScan, ['scan_lobby', 'sudah_scan'], true);
        $hasScanLapang = $statusScan === 'sudah_scan';

        // 💡 Jika double scan di checkpoint yang sama
        if ($checkpoint === 'gor' && $hasScanLobby) {
            return response()->json([
                'status' => 'error',
                'status_flag' => 'double_scan_lobby',
                'message' => 'Tiket sudah di-scan di pintu GOR',
                'data' => [
                    'kode_tiket' => $pemesanan->kode_tiket,
                    'nama_penyewa' => $pemesanan->penyewa->name,
                    'lapangan' => $lapangan?->nama_lapangan ?? '-',
                    'tanggal_main' => $waktuMainMulai ? $waktuMainMulai->format('d M Y') : '-',
                    'jam_main' => $waktuMainMulai && $waktuMainSelesai
                        ? $waktuMainMulai->format('H:i') . ' - ' . $waktuMainSelesai->format('H:i')
                        : '-',
                    'durasi' => $jadwal?->durasi_sewa ? $jadwal->durasi_sewa . ' menit' : '-',
                    'status_scan' => $statusScan,
                    'status_scan_label' => 'Sudah Scan GOR',
                    'checkpoint' => $checkpoint,
                    'status_pembayaran' => $pemesanan->status,
                    'status_pembayaran_label' => ucfirst($pemesanan->status ?? '-'),
                    'waktu_scan' => $pemesanan->waktu_scan ? $pemesanan->waktu_scan->format('d M Y H:i') : '-',
                ]
            ]);
        }

        if ($checkpoint === 'lapang' && $hasScanLapang) {
            return response()->json([
                'status' => 'error',
                'status_flag' => 'double_scan_lapang',
                'message' => 'Tiket sudah di-scan di pintu lapangan',
                'data' => [
                    'kode_tiket' => $pemesanan->kode_tiket,
                    'nama_penyewa' => $pemesanan->penyewa->name,
                    'lapangan' => $lapangan?->nama_lapangan ?? '-',
                    'tanggal_main' => $waktuMainMulai ? $waktuMainMulai->format('d M Y') : '-',
                    'jam_main' => $waktuMainMulai && $waktuMainSelesai
                        ? $waktuMainMulai->format('H:i') . ' - ' . $waktuMainSelesai->format('H:i')
                        : '-',
                    'durasi' => $jadwal?->durasi_sewa ? $jadwal->durasi_sewa . ' menit' : '-',
                    'status_scan' => $statusScan,
                    'status_scan_label' => 'Sudah Scan Lapang',
                    'checkpoint' => $checkpoint,
                    'status_pembayaran' => $pemesanan->status,
                    'status_pembayaran_label' => ucfirst($pemesanan->status ?? '-'),
                    'waktu_scan' => $pemesanan->waktu_scan ? $pemesanan->waktu_scan->format('d M Y H:i') : '-',
                ]
            ]);
        }

        if ($earlyLimit && $now->lt($earlyLimit)) {
            return response()->json([
                'status' => 'error',
                'status_flag' => 'too_early',
                'message' => 'Check-in baru bisa 15 menit sebelum jadwal mulai.',
                'data' => [
                    'kode_tiket' => $pemesanan->kode_tiket,
                    'nama_penyewa' => $pemesanan->penyewa->name,
                    'lapangan' => $lapangan?->nama_lapangan ?? '-',
                    'tanggal_main' => $waktuMainMulai ? $waktuMainMulai->format('d M Y') : '-',
                    'jam_main' => $waktuMainMulai && $waktuMainSelesai
                        ? $waktuMainMulai->format('H:i') . ' - ' . $waktuMainSelesai->format('H:i')
                        : '-',
                    'durasi' => $jadwal?->durasi_sewa ? $jadwal->durasi_sewa . ' menit' : '-',
                    'status_scan' => $statusScan,
                    'status_scan_label' => 'Belum Scan',
                    'checkpoint' => $checkpoint,
                    'status_pembayaran' => $pemesanan->status,
                    'status_pembayaran_label' => ucfirst($pemesanan->status ?? '-'),
                    'waktu_scan' => $pemesanan->waktu_scan ? $pemesanan->waktu_scan->format('d M Y H:i') : '-',
                ]
            ]);
        }

        // ✅ Update status sesuai checkpoint
        if ($checkpoint === 'gor') {
            $pemesanan->update([
                'status_scan' => 'scan_lobby',
                'waktu_scan' => $now,
            ]);

            return response()->json([
                'status' => 'success',
                'status_flag' => 'valid_lobby',
                'data' => [
                    'kode_tiket' => $pemesanan->kode_tiket,
                    'nama_penyewa' => $pemesanan->penyewa->name,
                    'lapangan' => $lapangan?->nama_lapangan ?? '-',
                    'tanggal_main' => $waktuMainMulai ? $waktuMainMulai->format('d M Y') : '-',
                    'jam_main' => $waktuMainMulai && $waktuMainSelesai
                        ? $waktuMainMulai->format('H:i') . ' - ' . $waktuMainSelesai->format('H:i')
                        : '-',
                    'durasi' => $jadwal?->durasi_sewa ? $jadwal->durasi_sewa . ' menit' : '-',
                    'status_scan' => 'scan_lobby',
                    'status_scan_label' => 'Sudah Scan GOR',
                    'checkpoint' => $checkpoint,
                    'status_pembayaran' => $pemesanan->status,
                    'status_pembayaran_label' => ucfirst($pemesanan->status ?? '-'),
                    'waktu_scan' => $now->format('d M Y H:i'),
                ]
            ]);
        }

        // checkpoint lapang
        $pemesanan->update([
            'status_scan' => 'sudah_scan',
            'waktu_scan' => $now,
        ]);

        return response()->json([
            'status' => 'success',
            'status_flag' => 'valid_lapang',
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
                'status_scan_label' => 'Sudah Scan Lapang',
                'checkpoint' => $checkpoint,
                'status_pembayaran' => $pemesanan->status,
                'status_pembayaran_label' => ucfirst($pemesanan->status ?? '-'),
                'waktu_scan' => $now->format('d M Y H:i'),
            ]
        ]);
    }
}
