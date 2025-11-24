<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lapangan;
use App\Models\Pemesanan;
use App\Models\JadwalLapangan;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Midtrans\Snap;
use Midtrans\Config;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
class PemesananController extends Controller
{
    public function pindahLangsung(Request $request, $pemesananId)
{
    $request->validate([
        'jadwal_baru_id' => 'required|exists:jadwal_lapangan,id',
        'section_baru_id' => 'nullable|exists:section_lapangan,id',
    ]);

    $pemesanan = Pemesanan::with('jadwal')->findOrFail($pemesananId);

    // hanya pemilik pemesanan (penyewa) yang boleh
    if ($pemesanan->penyewa_id !== Auth::id()) {
        abort(403);
    }

    // tidak boleh pindah jika sudah discan / sudah selesai
    if ($pemesanan->status === 'dibayar' && $pemesanan->status_scan === 'sudah_scan') {
        return response()->json(['error' => 'Sudah discan, tidak bisa dipindah.'], 422);
    }

    $jadwalBaru = JadwalLapangan::findOrFail($request->jadwal_baru_id);

    // Pastikan jadwal baru benar-benar tersedia (tersedia == true)
    if (! $jadwalBaru->tersedia) {
        return response()->json(['error' => 'Jadwal tidak tersedia.'], 422);
    }

    // Ubah jadwal lama jadi tersedia lagi
    if ($pemesanan->jadwal) {
        $pemesanan->jadwal->update(['tersedia' => true]);
    }

    // Lakukan pemindahan
    $pemesanan->update([
        'jadwal_id' => $jadwalBaru->id,
    ]);

    // Lock jadwal baru
    $jadwalBaru->update(['tersedia' => false]);

    return response()->json([
        'success' => true,
        'message' => 'Berhasil memindahkan jadwal.',
        'pemesanan' => $pemesanan->fresh()->load('jadwal.section')
    ]);
}

        public function __construct()
    {
        Carbon::setLocale('id');
        // ✅ Setup Midtrans config global
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

public function boot(): void
{
    Carbon::setLocale('id');
}


public function setujuiPermintaan($id)
{
    $permintaan = \App\Models\PermintaanPerubahan::with([
        'pemesanan',
        'jadwalLama',
        'jadwalBaru',
        'pemesanan.pembayaran'
    ])->findOrFail($id);

    // Validasi pemilik lapangan
    if ($permintaan->pemesanan->lapangan->pemilik_id != Auth::id()) {
        abort(403, 'Tidak punya akses.');
    }

    // Expired otomatis
    if ($permintaan->status === 'menunggu' && $permintaan->expires_at < now()) {
        $permintaan->update(['status' => 'kadaluarsa']);
        return response()->json(['error' => 'Waktu persetujuan sudah habis.'], 410);
    }

    \DB::beginTransaction();
    try {

        // 1. Update status permintaan
        $permintaan->update(['status' => 'disetujui']);

        $pemesananUtama = $permintaan->pemesanan;

        // 2. Buka jadwal lama
        if ($permintaan->jadwalLama) {
            $permintaan->jadwalLama->update(['tersedia' => true]);
        }

        // 3. Kunci jadwal baru
        if ($permintaan->jadwalBaru) {
            $permintaan->jadwalBaru->update(['tersedia' => false]);
        }

        // 4. Cari pemesanan lain yang menunggu di jadwal baru
        $pemesananLain = \App\Models\Pemesanan::where('jadwal_id', $permintaan->jadwal_baru_id)
            ->where('id', '!=', $pemesananUtama->id)
            ->whereIn('status', ['menunggu', 'dibayar'])
            ->get();

        foreach ($pemesananLain as $p) {

            // 🔥 4.1 Set pemesanan mereka menjadi BATAL
            $p->update(['status' => 'batal']);

            // 🔥 4.2 Jadwal mereka dibuka kembali
            if ($p->jadwal) {
                $p->jadwal->update(['tersedia' => true]);
            }

            // 🔥 4.3 Set pembayaran mereka menjadi batal
            \App\Models\Pembayaran::where('pemesanan_id', $p->id)
                ->whereNotIn('status', ['berhasil', 'gagal'])
                ->update(['status' => 'batal']);
        }

        // 5. Update jadwal pemesanan utama
        $pemesananUtama->update([
            'jadwal_id' => $permintaan->jadwal_baru_id,
        ]);

        \DB::commit();

        $pemesananUtama->load('jadwal.section');

        return response()->json([
            'success' => true,
            'message' => 'Permintaan perubahan jadwal disetujui.',
            'pemesanan' => $pemesananUtama
        ]);

    } catch (\Exception $e) {
        \DB::rollBack();

        return response()->json([
            'error' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}

public function getDetailPermintaan($id)
{
    $permintaan = \App\Models\PermintaanPerubahan::with(['sectionBaru', 'jadwalBaru', 'pemesanan.lapangan'])
        ->findOrFail($id);

    if ($permintaan->pemesanan->penyewa_id != Auth::id()) {
        abort(403);
    }

    $countdown = $permintaan->expires_at
        ? now()->diffInMinutes($permintaan->expires_at, false)
        : null;

    return response()->json([
        'id' => $permintaan->id,
        'status' => $permintaan->status,
        'alasan' => $permintaan->alasan,
        'pemesanan_id' => $permintaan->pemesanan_id,
        'lapangan_id' => $permintaan->pemesanan->lapangan_id,
        'section_baru' => $permintaan->sectionBaru,
        'jadwal_baru' => $permintaan->jadwalBaru,
        'countdown' => $countdown,  // ⏳ berapa menit tersisa
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

    // Pastikan penyewa benar
    if ($pemesanan->penyewa_id != Auth::id()) {
        abort(403);
    }

    // Tidak boleh ubah bila sudah scan
    if ($pemesanan->status_scan === 'sudah_scan') {
        return response()->json([
            'error' => 'Tiket sudah discan dan tidak bisa diubah.',
        ], 403);
    }

    // ============================================================
    // 🔥 1. Update semua permintaan yang sudah EXPIRED → jadikan kadaluarsa
    // ============================================================
    \App\Models\PermintaanPerubahan::where('pemesanan_id', $pemesanan->id)
        ->where('status', 'menunggu')
        ->where('expires_at', '<', now())
        ->update(['status' => 'kadaluarsa']);

    // ============================================================
    // 🔥 2. Hapus hanya permintaan LAMA yang masih "menunggu"
    //    (agar hanya ada 1 request pending)
    // ============================================================
    \App\Models\PermintaanPerubahan::where('pemesanan_id', $pemesanan->id)
        ->where('status', 'menunggu')
        ->delete();

    // NOTE:
    // - Status "disetujui", "ditolak", "kadaluarsa" TIDAK DIHAPUS
    //   (tetap jadi riwayat, aman)

    // ============================================================
    // 🔥 3. Buat permintaan baru (UNLIMITED)
    // ============================================================
    \App\Models\PermintaanPerubahan::create([
        'pemesanan_id'       => $pemesanan->id,
        'section_lama_id'    => $pemesanan->jadwal->section_id,
        'section_baru_id'    => $request->section_baru_id,
        'jadwal_lama_id'     => $pemesanan->jadwal_id,
        'jadwal_baru_id'     => $request->jadwal_baru_id,
        'alasan'             => $request->alasan,
        'status'             => 'menunggu',
        'expires_at'         => now()->addMinutes(15),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Permintaan perubahan berhasil diajukan.',
    ]);
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
    $now = Carbon::now('Asia/Jakarta');

    $jadwal = JadwalLapangan::where('section_id', $section_id)
        ->where(function ($q) use ($now) {
            $q->where('tanggal', '>', $now->toDateString())
              ->orWhere(function ($q2) use ($now) {
                  $q2->where('tanggal', '=', $now->toDateString())
                     ->where('jam_selesai', '>', $now->format('H:i:s'));
              });
        })
        ->orderBy('tanggal')
        ->orderBy('jam_mulai')
        ->get()
        ->map(function($j) {

            // cari pemesanan aktif utk jadwal ini
$p = Pemesanan::where('jadwal_id', $j->id)
    ->whereIn('status', ['menunggu', 'dibayar'])
    ->whereHas('pembayaran', function($q) {
        $q->whereNotIn('status', ['kadaluarsa', 'gagal', 'batal']);
    })
    ->first();


            if ($p) {
                $j->booking_status = $p->status; // menunggu / dibayar
            } else {
                $j->booking_status = null; // available
            }

            // lock pending
            if ($j->booking_status === 'menunggu') {
                $j->tersedia = false;
            }

            $j->tanggal = Carbon::parse($j->tanggal)->toDateString();

            return $j;
        });

    return response()->json($jadwal);
}





    // ========================== HALAMAN TIKET ==========================
public function riwayatTiket()
{
    $userId = Auth::id();
    $now = Carbon::now('Asia/Jakarta');
$semuaPemesananUser = Pemesanan::with(['jadwal', 'pembayaran'])
    ->where('penyewa_id', auth()->id())
    ->get();

// kirim ke blade: status pembayaran yang benar
$userOrders = $semuaPemesananUser->mapWithKeys(function ($p) {
    return [
        $p->jadwal_id => optional($p->pembayaran)->status // pending / berhasil / gagal / ...
    ];
});


    // Ambil semua tiket yang sudah dibayar
    $sudahDibayar = Pemesanan::with([
        'lapangan',
        'jadwal.section',
        'permintaanPerubahan.jadwalBaru.section',
        'permintaanPerubahan.sectionBaru',
    ])
    ->where('penyewa_id', $userId)
    ->where('status', 'dibayar')
    ->latest()
    ->get();

    // 🔹 Cek apakah sudah lewat waktu tapi belum di-scan
    foreach ($sudahDibayar as $p) {
        if ($p->jadwal && $p->status_scan === 'belum_scan') {
            $tanggal = Carbon::parse($p->jadwal->tanggal)->format('Y-m-d');
            $jamSelesai = $p->jadwal->jam_selesai;
            $tanggalWaktuMain = Carbon::parse("$tanggal $jamSelesai", 'Asia/Jakarta');

            // Kalau waktu main sudah lewat
            if ($tanggalWaktuMain->lt($now)) {
                $p->update(['status' => 'kadaluarsa']);
                $p->jadwal->update(['tersedia' => true]); // buka jadwal lagi
            }
        }
    }

    // 🔹 Ambil ulang tiket yang masih aktif (belum kadaluarsa)
    $sudahDibayar = Pemesanan::with([
        'lapangan',
        'jadwal.section',
        'permintaanPerubahan.jadwalBaru.section',
        'permintaanPerubahan.sectionBaru',
    ])
    ->where('penyewa_id', $userId)
    ->where('status', 'dibayar')
    ->latest()
    ->get()
    ->map(function ($p) {
        if ($p->permintaanPerubahan && $p->permintaanPerubahan->status === 'disetujui') {
            $p->refresh();
        }
        return $p;
    });
    \App\Models\PermintaanPerubahan::where('status', 'menunggu')
        ->where('expires_at', '<', now())
        ->update(['status' => 'kadaluarsa']);
return view('penyewa.tiket', [
    'sudahDibayar' => $sudahDibayar,
    'semuaPemesananUser' => $semuaPemesananUser,
            'userOrders' => $userOrders

]);
}





    // ========================== HALAMAN MENUNGGU PEMBAYARAN ==========================
public function riwayatBelum()
{
    $userId = Auth::id();
    $now = Carbon::now('Asia/Jakarta');
    $semuaPemesananUser = Pemesanan::with(['jadwal', 'pembayaran'])
    ->where('penyewa_id', auth()->id())
    ->get();

// kirim ke blade: status pembayaran yang benar
$userOrders = $semuaPemesananUser->mapWithKeys(function ($p) {
    return [
        $p->jadwal_id => optional($p->pembayaran)->status // pending / berhasil / gagal / ...
    ];
});

    // Ambil semua pesanan menunggu
    $belumDibayar = Pemesanan::with(['jadwal'])
        ->where('penyewa_id', $userId)
        ->where('status', 'menunggu')
        ->latest()
        ->get();

foreach ($belumDibayar as $p) {
    $batasWaktu = Carbon::parse($p->created_at)->addMinutes(15);

    if ($now->greaterThan($batasWaktu)) {
        $p->update(['status' => 'kadaluarsa']);

        if ($p->jadwal) {
            $p->jadwal->update(['tersedia' => true]);
        }

        if ($p->pembayaran) {
            $p->pembayaran->update(['status' => 'kadaluarsa']);
        }
    }
}


    // Setelah update, ambil ulang hanya yang benar-benar masih menunggu
    $belumDibayar = Pemesanan::with(['jadwal'])
        ->where('penyewa_id', $userId)
        ->where('status', 'menunggu')
        ->latest()
        ->get();

return view('penyewa.pembayaran', [
    'belumDibayar' => $belumDibayar,
    'semuaPemesananUser' => $semuaPemesananUser,
            'userOrders' => $userOrders

]);}




    // ========================== HALAMAN RIWAYAT / DIBATALKAN ==========================
public function riwayatBatal()
{
    $userId = Auth::id();

    $dibatalkan = Pemesanan::with('lapangan', 'jadwal')
        ->where('penyewa_id', $userId)
        ->whereIn('status', ['batal', 'kadaluarsa', 'di-scan'])
        ->latest()
        ->get();
        

    return view('penyewa.riwayat', compact('dibatalkan'));
}


public function getSnapToken(Request $request)
{
    try {
        \Log::info('📦 Request ke getSnapToken', $request->all());

        $lapangan = Lapangan::findOrFail($request->lapangan_id);
        $jadwal = JadwalLapangan::findOrFail($request->jadwal_id);

        if (!$jadwal->tersedia) {
            return response()->json(['error' => 'Jadwal sudah dipesan!'], 400);
        }

        // 🟢 CEK apakah user sudah pernah pesan jadwal ini
        $existing = Pemesanan::where('penyewa_id', Auth::id())
            ->where('jadwal_id', $jadwal->id)
            ->whereIn('status', ['menunggu', 'dibayar'])
            ->first();
            // 🔒 LOCK JADWAL: Cegah orang lain pilih jadwal yang sedang menunggu pembayaran
$pendingFromOtherUser = Pemesanan::where('jadwal_id', $jadwal->id)
    ->where('penyewa_id', '!=', Auth::id()) // orang lain
    ->where('status', 'menunggu') // BELUM dibayar, tapi pending
    ->exists();

if ($pendingFromOtherUser) {
    return response()->json([
        'error' => 'Jadwal ini sedang menunggu pembayaran oleh penyewa lain.',
    ], 409);
}


if ($existing) {
    return response()->json([
        'error' => 'Kamu sudah memesan jadwal ini.',
        'redirect' => route('penyewa.pembayaran')
    ], 409);
}


        // 🟢 Buat pemesanan baru
        $pemesanan = Pemesanan::create([
            'penyewa_id' => Auth::id(),
            'lapangan_id' => $lapangan->id,
            'jadwal_id' => $jadwal->id,
            'status' => 'menunggu',
        ]);

        $hargaSewa = $this->resolveHargaSewa($jadwal, $lapangan);
        $orderId = $this->generateOrderId($pemesanan);

        // 🔹 Dapatkan Snap Token dari Midtrans
        Config::$serverKey = config('midtrans.server_key');
        $snapToken = Snap::getSnapToken([
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $hargaSewa,
            ],
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ],
        ]);

        // 🔹 Simpan pembayaran pending
        Pembayaran::create([
            'pemesanan_id' => $pemesanan->id,
            'metode' => 'midtrans',
            'jumlah' => $hargaSewa,
            'status' => 'pending',
            'order_id' => $orderId,
            'snap_token' => $snapToken,
        ]);

        return response()->json([
            'snap_token' => $snapToken,
            'pemesanan_id' => $pemesanan->id,
        ]);

    } catch (\Exception $e) {
        \Log::error('🔥 ERROR getSnapToken: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



public function batalkan(Request $request, $id)
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

    if ($request->expectsJson()) {
        return response()->json(['success' => true]);
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

        if (!config('midtrans.server_key') || !config('midtrans.client_key')) {
            \Log::error('⚠ MIDTRANS belum dikonfigurasi saat getSnapTokenAgain');
            return response()->json(['error' => 'Konfigurasi pembayaran belum siap.'], 500);
        }

        // buat order_id unik tiap generate token
        $uniqueOrderId = $this->generateOrderId($pemesanan);

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
        $orderId = $this->generateOrderId($pemesanan);

        Pembayaran::create([
            'pemesanan_id' => $pemesanan->id,
            'metode' => 'midtrans',
            'jumlah' => $hargaSewa,
            'status' => 'pending',
            'order_id' => $orderId,
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

    private function generateOrderId(Pemesanan $pemesanan): string
    {
        return sprintf(
            'ORDER-%s-%s-%s',
            $pemesanan->id,
            now()->format('YmdHis'),
            Str::upper(Str::random(4))
        );
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