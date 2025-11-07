<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lapangan;
use App\Models\Pemesanan;
use App\Models\JadwalLapangan;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Auth;
use Midtrans\Snap;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
class PemesananController extends Controller
{
    
public function boot(): void
{
    Carbon::setLocale('id');
}


public function setujuiPermintaan($id)
{
    $permintaan = \App\Models\PermintaanPerubahan::with(['pemesanan', 'jadwalLama', 'jadwalBaru'])->findOrFail($id);

    // Cegah yang bukan pemilik lapangan
    if ($permintaan->pemesanan->lapangan->pemilik_id != Auth::id()) {
        abort(403, 'Tidak punya akses.');
    }

    // Update status permintaan
    $permintaan->update(['status' => 'disetujui']);

    // Ubah jadwal lama jadi tersedia lagi
    if ($permintaan->jadwalLama) {
        $permintaan->jadwalLama->update(['tersedia' => true]);
    }

    // Tandai jadwal baru jadi tidak tersedia
    if ($permintaan->jadwalBaru) {
        $permintaan->jadwalBaru->update(['tersedia' => false]);
    }

    // Update data pemesanan ke jadwal baru
    $pemesanan = $permintaan->pemesanan;
    $pemesanan->update([
        'jadwal_id' => $permintaan->jadwal_baru_id,
    ]);

    // ✅ refresh relasi agar ambil jadwal & section baru
    $pemesanan->load('jadwal.section');

    return response()->json([
        'success' => true,
        'message' => 'Permintaan perubahan telah disetujui.',
        'pemesanan' => $pemesanan, // kirim data baru kalau perlu di-ajax
    ]);
}

    public function getDetailPermintaan($id)
{
    $permintaan = \App\Models\PermintaanPerubahan::with(['sectionBaru', 'jadwalBaru', 'pemesanan.lapangan'])
        ->findOrFail($id);

    // Cegah akses data orang lain
    if ($permintaan->pemesanan->penyewa_id != Auth::id()) {
        abort(403);
    }

    return response()->json([
        'id' => $permintaan->id,
        'status' => $permintaan->status,
        'alasan' => $permintaan->alasan,
        'pemesanan_id' => $permintaan->pemesanan_id,
        'lapangan_id' => $permintaan->pemesanan->lapangan_id,
        'section_baru' => $permintaan->sectionBaru,
        'jadwal_baru' => $permintaan->jadwalBaru,
    ]);
}

    public function batalkanPermintaan($id)
{
    $permintaan = \App\Models\PermintaanPerubahan::findOrFail($id);
    if ($permintaan->pemesanan->penyewa_id != Auth::id()) {
        abort(403);
    }

    $permintaan->delete();

    return response()->json(['success' => true]);
}

    public function ajukanPerubahan(Request $request, $pemesananId)
{
    $request->validate([
        'section_baru_id' => 'required|exists:section_lapangan,id',
        'jadwal_baru_id' => 'required|exists:jadwal_lapangan,id',
        'alasan' => 'nullable|string|max:255',
    ]);

    $pemesanan = Pemesanan::findOrFail($pemesananId);
    if ($pemesanan->penyewa_id != Auth::id()) {
        abort(403);
    }

    \App\Models\PermintaanPerubahan::create([
        'pemesanan_id' => $pemesanan->id,
        'section_lama_id' => $pemesanan->jadwal->section_id,
        'section_baru_id' => $request->section_baru_id,
        'jadwal_lama_id' => $pemesanan->jadwal_id,
        'jadwal_baru_id' => $request->jadwal_baru_id,
        'alasan' => $request->alasan,
        'status' => 'menunggu',
    ]);

    return response()->json(['success' => true]);
}

public function getSectionsByLapangan($lapangan_id)
{
    $lapangan = \App\Models\Lapangan::with('sections')->findOrFail($lapangan_id);
    return response()->json($lapangan->sections);
}


public function downloadTiket($id)
{
    $pemesanan = Pemesanan::with([
        'lapangan',
        'jadwal.section',
        'user'
    ])->findOrFail($id);

    $pdf = Pdf::loadView('penyewa.tiket-download', compact('pemesanan'))
        ->setPaper('a4', 'landscape')
        ->setOption('margin-top', 0)
        ->setOption('margin-bottom', 0)
        ->setOption('margin-left', 0)
        ->setOption('margin-right', 0)
        ->setOption('enable-smart-shrinking', true);

    return $pdf->download('Tiket_'.$pemesanan->kode_tiket.'.pdf');
}

public function create($lapangan_id)
{
    $lapangan = Lapangan::with('sections')->findOrFail($lapangan_id);
    $userId = Auth::id();

    // Cek apakah user masih punya pemesanan menunggu
    $pemesananPending = Pemesanan::where('penyewa_id', $userId)
        ->where('lapangan_id', $lapangan_id)
        ->where('status', 'menunggu')
        ->with(['pembayaran', 'jadwal'])
        ->first();

    return view('pemesanan.create', compact('lapangan', 'pemesananPending'));
}
public function getJadwalBySection($section_id)
{
    $jadwal = JadwalLapangan::where('section_id', $section_id)
        ->orderBy('tanggal')
        ->orderBy('jam_mulai')
        ->get();

    return response()->json($jadwal);
}

    // ========================== HALAMAN TIKET ==========================
public function riwayatTiket()
{
    $userId = Auth::id();

    $sudahDibayar = Pemesanan::with([
        'lapangan',
        'jadwal.section',
        'permintaanPerubahan.jadwalBaru.section',
        'permintaanPerubahan.sectionBaru',
    ])
    ->where('penyewa_id', $userId)
    ->where('status', 'dibayar')
    ->get()
    ->map(function ($p) {
        // kalau ada perubahan disetujui, pastikan jadwalnya sudah ke-update
        if ($p->permintaanPerubahan && $p->permintaanPerubahan->status === 'disetujui') {
            $p->refresh(); // ✅ ambil ulang data pemesanan + relasi terbaru
        }
        return $p;
    });

    return view('penyewa.tiket', compact('sudahDibayar'));
}



    // ========================== HALAMAN MENUNGGU PEMBAYARAN ==========================
    public function riwayatBelum()
    {
        $userId = Auth::id();
        $belumDibayar = Pemesanan::where('penyewa_id', $userId)
                        ->where('status', 'menunggu')
                        ->get();

        return view('penyewa.pembayaran', compact('belumDibayar'));
    }

    // ========================== HALAMAN RIWAYAT / DIBATALKAN ==========================
    public function riwayatBatal()
    {
        $userId = Auth::id();
        $dibatalkan = Pemesanan::where('penyewa_id', $userId)
                        ->where('status', 'batal')
                        ->orWhere('status', 'di-scan')
                        ->get();

        return view('penyewa.riwayat', compact('dibatalkan'));
    }


public function getSnapToken(Request $request)
{
    try {
        \Log::info('📦 Request masuk ke getSnapToken', $request->all());

        $lapangan = Lapangan::findOrFail($request->lapangan_id);
        $jadwal = JadwalLapangan::findOrFail($request->jadwal_id);

        if (!$jadwal->tersedia) {
            \Log::warning('❌ Jadwal sudah dipesan', ['jadwal_id' => $jadwal->id]);
            return response()->json(['error' => 'Jadwal sudah dipesan!'], 400);
        }

        $pemesanan = Pemesanan::firstOrCreate(
            [
                'penyewa_id' => Auth::id(),
                'lapangan_id' => $lapangan->id,
                'jadwal_id' => $jadwal->id,
                'status' => 'menunggu',
            ]
        );

        // Debug harga
        \Log::info('💰 Harga sewa:', ['harga_sewa' => $jadwal->harga_sewa]);

        // Ambil token Snap
        $hargaSewa = $this->resolveHargaSewa($jadwal, $lapangan);

        if ($hargaSewa <= 0) {
            \Log::warning('Harga sewa tidak tersedia', [
                'lapangan_id' => $lapangan->id,
                'jadwal_id' => $jadwal->id,
            ]);
            return response()->json(['error' => 'Harga lapangan belum diatur.'], 422);
        }

        $snapToken = Snap::getSnapToken([
            'transaction_details' => [
                'order_id' => 'ORDER-' . $pemesanan->id,
                'gross_amount' => $hargaSewa,
            ],
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ],
        ]);

        // Debug token
        \Log::info('✅ Snap token berhasil dibuat', ['token' => $snapToken]);

        Pembayaran::updateOrCreate(
            ['pemesanan_id' => $pemesanan->id],
            [
                'metode' => 'midtrans',
                'jumlah' => $hargaSewa,
                'status' => 'pending',
                'order_id' => 'ORDER-' . $pemesanan->id,
                'snap_token' => $snapToken,
            ]
        );

        return response()->json([
            'snap_token' => $snapToken,
            'pemesanan_id' => $pemesanan->id,
        ]);
    } catch (\Exception $e) {
        \Log::error('🔥 ERROR getSnapToken: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



public function batalkan($id)
{
    $pemesanan = Pemesanan::findOrFail($id);

    if ($pemesanan->penyewa_id != Auth::id()) {
        abort(403, 'Tidak boleh membatalkan pemesanan orang lain.');
    }

    $pemesanan->update(['status' => 'batal']);

    // Jadwal kembali tersedia
    if ($pemesanan->jadwal) {
        $pemesanan->jadwal->update(['tersedia' => true]);
    }

    // Hapus atau update pembayaran (optional)
    if ($pemesanan->pembayaran) {
        $pemesanan->pembayaran->update(['status' => 'batal']);
    }

    return redirect()->back()->with('success', 'Pemesanan berhasil dibatalkan.');
}


public function getSnapTokenAgain(Pemesanan $pemesanan)
{
    try {
        $lapangan = $pemesanan->lapangan;
        $jadwal = $pemesanan->jadwal;
        $hargaSewa = $this->resolveHargaSewa($jadwal, $lapangan);

        if ($hargaSewa <= 0) {
            return response()->json(['error' => 'Harga lapangan belum diatur.'], 422);
        }

        // buat order_id unik tiap generate token
        $uniqueOrderId = 'ORDER-' . $pemesanan->id . '-' . time();

        $snapToken = Snap::getSnapToken([
            'transaction_details' => [
                'order_id' => $uniqueOrderId,
                'gross_amount' => $hargaSewa,
            ],
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ],
        ]);

        // pastikan pembayaran ada
        $pembayaran = $pemesanan->pembayaran;
        if(!$pembayaran){
            $pembayaran = Pembayaran::create([
                'pemesanan_id' => $pemesanan->id,
                'metode' => 'midtrans',
                'jumlah' => $hargaSewa,
                'status' => 'pending',
                'order_id' => $uniqueOrderId,
                'snap_token' => $snapToken,
            ]);
        } else {
            $pembayaran->update([
                'snap_token' => $snapToken,
                'status' => 'pending',
                'order_id' => $uniqueOrderId,
                'jumlah' => $hargaSewa,
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



    // Simpan pemesanan awal (status menunggu)
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

        // 🔹 Simpan pemesanan dengan status menunggu
        $pemesanan = Pemesanan::create([
            'penyewa_id' => Auth::id(),
            'lapangan_id' => $lapangan->id,
            'jadwal_id' => $jadwal->id,
            'status' => 'menunggu',
        ]);

        // 🔹 Simpan pembayaran pending
        $hargaSewa = $this->resolveHargaSewa($pemesanan->jadwal, $lapangan);

        Pembayaran::create([
            'pemesanan_id' => $pemesanan->id,
            'metode' => 'midtrans',
            'jumlah' => $hargaSewa,
            'status' => 'pending',
            'order_id' => 'ORDER-' . time(),
            'snap_token' => $request->snap_token,
        ]);

        return response()->json([
            'snap_token' => $request->snap_token,
            'pemesanan_id' => $pemesanan->id,
        ]);
    }

    // Update status sukses
public function updateSuccess(Request $request, $id)
{
    $pemesanan = Pemesanan::findOrFail($id);

        $hargaSewa = $this->resolveHargaSewa($pemesanan->jadwal, $pemesanan->lapangan);

        $pemesanan->update([
            'status' => 'dibayar',
            'kode_tiket' => $this->generateShortTicketCode(),
        ]);

        if ($pemesanan->pembayaran) {
            $pemesanan->pembayaran->update([
                'status' => 'berhasil',
                'jumlah' => $hargaSewa,
            ]);
        }

    if ($pemesanan->jadwal) {
        $pemesanan->jadwal->update(['tersedia' => false]);
    }

    return response()->json(['success' => true]);
}


        private function generateShortTicketCode()
    {
        $prefix = 'LPN'; // bisa diganti misal "LPN" untuk lapangan
        $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)); 
        return $prefix . $random; // contoh hasil: TK7F3C9A
    }

    private function resolveHargaSewa(?JadwalLapangan $jadwal, ?Lapangan $lapangan): int
    {
        $candidates = [
            optional($jadwal)->harga_sewa,
            optional($lapangan)->harga_sewa,
            optional($lapangan)->harga_per_jam,
        ];

        foreach ($candidates as $value) {
            if (is_numeric($value) && $value > 0) {
                return (int) round($value);
            }
        }

        return 0;
    }

}
