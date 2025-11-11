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

        $jadwal = $pemesanan->jadwal;
        $lapangan = $pemesanan->lapangan;
        $tanggalMain = $pemesanan->created_at->format('d M Y H:i');

        if ($jadwal) {
            $tanggalFormatted = $jadwal->tanggal
                ? $jadwal->tanggal->format('d M Y')
                : null;

            $jamMulai = $jadwal->jam_mulai
                ? Carbon::parse($jadwal->jam_mulai)->format('H:i')
                : null;

            $tanggalMain = trim(collect([$tanggalFormatted, $jamMulai])->filter()->join(' ')) ?: $tanggalMain;
        }

        $jamMain = null;
        if ($jadwal && $jadwal->jam_mulai) {
            $mulai = Carbon::parse($jadwal->jam_mulai)->format('H:i');
            $selesai = $jadwal->jam_selesai
                ? Carbon::parse($jadwal->jam_selesai)->format('H:i')
                : null;
            $jamMain = $selesai ? "{$mulai} - {$selesai}" : $mulai;
        }

        $durasi = $jadwal && $jadwal->durasi_sewa
            ? $jadwal->durasi_sewa . ' menit'
            : null;

        $statusScanLabel = match($pemesanan->status_scan) {
            'sudah_scan' => 'Sudah Scan',
            default => 'Belum Scan',
        };

        $statusPembayaranLabel = match($pemesanan->status) {
            'dibayar' => 'Dibayar',
            'selesai' => 'Selesai',
            'batal' => 'Dibatalkan',
            default => 'Menunggu',
        };

        return response()->json([
            'status' => 'success',
            'data' => [
                'kode_tiket' => $pemesanan->kode_tiket,
                'nama_penyewa' => $pemesanan->penyewa->name,
                'status_scan' => $pemesanan->status_scan,
                'status_scan_label' => $statusScanLabel,
                'status_pembayaran' => $pemesanan->status,
                'status_pembayaran_label' => $statusPembayaranLabel,
                'tanggal_main' => $tanggalMain,
                'waktu_sca n' => $pemesanan?->waktu_scan ? $pemesanan?->waktu_scan?->format('d M Y H:i') : '-',
                'lapangan' => $lapangan ? ($lapangan->nama_lapangan ?? $lapangan->nama ?? '-') : '-',
                'jam_main' => $jamMain,
                'durasi' => $durasi,
            ]
        ]);
    }
}
