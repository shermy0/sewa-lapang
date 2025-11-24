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
use Symfony\Component\HttpFoundation\Response;
class PemesananController extends Controller
{

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
    $permintaan = \App\Models\PermintaanPerubahan::with(['pemesanan', 'jadwalLama', 'jadwalBaru'])->findOrFail($id);

    // Cegah yang bukan pemilik lapangan
    if ($permintaan->pemesanan->lapangan->pemilik_id != Auth::id()) {
        abort(403, 'Tidak punya akses.');
    }

    $this->autoExpirePermintaan($permintaan);
    if ($permintaan->status !== 'menunggu') {
        return response()->json([
            'success' => false,
            'message' => 'Permintaan sudah tidak aktif.',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
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

    private function autoExpirePermintaan(?\App\Models\PermintaanPerubahan $permintaan): void
    {
        if (! $permintaan || $permintaan->status !== 'menunggu') {
            return;
        }

        if ($permintaan->expires_at && now()->greaterThan($permintaan->expires_at)) {
            $permintaan->update([
                'status' => 'ditolak',
                'alasan_internal' => 'Permintaan kadaluarsa otomatis.',
            ]);
        }
    }

    public function getDetailPermintaan($id)
{
    $permintaan = \App\Models\PermintaanPerubahan::with(['sectionBaru', 'jadwalBaru', 'pemesanan.lapangan'])
        ->findOrFail($id);

    // Cegah akses data orang lain
    if ($permintaan->pemesanan->penyewa_id != Auth::id()) {
        abort(403);
    }

    $this->autoExpirePermintaan($permintaan);

    return response()->json([
        'id' => $permintaan->id,
        'status' => $permintaan->status,
        'alasan' => $permintaan->alasan,
        'alasan_internal' => $permintaan->alasan_internal,
        'expires_at' => optional($permintaan->expires_at)->toIso8601String(),
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

    // Tidak boleh ajukan setelah check-in
    if ($pemesanan->status_scan === 'sudah_scan') {
        return response()->json([
            'error' => 'Tidak bisa mengajukan perubahan setelah check-in.',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    $this->autoExpirePermintaan($pemesanan->permintaanPerubahan);
    if ($pemesanan->permintaanPerubahan && $pemesanan->permintaanPerubahan->status === 'menunggu') {
        return response()->json([
            'error' => 'Masih ada permintaan perubahan yang menunggu. Selesaikan dahulu.',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    $jadwalBaru = JadwalLapangan::with('pemesanan')->findOrFail($request->jadwal_baru_id);
    $jadwalLama = $pemesanan->jadwal;

    if ($jadwalLama && $jadwalLama->id === $jadwalBaru->id) {
        return response()->json([
            'error' => 'Kamu sudah berada di jadwal ini.',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    $now = Carbon::now('Asia/Jakarta');
    $mulaiLama = $jadwalLama ? Carbon::parse($jadwalLama->tanggal . ' ' . $jadwalLama->jam_mulai, 'Asia/Jakarta') : null;
    $mulaiBaru = $jadwalBaru ? Carbon::parse($jadwalBaru->tanggal . ' ' . $jadwalBaru->jam_mulai, 'Asia/Jakarta') : null;

    // Hanya izinkan ajukan sebelum jadwal lama dimulai
    if ($mulaiLama && $now->greaterThanOrEqualTo($mulaiLama)) {
        return response()->json([
            'error' => 'Jadwal sudah dimulai, tidak bisa diajukan.',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    // Pastikan jadwal baru masih di masa depan
    if ($mulaiBaru && $mulaiBaru->lessThanOrEqualTo($now)) {
        return response()->json([
            'error' => 'Tidak bisa pindah ke jadwal yang sudah lewat.',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    // Izinkan ajukan di slot unavailable hanya jika bookingnya status menunggu (belum bayar)
    $bookingStatus = optional($jadwalBaru->pemesanan)->status;
    $slotPending = $bookingStatus === 'menunggu';
    $slotPaid = $bookingStatus === 'dibayar';

    if (! $jadwalBaru->tersedia && ! $slotPending) {
        return response()->json([
            'error' => 'Jadwal ini sudah tidak tersedia.',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    if ($slotPaid) {
        return response()->json([
            'error' => 'Jadwal ini sudah dibayar dan tidak bisa diajukan.',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    $permintaan = \App\Models\PermintaanPerubahan::create([
        'pemesanan_id' => $pemesanan->id,
        'section_lama_id' => $pemesanan->jadwal->section_id,
        'section_baru_id' => $request->section_baru_id,
        'jadwal_lama_id' => $pemesanan->jadwal_id,
        'jadwal_baru_id' => $request->jadwal_baru_id,
        'alasan' => $request->alasan,
        'status' => 'menunggu',
        'expires_at' => now()->addMinutes(60),
        'alasan_internal' => $slotPending
            ? 'Slot target sedang menunggu pembayaran; permintaan takeover.'
            : null,
    ]);

    return response()->json(['success' => true, 'expires_at' => optional($permintaan->expires_at)->toIso8601String()]);
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
    $now = Carbon::now('Asia/Jakarta'); // waktu sekarang
    $jadwal = JadwalLapangan::with(['pemesanan' => function ($q) {
            $q->whereIn('status', ['menunggu', 'dibayar'])->with('pembayaran');
        }])
        ->where('section_id', $section_id)
        ->where(function ($q) use ($now) {
            $q->where('tanggal', '>', $now->toDateString()) // tanggal di masa depan
              ->orWhere(function ($q2) use ($now) {
                  // kalau tanggal sama, cek jam_selesai belum lewat
                  $q2->where('tanggal', '=', $now->toDateString())
                     ->where('jam_selesai', '>', $now->format('H:i:s'));
              });
        })
        ->orderBy('tanggal')
        ->orderBy('jam_mulai')
        ->get();

    $jadwal = $jadwal->map(function ($item) {
        $booking = $item->pemesanan;
        $bookingStatus = optional($booking)->status;

        // Lepas blokir jika pesanan menunggu sudah melewati batas waktu (20 menit)
        if ($booking && $bookingStatus === 'menunggu') {
            $expiresAt = Carbon::parse($booking->created_at)->addMinutes(20);
            if (now('Asia/Jakarta')->greaterThan($expiresAt)) {
                // Tandai kadaluarsa dan buka slot
                $booking->update(['status' => 'kadaluarsa']);
                if ($booking->pembayaran) {
                    $booking->pembayaran->update(['status' => 'kadaluarsa']);
                }
                $item->tersedia = true;
                $bookingStatus = null;
            }
        }

        return [
            'id' => $item->id,
            'section_id' => $item->section_id,
            'tanggal' => $item->tanggal,
            'jam_mulai' => $item->jam_mulai,
            'jam_selesai' => $item->jam_selesai,
            'tersedia' => (bool) $item->tersedia,
            'harga_sewa' => $item->harga_sewa,
            'durasi_sewa' => $item->durasi_sewa,
            'booking_status' => $bookingStatus,
        ];
    });

    return response()->json($jadwal);
}


    // ========================== HALAMAN TIKET ==========================
public function riwayatTiket()
{
    $userId = Auth::id();
    $now = Carbon::now('Asia/Jakarta');

    // Ambil semua tiket yang sudah dibayar
    $sudahDibayar = Pemesanan::with([
        'lapangan',
        'jadwal.section',
        'permintaanPerubahan.jadwalBaru.section',
        'permintaanPerubahan.sectionBaru',
    ])
    ->where('penyewa_id', $userId)
    ->where('status', 'dibayar')
    ->get();

    // 🔹 Cek apakah sudah lewat waktu tapi belum di-scan
    foreach ($sudahDibayar as $p) {
        $this->autoExpirePermintaan($p->permintaanPerubahan);

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
    ->get()
    ->map(function ($p) {
        $this->autoExpirePermintaan($p->permintaanPerubahan);

        if ($p->permintaanPerubahan && $p->permintaanPerubahan->status === 'disetujui') {
            $p->refresh();
        }
        return $p;
    });

    return view('penyewa.tiket', compact('sudahDibayar'));
}



    // ========================== HALAMAN MENUNGGU PEMBAYARAN ==========================
public function riwayatBelum()
{
    $userId = Auth::id();
    $now = Carbon::now('Asia/Jakarta');

    // Ambil semua pesanan menunggu
    $belumDibayar = Pemesanan::with(['jadwal', 'permintaanPerubahan'])
        ->where('penyewa_id', $userId)
        ->where('status', 'menunggu')
        ->get();

    foreach ($belumDibayar as $p) {
        $this->autoExpirePermintaan($p->permintaanPerubahan);

        // Cek apakah sudah melewati batas waktu pembayaran (20 menit)
        $batasWaktu = Carbon::parse($p->created_at)->addMinutes(20);

        if ($now->greaterThan($batasWaktu)) {
            // Ubah status jadi kadaluarsa dan buka jadwalnya
            $p->update(['status' => 'kadaluarsa']);
            if ($p->jadwal) {
                $p->jadwal->update(['tersedia' => true]);
            }

            // Kalau ada pembayaran pending, ubah juga statusnya
            if ($p->pembayaran) {
                $p->pembayaran->update(['status' => 'kadaluarsa']);
            }
        }
    }

    // Setelah update, ambil ulang hanya yang benar-benar masih menunggu
    $belumDibayar = Pemesanan::with(['jadwal', 'permintaanPerubahan'])
        ->where('penyewa_id', $userId)
        ->where('status', 'menunggu')
        ->get();

    return view('penyewa.pembayaran', compact('belumDibayar'));
}




    // ========================== HALAMAN RIWAYAT / DIBATALKAN ==========================
public function riwayatBatal()
{
    $userId = Auth::id();

    $dibatalkan = Pemesanan::with('lapangan', 'jadwal')
        ->where('penyewa_id', $userId)
        ->whereIn('status', ['batal', 'kadaluarsa', 'di-scan'])
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