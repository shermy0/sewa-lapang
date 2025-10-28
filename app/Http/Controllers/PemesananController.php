<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lapangan;
use App\Models\Pemesanan;
use App\Models\JadwalLapangan;
use App\Models\Pembayaran;
use App\Models\PencairanDana; // ✅ tambahkan ini
use Illuminate\Support\Facades\Auth;
use Midtrans\Snap;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;

class PemesananController extends Controller
{
    // ========================== DOWNLOAD TIKET ==========================
    public function downloadTiket($id)
    {
        $pemesanan = Pemesanan::findOrFail($id);
        $pdf = Pdf::loadView('penyewa.tiket-download', compact('pemesanan'))
                  ->setPaper('a4', 'landscape');
        return $pdf->download('Tiket_'.$pemesanan->kode_tiket.'.pdf');
    }

    // ========================== FORM PEMESANAN ==========================
    public function create($lapangan_id)
    {
        $lapangan = Lapangan::findOrFail($lapangan_id);
        $userId = Auth::id();

        $jadwalTersedia = JadwalLapangan::where('lapangan_id', $lapangan_id)
            ->where('tersedia', true)
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        $pemesananPending = Pemesanan::where('penyewa_id', $userId)
            ->where('lapangan_id', $lapangan_id)
            ->where('status', 'menunggu')
            ->with('pembayaran')
            ->first();

        return view('pemesanan.create', compact('lapangan', 'jadwalTersedia', 'pemesananPending'));
    }

    // ========================== TIKET SAYA ==========================
    public function riwayatTiket()
    {
        $userId = Auth::id();
        $sudahDibayar = Pemesanan::where('penyewa_id', $userId)
            ->where('status', 'dibayar')
            ->get();

        return view('penyewa.tiket', compact('sudahDibayar'));
    }

    // ========================== PEMBAYARAN BELUM LUNAS ==========================
    public function riwayatBelum()
    {
        $userId = Auth::id();
        $belumDibayar = Pemesanan::where('penyewa_id', $userId)
            ->where('status', 'menunggu')
            ->get();

        return view('penyewa.pembayaran', compact('belumDibayar'));
    }

    // ========================== RIWAYAT PEMESANAN / BATAL ==========================
    public function riwayatBatal()
    {
        $userId = Auth::id();
        $dibatalkan = Pemesanan::where('penyewa_id', $userId)
            ->whereIn('status', ['batal', 'di-scan'])
            ->get();

        return view('penyewa.riwayat', compact('dibatalkan'));
    }

    // ========================== SNAP TOKEN BARU ==========================
    public function getSnapToken(Request $request)
    {
        $lapangan = Lapangan::findOrFail($request->lapangan_id);
        $jadwal = JadwalLapangan::findOrFail($request->jadwal_id);

        $pemesanan = Pemesanan::firstOrCreate(
            [
                'penyewa_id' => Auth::id(),
                'lapangan_id' => $lapangan->id,
                'jadwal_id' => $jadwal->id,
                'status' => 'menunggu'
            ]
        );
        

try {
    $snapToken = \Midtrans\Snap::getSnapToken([
        'transaction_details' => [
            'order_id' => 'ORDER-' . $pemesanan->id . '-' . time(),
            'gross_amount' => $jadwal->harga_sewa,
        ],
        'customer_details' => [
            'first_name' => Auth::user()->name,
            'email' => Auth::user()->email,
        ],
    ]);
} catch (\Throwable $e) {
    \Log::error('Gagal membuat Snap Token: ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);

    return response()->json([
        'status' => 'error',
        'message' => 'Gagal membuat token Midtrans, coba beberapa saat lagi.',
        'debug' => $e->getMessage(), // boleh dihapus kalau sudah stabil
    ], 200); // <– ubah ke 200 supaya JS tidak masuk ke catch()
}



        Pembayaran::updateOrCreate(
            ['pemesanan_id' => $pemesanan->id],
            [
                'metode' => 'midtrans',
'jumlah' => $jadwal->harga_sewa,
                'status' => 'pending',
                'order_id' => 'ORDER-' . $pemesanan->id,
                'snap_token' => $snapToken,
            ]
        );

        return response()->json([
            'snap_token' => $snapToken,
            'pemesanan_id' => $pemesanan->id
        ]);
    }

    // ========================== PEMBATALAN ==========================
    public function batalkan($id)
    {
        $pemesanan = Pemesanan::findOrFail($id);

        if ($pemesanan->penyewa_id != Auth::id()) {
            abort(403, 'Tidak boleh membatalkan pemesanan orang lain.');
        }

        $pemesanan->update(['status' => 'batal']);

        if ($pemesanan->jadwal) {
            $pemesanan->jadwal->update(['tersedia' => true]);
        }

        if ($pemesanan->pembayaran) {
            $pemesanan->pembayaran->update(['status' => 'batal']);
        }

        return redirect()->back()->with('success', 'Pemesanan berhasil dibatalkan.');
    }

    // ========================== SNAP TOKEN ULANG ==========================
    public function getSnapTokenAgain(Pemesanan $pemesanan)
    {
        try {
            $lapangan = $pemesanan->lapangan;
            $uniqueOrderId = 'ORDER-' . $pemesanan->id . '-' . time();

            $snapToken = Snap::getSnapToken([
                'transaction_details' => [
                    'order_id' => $uniqueOrderId,
                    'gross_amount' => $lapangan->harga_sewa,
                ],
                'customer_details' => [
                    'first_name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                ],
            ]);

            $pembayaran = $pemesanan->pembayaran;
            if (!$pembayaran) {
                $pembayaran = Pembayaran::create([
                    'pemesanan_id' => $pemesanan->id,
                    'metode' => 'midtrans',
                    'jumlah' => $lapangan->harga_sewa,
                    'status' => 'pending',
                    'order_id' => $uniqueOrderId,
                    'snap_token' => $snapToken,
                ]);
            } else {
                $pembayaran->update([
                    'snap_token' => $snapToken,
                    'status' => 'pending',
                    'order_id' => $uniqueOrderId,
                ]);
            }

            return response()->json([
                'snap_token' => $snapToken,
                'pemesanan_id' => $pemesanan->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ========================== SIMPAN PEMESANAN ==========================
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

        $pemesanan = Pemesanan::create([
            'penyewa_id' => Auth::id(),
            'lapangan_id' => $lapangan->id,
            'jadwal_id' => $jadwal->id,
            'status' => 'menunggu',
        ]);

        Pembayaran::create([
            'pemesanan_id' => $pemesanan->id,
            'metode' => 'midtrans',
            'jumlah' => $jadwal->harga_sewa,
            'status' => 'pending',
            'order_id' => 'ORDER-' . time(),
            'snap_token' => $request->snap_token,
        ]);

        return response()->json([
            'snap_token' => $request->snap_token,
            'pemesanan_id' => $pemesanan->id,
        ]);
    }

    // ========================== UPDATE PEMBAYARAN BERHASIL ==========================
public function updateSuccess(Request $request)
{
    $order_id = $request->input('order_id');
    $transaction_status = $request->input('transaction_status');

    $pembayaran = Pembayaran::where('order_id', $order_id)->first();
    if (!$pembayaran) {
        \Log::error('Pembayaran tidak ditemukan untuk order_id: ' . $order_id);
        return;
    }

    $pemesanan = $pembayaran->pemesanan;
    $pemilik = $pemesanan->lapangan->pemilik;
    $rekening = RekeningPemilik::where('pemilik_id', $pemilik->id)->first();
    $bagianPemilik = $pembayaran->total_harga * 0.9; // contoh: 90% ke pemilik

    if ($transaction_status === 'settlement' || $transaction_status === 'capture') {
        $pembayaran->update([
            'status' => 'berhasil',
            'catatan' => 'Pembayaran berhasil dan dana dikirim ke pemilik.',
        ]);

        try {
            // Simpan pencairan ke database
            PencairanDana::create([
                'pembayaran_id' => $pembayaran->id,
                'pemilik_id' => $pemilik->id,
                'bank_tujuan' => $rekening->nama_bank ?? '-',
                'nomor_rekening' => $rekening->nomor_rekening ?? '-',
                'atas_nama' => $rekening->atas_nama ?? '-',
                'jumlah' => $bagianPemilik,
                'status' => 'berhasil',
            ]);

            \Log::info('Pencairan dana berhasil disimpan untuk pembayaran_id: ' . $pembayaran->id);
        } catch (\Exception $e) {
            \Log::error('Gagal simpan pencairan dana: ' . $e->getMessage());
        }
    } else {
        $pembayaran->update([
            'status' => 'gagal',
            'catatan' => 'Pembayaran gagal atau dibatalkan.',
        ]);

        try {
            PencairanDana::create([
                'pembayaran_id' => $pembayaran->id,
                'pemilik_id' => $pemilik->id,
                'bank_tujuan' => $rekening->nama_bank ?? '-',
                'nomor_rekening' => $rekening->nomor_rekening ?? '-',
                'atas_nama' => $rekening->atas_nama ?? '-',
                'jumlah' => $bagianPemilik,
                'status' => 'gagal',
            ]);

            \Log::info('Pencairan dana gagal disimpan untuk pembayaran_id: ' . $pembayaran->id);
        } catch (\Exception $e) {
            \Log::error('Gagal simpan pencairan dana (status gagal): ' . $e->getMessage());
        }
    }
}


    // ========================== GENERATOR KODE TIKET ==========================
    private function generateShortTicketCode()
    {
        $prefix = 'LPN';
        $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        return $prefix . $random;
    }
}