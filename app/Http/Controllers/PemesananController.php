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
use Illuminate\Support\Facades\DB;

class PemesananController extends Controller
{
    public function __construct()
    {
        Carbon::setLocale('id');
        // Setup Midtrans config global
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    // ==================== HELPERS ====================
    private function expirePendingOrders(): void
    {
        $now = Carbon::now('Asia/Jakarta');

        $expiredOrders = Pemesanan::with(['jadwal', 'pembayaran'])
            ->where('status', 'menunggu')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->get();

        foreach ($expiredOrders as $order) {
            DB::transaction(function () use ($order) {
                $order->update(['status' => 'kadaluarsa']);

                if ($order->jadwal) {
                    $order->jadwal->update(['tersedia' => true]);
                }

                if ($order->pembayaran) {
                    $order->pembayaran->update(['status' => 'kadaluarsa']);
                }
            });
        }
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

    private function generateShortTicketCode()
    {
        $prefix = 'TK';
        $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        return $prefix . $random;
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

    // Release jadwal jika pemesanan sudah kadaluarsa atau pembayaran gagal
    private function releaseJadwalIfNeeded(Pemesanan $pemesanan)
    {
        if ($pemesanan->jadwal) {
            // Jika pemesanan expired atau status bukan menunggu/dibayar -> buka jadwal
            if (!in_array($pemesanan->status, ['menunggu', 'dibayar'])) {
                $pemesanan->jadwal->update(['tersedia' => true]);
            }
        }
    }

    // ==================== API: GET JADWAL ====================
    public function getJadwalBySection($section_id)
    {
        $this->expirePendingOrders();
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

                // cari pemesanan aktif utk jadwal ini (menunggu atau dibayar)
                $p = Pemesanan::where('jadwal_id', $j->id)
                    ->whereIn('status', ['menunggu', 'dibayar'])
                    ->whereHas('pembayaran', function($q) {
                        $q->whereNotIn('status', ['kadaluarsa', 'gagal', 'batal']);
                    })
                    ->first();

                if ($p) {
                    $j->booking_status = $p->status; // menunggu / dibayar
                    $j->tersedia = false;            // lock langsung
                } else {
                    $j->booking_status = null;       // available
                    $j->tersedia = true;
                }

                $j->tanggal = Carbon::parse($j->tanggal)->toDateString();

                return $j;
            });

        return response()->json($jadwal);
    }

    // ==================== CREATE / STORE PEMESANAN ====================
    public function store(Request $request)
    {
        $request->validate([
            'lapangan_id' => 'required|exists:lapangan,id',
            'jadwal_id' => 'required|exists:jadwal_lapangan,id',
            'snap_token' => 'required',
            'nama_komunitas' => 'nullable|string|max:255',
        ]);

        $jadwal = JadwalLapangan::findOrFail($request->jadwal_id);
        $lapangan = Lapangan::findOrFail($request->lapangan_id);

        // Pastikan jadwal benar-benar bisa dipesan
        if (! $jadwal->tersedia) {
            return back()->with('error', 'Jadwal sudah dipesan!');
        }

        // Cek apakah ada pemesanan oleh orang lain (menunggu/dibayar)
        $occupied = Pemesanan::where('jadwal_id', $jadwal->id)
            ->whereIn('status', ['menunggu', 'dibayar'])
            ->whereHas('pembayaran', function($q) {
                $q->whereNotIn('status', ['kadaluarsa', 'gagal', 'batal']);
            })
            ->exists();

        if ($occupied) {
            return back()->with('error', 'Jadwal sedang dipesan oleh pengguna lain.');
        }

        DB::beginTransaction();
        try {
            // Simpan pemesanan dengan status menunggu dan langsung lock jadwal
            $pemesanan = Pemesanan::create([
                'penyewa_id' => Auth::id(),
                'lapangan_id' => $lapangan->id,
                'jadwal_id' => $jadwal->id,
                'status' => 'menunggu',
                'expires_at' => now()->addMinutes(15),
                'nama_komunitas.required' => 'Nama komunitas wajib diisi.',
            ]);

            // hitung harga
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

            // LOCK jadwal
            $jadwal->update(['tersedia' => false]);

            DB::commit();

            return response()->json([
                'snap_token' => $request->snap_token,
                'pemesanan_id' => $pemesanan->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('ERROR store pemesanan: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal membuat pemesanan'], 500);
        }
    }

    public function getSnapTokenAgain(Pemesanan $pemesanan)
{
    $request->validate([
    'nama_komunitas' => 'nullable|string|max:255',
]);

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

    public function getSectionsByLapangan($lapangan_id)
{
    $lapangan = \App\Models\Lapangan::with('sections')->findOrFail($lapangan_id);
    return response()->json($lapangan->sections);
}

    // ==================== MIDTRANS: GET SNAP TOKEN (START PAYMENT) ====================
    public function getSnapToken(Request $request)
    {
        $this->expirePendingOrders();
        try {
            \Log::info('📦 Request ke getSnapToken', $request->all());

            $lapangan = Lapangan::findOrFail($request->lapangan_id);
            $jadwalIds = [];
            if ($request->filled('jadwal_id')) {
                $jadwalIds[] = $request->jadwal_id;
            }
            if (is_array($request->jadwal_ids ?? null)) {
                $jadwalIds = array_merge($jadwalIds, $request->jadwal_ids);
            }
            $jadwalIds = array_values(array_filter($jadwalIds));
            if (empty($jadwalIds)) {
                return response()->json(['error' => 'Jadwal tidak ditemukan.'], 404);
            }

            $jadwals = JadwalLapangan::whereIn('id', $jadwalIds)->get();
            if ($jadwals->count() !== count($jadwalIds)) {
                return response()->json(['error' => 'Ada jadwal yang tidak ditemukan.'], 404);
            }

            // Validasi setiap jadwal
            foreach ($jadwals as $jadwal) {
                // Cek apakah jadwal sedang dipakai oleh orang lain (pending/dibayar)
                $pendingFromOtherUser = Pemesanan::where('jadwal_id', $jadwal->id)
                    ->where('penyewa_id', '!=', Auth::id())
                    ->whereIn('status', ['menunggu', 'dibayar'])
                    ->whereHas('pembayaran', function ($q) {
                        $q->whereNotIn('status', ['kadaluarsa', 'gagal', 'batal']);
                    })
                    ->exists();

                if ($pendingFromOtherUser) {
                    return response()->json([
                        'error' => 'Ada jadwal yang sedang menunggu pembayaran oleh penyewa lain.',
                    ], 409);
                }

                if (! $jadwal->tersedia) {
                    return response()->json(['error' => 'Ada jadwal yang tidak tersedia.'], 400);
                }

                // Cegah user melakukan double booking pada jadwal yang sama
                $existing = Pemesanan::where('penyewa_id', Auth::id())
                    ->where('jadwal_id', $jadwal->id)
                    ->whereIn('status', ['menunggu', 'dibayar'])
                    ->first();

                if ($existing) {
                    return response()->json([
                        'error' => 'Kamu sudah memesan salah satu jadwal ini.',
                        'redirect' => route('penyewa.pembayaran')
                    ], 409);
                }
            }

            $totalBayar = 0;
            foreach ($jadwals as $jadwal) {
                $hargaSewa = $this->resolveHargaSewa($jadwal, $lapangan);
                if ($hargaSewa <= 0) {
                    return response()->json(['error' => 'Harga lapangan belum diatur.'], 422);
                }
                $totalBayar += $hargaSewa;
            }

            if (!config('midtrans.server_key') || !config('midtrans.client_key')) {
                \Log::error('⚠ MIDTRANS belum dikonfigurasi saat getSnapToken');
                return response()->json(['error' => 'Konfigurasi pembayaran belum siap.'], 500);
            }

            $transactionId = sprintf('TMP-%s-%s-%s', Auth::id(), now()->timestamp, Str::upper(Str::random(4)));

            $snapToken = Snap::getSnapToken([
                'transaction_details' => [
                    'order_id' => $transactionId,
                    'gross_amount' => $totalBayar,
                ],
                'customer_details' => [
                    'first_name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                ],
            ]);

            // Simpan pemesanan & pembayaran sementara
            DB::beginTransaction();
            try {
                $pemesananIds = [];

                foreach ($jadwals as $jadwal) {
                    $pemesanan = Pemesanan::create([
                        'penyewa_id' => Auth::id(),
                        'lapangan_id' => $lapangan->id,
                        'jadwal_id' => $jadwal->id,
                        'status' => 'menunggu',
                        'expires_at' => now()->addMinutes(15),
                        'nama_komunitas.required' => 'Nama komunitas wajib diisi.',
                    ]);

                    Pembayaran::create([
                        'pemesanan_id' => $pemesanan->id,
                        'metode' => 'midtrans',
                        'jumlah' => $this->resolveHargaSewa($jadwal, $lapangan),
                        'status' => 'pending',
                        // order_id unik per pembayaran untuk hindari constraint,
                        // tetap merujuk ke transactionId Midtrans.
                        'order_id' => $transactionId . '-' . $pemesanan->id,
                        'snap_token' => $snapToken,
                    ]);

                    $jadwal->update(['tersedia' => false]);
                    $pemesananIds[] = $pemesanan->id;
                }

                DB::commit();

                return response()->json([
                    'snap_token' => $snapToken,
                    'pemesanan_ids' => $pemesananIds,
                    'pemesanan_id' => $pemesananIds[0] ?? null, // fallback untuk client lama
                    'transaction_id' => $transactionId,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                // unlock jadwal jika gagal menyimpan
                foreach ($jadwals as $jadwal) {
                    $jadwal->update(['tersedia' => true]);
                }
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('🔥 ERROR getSnapToken: ' . $e->getMessage());
            // Pastikan jadwal tidak terkunci jika token gagal
            if (isset($jadwal) && $jadwal->exists) {
                $jadwal->update(['tersedia' => true]);
            }
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ==================== COMPLETE PAYMENT (MIDTRANS CALLBACK / MANUAL) ====================
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

        // jadwal sudah pasti terkunci
        if ($pemesanan->jadwal) {
            $pemesanan->jadwal->update(['tersedia' => false]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Webhook/callback dari Midtrans: tandai semua pembayaran dengan order_id ini sebagai berhasil,
     * dan update semua pemesanan terkait ke status dibayar.
     */
    public function midtransCallback(Request $request)
    {
        $orderId = $request->input('order_id');
        $transactionStatus = $request->input('transaction_status');
        $fraudStatus = $request->input('fraud_status');

        if (! $orderId) {
            return response()->json(['message' => 'order_id kosong'], 400);
        }

        $isPaid = false;
        if ($transactionStatus === 'capture') {
            $isPaid = ($fraudStatus === 'accept');
        } elseif ($transactionStatus === 'settlement') {
            $isPaid = true;
        }

        if (! $isPaid) {
            return response()->json([
                'message' => 'Status belum dibayar',
                'transaction_status' => $transactionStatus,
            ], 200);
        }

        $pembayarans = Pembayaran::where('order_id', $orderId)
            ->orWhere('order_id', 'like', $orderId . '-%')
            ->get();

        if ($pembayarans->isEmpty()) {
            return response()->json(['message' => 'Pembayaran tidak ditemukan'], 404);
        }

        foreach ($pembayarans as $pembayaran) {
            if ($pembayaran->status !== 'berhasil') {
                $pembayaran->update(['status' => 'berhasil']);
            }

            $pemesanan = $pembayaran->pemesanan;
            if ($pemesanan && $pemesanan->status !== 'dibayar') {
                $pemesanan->update([
                    'status' => 'dibayar',
                    'kode_tiket' => $pemesanan->kode_tiket ?: $this->generateShortTicketCode(),
                ]);

                if ($pemesanan->jadwal) {
                    $pemesanan->jadwal->update(['tersedia' => false]);
                }
            }
        }

        return response()->json(['success' => true]);
    }

    // ==================== BATALKAN PEMESANAN ====================
    public function batalkan(Request $request, $id)
    {
        $pemesanan = Pemesanan::findOrFail($id);

        if ($pemesanan->penyewa_id != Auth::id()) {
            abort(403, 'Tidak boleh membatalkan pemesanan orang lain.');
        }

        $pemesanan->update(['status' => 'batal']);

        // buka jadwal kembali
        if ($pemesanan->jadwal) {
            $pemesanan->jadwal->update(['tersedia' => true]);
        }

        if ($pemesanan->pembayaran) {
            $pemesanan->pembayaran->update(['status' => 'batal']);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Pemesanan berhasil dibatalkan.');
    }

    // ==================== PINDAH LANGSUNG ====================
public function pindahLangsung(Request $request, $pemesananId)
{
    $request->validate([
        'jadwal_baru_id' => 'required|exists:jadwal_lapangan,id',
        'section_baru_id' => 'nullable|exists:section_lapangan,id',
        'alasan' => 'nullable|string|max:500',
    ]);

    $pemesanan = Pemesanan::with('jadwal.section')->findOrFail($pemesananId);

    if ($pemesanan->penyewa_id !== Auth::id()) {
        abort(403, 'Tidak boleh memindahkan pemesanan orang lain.');
    }

    if ($pemesanan->status === 'dibayar' && $pemesanan->status_scan === 'sudah_scan') {
        return response()->json(['error' => 'Sudah discan, tidak bisa dipindah.'], 422);
    }

    $jadwalBaru = JadwalLapangan::findOrFail($request->jadwal_baru_id);

    // cek apakah jadwal baru sedang dipakai orang lain
    $dipakaiOrangLain = Pemesanan::where('jadwal_id', $jadwalBaru->id)
        ->where('penyewa_id', '!=', Auth::id())
        ->whereIn('status', ['menunggu', 'dibayar'])
        ->exists();

    if ($dipakaiOrangLain || ! $jadwalBaru->tersedia) {
        return response()->json(['error' => 'Jadwal tidak tersedia.'], 422);
    }

    DB::beginTransaction();
    try {
        // buka jadwal lama
        if ($pemesanan->jadwal) {
            $pemesanan->jadwal->update(['tersedia' => true]);
        }

        // simpan riwayat perpindahan ke tabel permintaan_perubahan
        $perubahan = \App\Models\PermintaanPerubahan::create([
            'pemesanan_id' => $pemesanan->id,
            'section_lama_id' => $pemesanan->jadwal->section_id ?? null,
            'section_baru_id' => $request->section_baru_id,
            'jadwal_lama_id' => $pemesanan->jadwal_id,
            'jadwal_baru_id' => $request->jadwal_baru_id,
            'alasan' => $request->alasan,
            'status' => 'disetujui', // bisa diubah menjadi 'menunggu' jika perlu approval
            'expires_at' => now()->addMinutes(15),
        ]);

        // update pemesanan utama
        $pemesanan->update([
            'jadwal_id' => $jadwalBaru->id,
            'alasan' => $request->alasan,
        ]);

        // kunci jadwal baru
        $jadwalBaru->update(['tersedia' => false]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil memindahkan jadwal dan menyimpan riwayat perubahan.',
            'pemesanan' => $pemesanan->fresh()->load('jadwal.section'),
            'perubahan' => $perubahan,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'Gagal memindahkan jadwal.'], 500);
    }
}

    // ==================== DOWNLOAD TIKET ====================
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

        return $pdf->download('Tiket_' . $pemesanan->kode_tiket . '.pdf');
    }

    // ==================== HALAMAN & RIWAYAT ====================
    public function create($lapangan_id)
    {
        $this->expirePendingOrders();
        $lapangan = Lapangan::with('sections')->findOrFail($lapangan_id);
        $userId = Auth::id();

        $pemesananPending = Pemesanan::where('penyewa_id', $userId)
            ->where('lapangan_id', $lapangan_id)
            ->where('status', 'menunggu')
            ->with(['pembayaran', 'jadwal'])
            ->first();

        return view('pemesanan.create', compact('lapangan', 'pemesananPending'));
    }

    public function riwayatTiket()
    {
        $userId = Auth::id();
        $now = Carbon::now('Asia/Jakarta');

        $semuaPemesananUser = Pemesanan::with(['jadwal', 'pembayaran'])
            ->where('penyewa_id', auth()->id())
            ->get();

        $userOrders = $semuaPemesananUser->mapWithKeys(function ($p) {
            return [
                $p->jadwal_id => optional($p->pembayaran)->status
            ];
        });

        // Tandai pemesanan yang waktunya lewat -> jadwal dibuka
        $sudahDibayar = Pemesanan::with([
            'lapangan',
            'jadwal.section',
        ])
        ->where('penyewa_id', $userId)
        ->where('status', 'dibayar')
        ->latest()
        ->get();

        foreach ($sudahDibayar as $p) {
            if ($p->jadwal && $p->status_scan === 'belum_scan') {
                $tanggal = Carbon::parse($p->jadwal->tanggal)->format('Y-m-d');
                $jamSelesai = $p->jadwal->jam_selesai;
                $tanggalWaktuMain = Carbon::parse("$tanggal $jamSelesai", 'Asia/Jakarta');

                if ($tanggalWaktuMain->lt($now)) {
                    $p->update(['status' => 'kadaluarsa']);
                    $p->jadwal->update(['tersedia' => true]);
                }
            }
        }

        $sudahDibayar = Pemesanan::with([
            'lapangan',
            'jadwal.section',
        ])
        ->where('penyewa_id', $userId)
        ->where('status', 'dibayar')
        ->latest()
        ->get();

        // Hapus/ubah status permintaan perubahan handled di frontend (fitur dihapus)

        return view('penyewa.tiket', [
            'sudahDibayar' => $sudahDibayar,
            'semuaPemesananUser' => $semuaPemesananUser,
            'userOrders' => $userOrders
        ]);
    }

    public function riwayatBelum()
    {
        $this->expirePendingOrders();
        $userId = Auth::id();
        $semuaPemesananUser = Pemesanan::with(['jadwal', 'pembayaran'])
            ->where('penyewa_id', $userId)
            ->get();

        $userOrders = $semuaPemesananUser->mapWithKeys(function ($p) {
            return [
                $p->jadwal_id => optional($p->pembayaran)->status
            ];
        });

        $belumDibayar = Pemesanan::with(['jadwal', 'pembayaran'])
            ->where('penyewa_id', $userId)
            ->where('status', 'menunggu')
            ->latest()
            ->get()
            ->each
            ->checkExpired();

        $belumDibayar = Pemesanan::with(['jadwal', 'pembayaran'])
            ->where('penyewa_id', $userId)
            ->where('status', 'menunggu')
            ->latest()
            ->get();

        // Sinkronisasi status pending dengan Midtrans jika ada pembayaran pending
        $this->syncPendingPayments($belumDibayar);

        return view('penyewa.pembayaran', [
            'belumDibayar' => $belumDibayar,
            'semuaPemesananUser' => $semuaPemesananUser,
            'userOrders' => $userOrders
        ]);
    }

    /**
     * Cek status pembayaran pending ke Midtrans dan perbarui pemesanan + pembayaran.
     */
    private function syncPendingPayments($pemesananCollection): void
    {
        if (!config('midtrans.server_key')) {
            return; // konfigurasi belum siap
        }

        $pendingPayments = [];
        foreach ($pemesananCollection as $p) {
            if ($p->pembayaran && $p->pembayaran->status === 'pending') {
                $pendingPayments[] = $p->pembayaran;
            }
        }

        if (empty($pendingPayments)) {
            return;
        }

        $baseOrderIds = collect($pendingPayments)
            ->map(function ($pay) {
                return preg_replace('/-\d+$/', '', $pay->order_id);
            })
            ->unique()
            ->values();

        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        foreach ($baseOrderIds as $baseOrderId) {
            try {
                $status = \Midtrans\Transaction::status($baseOrderId);
                $transactionStatus = $status->transaction_status ?? null;
                $fraudStatus = $status->fraud_status ?? null;

                $paid = false;
                if ($transactionStatus === 'capture') {
                    $paid = ($fraudStatus === 'accept');
                } elseif ($transactionStatus === 'settlement') {
                    $paid = true;
                }

                if ($paid) {
                    $pembayarans = Pembayaran::where('order_id', $baseOrderId)
                        ->orWhere('order_id', 'like', $baseOrderId . '-%')
                        ->get();

                    foreach ($pembayarans as $pay) {
                        if ($pay->status !== 'berhasil') {
                            $pay->update(['status' => 'berhasil']);
                        }

                        $pemesanan = $pay->pemesanan;
                        if ($pemesanan && $pemesanan->status !== 'dibayar') {
                            $pemesanan->update([
                                'status' => 'dibayar',
                                'kode_tiket' => $pemesanan->kode_tiket ?: $this->generateShortTicketCode(),
                            ]);

                            if ($pemesanan->jadwal) {
                                $pemesanan->jadwal->update(['tersedia' => false]);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Midtrans status check failed for '.$baseOrderId.' : '.$e->getMessage());
                continue;
            }
        }
    }

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
    /**
     * Tandai pemesanan sebagai kadaluarsa jika sudah melewati expires_at (dipanggil via AJAX countdown).
     */
    public function expireNow(Pemesanan $pemesanan)
    {
        if ($pemesanan->penyewa_id !== Auth::id()) {
            abort(403, 'Tidak diizinkan.');
        }

        if ($pemesanan->status !== 'menunggu') {
            return response()->json(['status' => $pemesanan->status]);
        }

        $now = Carbon::now('Asia/Jakarta');
        $expiresAt = $pemesanan->expires_at ? Carbon::parse($pemesanan->expires_at, 'Asia/Jakarta') : null;

        if (! $expiresAt || $expiresAt->gt($now)) {
            return response()->json([
                'status' => 'menunggu',
                'expires_at' => $expiresAt?->timezone('Asia/Jakarta')->toIso8601String(),
                'server_time' => $now->toIso8601String(),
            ]);
        }

        DB::transaction(function () use ($pemesanan) {
            $pemesanan->update(['status' => 'kadaluarsa']);

            if ($pemesanan->jadwal) {
                $pemesanan->jadwal->update(['tersedia' => true]);
            }

            if ($pemesanan->pembayaran) {
                $pemesanan->pembayaran->update(['status' => 'kadaluarsa']);
            }
        });

        return response()->json([
            'status' => 'kadaluarsa',
            'expires_at' => $expiresAt?->timezone('Asia/Jakarta')->toIso8601String(),
            'server_time' => $now->toIso8601String(),
        ]);
    }
}
