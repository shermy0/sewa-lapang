<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use App\Models\Kategori;
use App\Models\Pemesanan;
use App\Models\Pembayaran;
use App\Models\JadwalLapangan;
use App\Models\SectionLapangan;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PetugasController extends Controller
{
    public function index()
    {
        $petugas = Auth::user();
        $petugasName = $petugas->name;
        $pemilikId = $petugas->pemilik_id;

        // Ambil semua lapangan milik pemilik petugas.
        // Jika petugas belum di-link ke pemilik (pemilik_id null), fallback ke semua lapangan supaya tidak kosong.
        $lapangan = Lapangan::query()
            ->with('kategori')
            ->when($pemilikId, fn ($q) => $q->where('pemilik_id', $pemilikId))
            ->get();

        // Fallback jika kosong: tampilkan semua lapangan supaya grid tidak blank.
        if ($lapangan->isEmpty()) {
            $lapangan = Lapangan::with('kategori')->get();
        }

        // Fallback: jika tidak ada pemilik_id di user tetapi lapangan ada, pakai pemilik_id pertama untuk filter antrean.
        if (! $pemilikId && $lapangan->isNotEmpty()) {
            $pemilikId = $lapangan->first()->pemilik_id;
        }

        // Ambil kategori berdasarkan lapangan yang tersedia
        $kategori = Kategori::whereIn('id', $lapangan->pluck('id_kategori')->unique())
            ->orderBy('nama_kategori')
            ->get();

        // Map lapangan supaya JS bisa pakai
        $lapanganData = $lapangan->map(function ($l) {
            // Foto array
            if (is_array($l->foto)) {
                $fotoArray = $l->foto;
            } else {
                $fotoArray = $l->foto ? json_decode($l->foto, true) : [];
                if (!is_array($fotoArray)) $fotoArray = [];
            }

            // Ambil semua section_id untuk lapangan ini
            $sectionIds = DB::table('section_lapangan')
                ->where('lapangan_id', $l->id)
                ->pluck('id');

            // Harga rata-rata dari jadwal_lapangan
            $harga = DB::table('jadwal_lapangan')
                ->whereIn('section_id', $sectionIds)
                ->avg('harga_sewa');

            $primaryPhoto = '';
            if (! empty($fotoArray)) {
                $primaryPhoto = $fotoArray[0];
            } elseif (is_string($l->foto)) {
                $primaryPhoto = $l->foto;
            }

            return [
                'id' => $l->id,
                'nama' => $l->nama_lapangan,
                'pemilik_id' => $l->pemilik_id,
                'kategori_id' => $l->id_kategori,
                'kategori' => $l->kategori->nama_kategori ?? '',
                'foto' => $fotoArray, // Pass array for carousel
                'hargaRataRata' => $harga ? (int) $harga : 0,
                'durasi' => 'Per jam',
                'lokasi' => $l->lokasi ?? '',
                'status' => $l->status ?? 'available',
                'deskripsi' => $l->deskripsi ?? '',
                'kategori_nama' => $l->kategori->nama_kategori ?? '',
            ];
        });

        // Data antrean per section
        $sectionQueues = $this->buildSectionQueues($lapangan->pluck('id'));

        return view('petugas.queue', [
            'kategori' => $kategori,
            // Reindex to avoid JSON becoming an object when keys are non-sequential
            'lapangan' => $lapanganData->values(),
            'petugasName' => $petugasName,
            'sectionQueues' => $sectionQueues,
            'pemilikId' => $pemilikId,
        ]);
    }

    /**
     * Build all schedules for today including available and booked slots
     * Grouped by section and ordered by time
     */
    private function buildAllSchedulesToday(Collection $lapanganIds, string $date): Collection
    {
        if ($lapanganIds->isEmpty()) {
            return collect();
        }

        $now = Carbon::now('Asia/Jakarta');

        // Get all jadwal for today
        $schedules = DB::table('jadwal_lapangan as j')
            ->join('section_lapangan as s', 'j.section_id', '=', 's.id')
            ->join('lapangan as l', 's.lapangan_id', '=', 'l.id')
            ->leftJoin(DB::raw("(SELECT * FROM pemesanan ORDER BY updated_at DESC) as p"), function($join) {
                $join->on('p.jadwal_id', '=', 'j.id')
                     ->whereIn('p.status', ['keranjang','menunggu','dibayar','selesai']);
            })            
            ->leftJoin('users as u', 'p.penyewa_id', '=', 'u.id')
            ->whereIn('l.id', $lapanganIds)
            ->whereDate('j.tanggal', $date)
            ->select([
                'j.id as jadwal_id',
                'j.tanggal',
                'j.jam_mulai',
                'j.jam_selesai',
                'j.harga_sewa',
                's.id as section_id',
                's.nama_section',
                'l.nama_lapangan',
                'p.id as pemesanan_id',
                'p.status as pemesanan_status',
                'p.status_scan',
                'p.kode_tiket',
                'p.nama_komunitas',
                'u.name as penyewa_name',
            ])
            ->orderBy('s.nama_section')
            ->orderBy('j.jam_mulai')
            ->get();

        // Group by section
        $grouped = $schedules->groupBy('section_id')->map(function($sectionSchedules) use ($now) {
            $first = $sectionSchedules->first();
            
            $scheduleItems = $sectionSchedules->map(function($item) use ($now) {
                // Determine status
                $status = 'tersedia';
                $penyewa = $item->nama_komunitas ?: $item->penyewa_name;
                
                $displayName = $item->nama_komunitas ?: $item->penyewa_name;

                if ($item->pemesanan_status === 'dibayar') {
                    $status = 'dibayar';
                    $penyewa = $displayName;
                    
                    // Check if playing
                    if (in_array($item->status_scan, ['masuk_lapang', 'sudah_scan'], true)) {
                        $status = 'sedang_main';
                    } elseif ($item->status_scan === 'masuk_arena') {
                        $status = 'masuk_arena';
                    }
                } elseif ($item->pemesanan_status === 'menunggu') {
                    $status = 'menunggu';
                    $penyewa = $displayName;
                }

                return [
                    'jadwal_id' => $item->jadwal_id,
                    'tanggal' => Carbon::parse($item->tanggal)->format('d M Y'),
                    'jam_mulai' => substr($item->jam_mulai, 0, 5),
                    'jam_selesai' => substr($item->jam_selesai, 0, 5),
                    'harga_sewa' => $item->harga_sewa,
                    'status' => $status,
                    'penyewa' => $penyewa,
                    'kode_tiket' => $item->kode_tiket,
                    'nama_section' => $item->nama_section,
                    'nama_lapangan' => $item->nama_lapangan,
                ];
            });

            return [
                'section_id' => $first->section_id,
                'section_name' => $first->nama_section,
                'lapangan_name' => $first->nama_lapangan,
                'schedules' => $scheduleItems,
            ];
        });

        return $grouped->values();
    }

    /**
     * Ambil antrean pemesanan per section untuk lapangan milik pemilik ini.
     */
    private function buildSectionQueues(Collection $lapanganIds): Collection
    {
        if ($lapanganIds->isEmpty()) {
            return collect();
        }

        $today = Carbon::today()->toDateString();
        $nowTime = Carbon::now()->format('H:i:s');

        $rows = DB::table('pemesanan as p')
            ->join('jadwal_lapangan as j', 'p.jadwal_id', '=', 'j.id')
            ->join('section_lapangan as s', 'j.section_id', '=', 's.id')
            ->join('lapangan as l', 's.lapangan_id', '=', 'l.id')
            ->join('users as u', 'p.penyewa_id', '=', 'u.id')
            ->join('kategori as k', 'l.id_kategori', '=', 'k.id')
            ->whereIn('l.id', $lapanganIds)
            ->whereIn('p.status', ['dibayar'])
            ->where(function ($q) use ($today, $nowTime) {
                $q->whereDate('j.tanggal', '>', $today)
                  ->orWhere(function ($q2) use ($today, $nowTime) {
                      $q2->whereDate('j.tanggal', $today)
                         ->where('j.jam_selesai', '>', $nowTime);
                  });
            })
            ->select([
                'p.id as pemesanan_id',
                'p.kode_tiket',
                'p.status',
                'p.status_scan',
                'p.waktu_scan',
                'p.nama_komunitas',
                'u.name as penyewa',
                'j.tanggal',
                'j.jam_mulai',
                'j.jam_selesai',
                'j.harga_sewa',
                's.id as section_id',
                's.nama_section',
                'l.nama_lapangan',
                'l.lokasi',
                'k.nama_kategori',
            ])
            ->orderBy('j.tanggal')
            ->orderBy('j.jam_mulai')
            ->get();

        return $rows
            ->groupBy('section_id')
            ->map(function ($items) {
                $first = $items->first();

                return [
                    'section_id' => $first->section_id,
                    'label' => $first->nama_section ?? 'Section',
                    'lapangan' => $first->nama_lapangan ?? '-',
                    'queue' => $items->map(function ($row) {
                        $statusFromScan = match ($row->status_scan) {
                            'masuk_arena' => 'masuk_arena',
                            'masuk_lapang', 'sudah_scan' => 'sedang_main',
                            default => null,
                        };
                        $status = $statusFromScan ?? $row->status;
                        $statusScanLabel = match ($row->status_scan) {
                            'masuk_arena' => 'Masuk Arena',
                            'masuk_lapang', 'sudah_scan' => 'Masuk Lapang',
                            default => 'Belum Scan',
                        };
                        $displayName = $row->nama_komunitas ?: $row->penyewa;

                        return [
                            'penyewa' => $displayName,
                            'komunitas' => $row->nama_komunitas,
                            'tanggal' => Carbon::parse($row->tanggal)->format('d M Y'),
                            'jam_mulai' => substr($row->jam_mulai, 0, 5),
                            'jam_selesai' => substr($row->jam_selesai, 0, 5),
                            'status' => $status,
                            'status_scan' => $row->status_scan,
                            'status_scan_label' => $statusScanLabel,
                            'kode_tiket' => $row->kode_tiket,
                            'lokasi' => $row->lokasi,
                            'kategori' => $row->nama_kategori,
                            'harga_sewa' => $row->harga_sewa,
                            'nama_lapangan' => $row->nama_lapangan,
                            'nama_section' => $row->nama_section,
                        ];
                    }),
                ];
            });
    }

    public function create()
    {
        $kategori = Kategori::orderBy('nama_kategori')->get();
        return view('petugas.create', compact('kategori'));
    }

    public function store(Request $request)
    {
        return $this->storeCash($request);
    }

    public function storeCash(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.cart_item_id' => 'required|exists:pemesanan,id',
            'items.*.lapangan_id' => 'required|exists:lapangan,id',
            'items.*.jadwal_id' => 'required|exists:jadwal_lapangan,id',
            'items.*.harga' => 'required|numeric',
            'kasir' => 'required|string',
            'komunitas' => 'required|string',
            'nama_penyewa' => 'nullable|string',
            'penyewa_id' => 'nullable|integer',
        ]);
    
        DB::beginTransaction();
    
        try {
            $pemilikId = $user->pemilik_id ?? null;
            $penyewaId = $validated['penyewa_id'] ?? null;
    
            // Buat guest jika penyewa_id tidak ada
            if (!$penyewaId) {
                $guestName = $validated['nama_penyewa'] ?? 'Tamu';
                $guestEmail = 'guest-' . Str::uuid() . '@guest.local';
                $guest = User::create([
                    'name' => $guestName,
                    'email' => $guestEmail,
                    'password' => bcrypt('12345678'),
                    'role' => 'penyewa',
                    'pemilik_id' => $pemilikId,
                    'status' => 'aktif',
                ]);
                $penyewaId = $guest->id;
            }
    
            // Buat 1 kode tiket untuk seluruh transaksi
            $kodeTiket = 'TK' . strtoupper(Str::random(6));
    
            $receiptItems = [];
            $total = 0;
            $ids = [];
    
            foreach ($validated['items'] as $item) {
                $pemesanan = Pemesanan::findOrFail($item['cart_item_id']);
                $jadwal = JadwalLapangan::findOrFail($item['jadwal_id']);
                $lapangan = Lapangan::findOrFail($item['lapangan_id']);
    
                // Update status pemesanan jadi dibayar & tambahkan kode tiket
                $pemesanan->update([
                    'status' => 'dibayar',
                    'nama_komunitas' => $validated['komunitas'],
                    'kode_tiket' => $kodeTiket,
                ]);
    
                // Simpan pembayaran
                Pembayaran::create([
                    'pemesanan_id' => $pemesanan->id,
                    'metode' => 'cash',
                    'jumlah' => $item['harga'],
                    'status' => 'berhasil',
                    'order_id' => 'CASH-' . $pemesanan->id . '-' . Str::random(4),
                    'tanggal_pembayaran' => now(),
                ]);
    
                // Tandai jadwal tidak tersedia
                $jadwal->update(['tersedia' => false]);
    
                // Tambahkan ke receipt
                $receiptItems[] = [
                    'lapangan' => $lapangan->nama_lapangan,
                    'section' => $jadwal->section->nama_section ?? '-',
                    'tanggal' => $jadwal->tanggal->format('d M Y'),
                    'jam' => substr($jadwal->jam_mulai,0,5) . ' - ' . substr($jadwal->jam_selesai,0,5),
                    'harga' => $item['harga']
                ];
    
                $total += $item['harga'];
                $ids[] = $pemesanan->id;
            }
    
            DB::commit();
    
            $customer = User::find($penyewaId);
    
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran cash berhasil, status diperbarui!',
                'pemesanan_ids' => $ids,
                'receipt' => [
                    'kode_tiket' => $kodeTiket, // ini kode tiket random
                    'tanggal' => now('Asia/Jakarta')->format('d M Y H:i'),
                    'kasir' => $validated['kasir'],
                    'penyewa' => $customer->name ?? 'Tamu',
                    'komunitas' => $validated['komunitas'],
                    'items' => $receiptItems,
                    'total' => $total
                ]
            ]);
    
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Pembayaran cash gagal', [
                'user_id' => Auth::id(),
                'request' => $request->all(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pembayaran cash: ' . $e->getMessage(),
            ], 500);
        }
    }
     

    public function storeMidtrans(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.cart_item_id' => 'required|exists:pemesanan,id',
            'items.*.lapangan_id' => 'required|exists:lapangan,id',
            'items.*.jadwal_id' => 'required|exists:jadwal_lapangan,id',
            'items.*.harga' => 'required|numeric',
            'komunitas' => 'required|string',
            'nama_penyewa' => 'nullable|string',
            'penyewa_id' => 'nullable|integer',
        ]);

        DB::beginTransaction();

        try {
            $pemilikId = $user->pemilik_id;
            $penyewaId = $validated['penyewa_id'] ?? null;

            // Jika tidak ada penyewa → buat tamu
            if (!$penyewaId) {
                $guestName = $validated['nama_penyewa'] ?? 'Tamu';
                $guestEmail = 'guest-' . Str::uuid() . '@guest.local';

                $guest = User::create([
                    'name' => $guestName,
                    'email' => $guestEmail,
                    'password' => bcrypt('12345678'),
                    'role' => 'penyewa',
                    'pemilik_id' => $pemilikId,
                    'status' => 'aktif',
                ]);

                $penyewaId = $guest->id;
            }

            // 1 kode tiket untuk semua transaksi
            $kodeTiket = 'TK' . strtoupper(Str::random(6));

            // ORDER ID (wajib disimpan)
            $orderId = 'ORDER-' . time() . '-' . rand(1000, 9999);

            $total = 0;
            $orderItems = [];
            $receiptItems = [];
            $pemesananIds = [];

            foreach ($validated['items'] as $item) {
                $p = Pemesanan::findOrFail($item['cart_item_id']);
                $jadwal = JadwalLapangan::findOrFail($item['jadwal_id']);
                $lapangan = Lapangan::findOrFail($item['lapangan_id']);

                // UPDATE PEMESANAN
                $p->update([
                    'status' => 'berhasil',
                    'nama_komunitas' => $validated['komunitas'],
                    'kode_tiket' => $kodeTiket,
                    'order_id' => $orderId   // FIX UTAMA!!
                ]);

                $pemesananIds[] = $p->id;
                $total += $item['harga'];

                $orderItems[] = [
                    'id' => "ITEM-" . $p->id,
                    'price' => $item['harga'],
                    'quantity' => 1,
                    'name' => "Sewa Lapangan " . $lapangan->nama_lapangan
                ];

                $receiptItems[] = [
                    'lapangan' => $lapangan->nama_lapangan,
                    'section' => $jadwal->section->nama_section ?? '-',
                    'tanggal' => $jadwal->tanggal->format('d M Y'),
                    'jam' => substr($jadwal->jam_mulai,0,5) . ' - ' . substr($jadwal->jam_selesai,0,5),
                    'harga' => $item['harga']
                ];
            }

            // Konfigurasi Midtrans
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = false;
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            // Payload
            $payload = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $total,
                ],
                'item_details' => $orderItems,
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                ],
                'metadata' => [
                    'pemesanan_ids' => $pemesananIds,
                    'kode_tiket' => $kodeTiket,
                    'komunitas' => $validated['komunitas'],
                ]
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($payload);

            DB::commit();

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'snapToken' => $snapToken,
                'order_id' => $orderId,
                'pemesanan_ids' => $pemesananIds,
                'receipt_preview' => [
                    'kode_tiket' => $kodeTiket,
                    'komunitas' => $validated['komunitas'],
                    'items' => $receiptItems,
                    'total' => $total
                ]
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat Midtrans: ' . $e->getMessage(),
            ], 500);
        }
    }



    // =============================
    // MIDTRANS CALLBACK
    // =============================
    public function midtransCallback(Request $request)
    {
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $notif = new \Midtrans\Notification();

        $transactionStatus = $notif->transaction_status;
        $orderId = $notif->order_id;

        // Ambil pemesanan berdasarkan order_id
        $pemesananList = Pemesanan::where('order_id', $orderId)->get();

        if ($pemesananList->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Order ID tidak ditemukan'], 404);
        }

        DB::beginTransaction();
        try {
            foreach ($pemesananList as $pemesanan) {

                // SUKSES = settlement / capture
                if (in_array($transactionStatus, ['settlement', 'capture'])) {

                    $pemesanan->update(['status' => 'dibayar']);

                    // Jadwal otomatis tidak tersedia
                    $jadwal = $pemesanan->jadwal;
                    if ($jadwal) {
                        $jadwal->update(['tersedia' => false]);
                    }

                    // Simpan pembayaran
                    Pembayaran::updateOrCreate(
                        ['pemesanan_id' => $pemesanan->id],
                        [
                            'metode' => 'midtrans',
                            'jumlah' => $pemesanan->total_harga,
                            'status' => 'berhasil',
                            'order_id' => $orderId,
                            'kode_tiket' => $pemesanan->kode_tiket, // FIX: jangan ganti kode tiket!
                            'tanggal_pembayaran' => now(),
                        ]
                    );
                }

                // STATUS LAIN
                elseif (in_array($transactionStatus, ['pending', 'deny', 'expire', 'cancel'])) {
                    $pemesanan->update(['status' => 'menunggu']);
                }
            }

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Midtrans callback gagal', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function generateTicketCode(): string
    {
        return 'TK' . strtoupper(Str::random(6));
    }

    /**
     * Pastikan slot jadwal tersedia.
     * - Jika ada pemesanan dibayar: blok.
     * - Jika ada pemesanan menunggu yang sudah kadaluarsa (>15 menit) atau bayar gagal/batal: tandai kadaluarsa dan buka slot.
     * - Jika ada pemesanan menunggu yang masih aktif: blok.
     */
    private function assertSlotAvailable(JadwalLapangan $jadwal): void
    {
        $existing = Pemesanan::with('pembayaran')
            ->where('jadwal_id', $jadwal->id)
            ->whereIn('status', ['menunggu', 'dibayar'])
            ->first();

        if (! $existing) {
            if (! $jadwal->tersedia) {
                throw new \RuntimeException('Jadwal sudah dibooking.');
            }
            return;
        }

        // Jika sudah dibayar, langsung blok
        if ($existing->status === 'dibayar') {
            throw new \RuntimeException('Jadwal sudah dibooking.');
        }

        // Status menunggu: cek pembayaran
        $payment = $existing->pembayaran;
        $now = Carbon::now('Asia/Jakarta');
        $expired = false;

        if ($payment) {
            if (in_array($payment->status, ['kadaluarsa', 'batal', 'gagal'], true)) {
                $expired = true;
            } elseif ($payment->status === 'pending') {
                $expired = $payment->created_at && $payment->created_at->addMinutes(15)->lt($now);
            }
        } else {
            // Tidak ada pembayaran, pakai created_at pemesanan
            $expired = $existing->created_at && $existing->created_at->addMinutes(15)->lt($now);
        }

        if ($expired) {
            $existing->update(['status' => 'kadaluarsa']);
            if ($payment && $payment->status === 'pending') {
                $payment->update(['status' => 'kadaluarsa']);
            }
            $jadwal->update(['tersedia' => true]);
            return;
        }

        // Masih aktif
        throw new \RuntimeException('Jadwal sudah dibooking.');
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

    public function midtransPayAgain(Pemesanan $pemesanan)
    {
        $petugas = Auth::user();
        $pemilikId = $petugas->pemilik_id;

        if ($pemilikId && optional($pemesanan->lapangan)->pemilik_id !== $pemilikId) {
            abort(403, 'Tidak diizinkan.');
        }

        $lapangan = $pemesanan->lapangan;
        $jadwal = $pemesanan->jadwal;
        $hargaSewa = $this->resolveHargaSewa($jadwal, $lapangan);

        if ($hargaSewa <= 0) {
            return response()->json(['error' => 'Harga lapangan belum diatur.'], 422);
        }

        if (!config('midtrans.server_key') || !config('midtrans.client_key')) {
            return response()->json(['error' => 'Konfigurasi Midtrans belum siap.'], 500);
        }

        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $uniqueOrderId = 'POS-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));

        $snapToken = Snap::getSnapToken([
            'transaction_details' => [
                'order_id' => $uniqueOrderId,
                'gross_amount' => $hargaSewa,
            ],
            'customer_details' => [
                'first_name' => $pemesanan->penyewa->name ?? 'Penyewa',
                'email' => $pemesanan->penyewa->email ?? 'no-reply@example.com',
            ],
        ]);

        $pembayaran = $pemesanan->pembayaran;
        if ($pembayaran) {
            $pembayaran->update([
                'snap_token' => $snapToken,
                'status' => 'pending',
                'order_id' => $uniqueOrderId,
                'jumlah' => $hargaSewa,
            ]);
        } else {
            Pembayaran::create([
                'pemesanan_id' => $pemesanan->id,
                'metode' => 'midtrans',
                'jumlah' => $hargaSewa,
                'status' => 'pending',
                'order_id' => $uniqueOrderId,
                'snap_token' => $snapToken,
            ]);
        }

        $pemesanan->update(['status' => 'menunggu']);

        return response()->json([
            'snap_token' => $snapToken,
            'pemesanan_id' => $pemesanan->id,
            'order_id' => $uniqueOrderId,
        ]);
    }

    public function midtransSuccess(Request $request, Pemesanan $pemesanan)
    {
        $petugas = Auth::user();
        $pemilikId = $petugas->pemilik_id;

        if ($pemilikId && optional($pemesanan->lapangan)->pemilik_id !== $pemilikId) {
            abort(403, 'Tidak diizinkan.');
        }

        $hargaSewa = $this->resolveHargaSewa($pemesanan->jadwal, $pemesanan->lapangan);

        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $pemesanan->update([
            'status' => 'dibayar',
            'kode_tiket' => $pemesanan->kode_tiket ?: $this->generateTicketCode(),
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

    public function getJadwalLapangan(Request $request, $lapanganId)
    {
        $tanggal   = $request->query('tanggal');      // ?tanggal=YYYY-MM-DD
        $jamMulai  = $request->query('jam_mulai');   // ?jam_mulai=HH:MM
        $sectionName = $request->query('section_name'); // ?section_name=VIP

        // Ambil section sesuai nama & lapangan
        $sectionIds = DB::table('section_lapangan')
            ->where('lapangan_id', $lapanganId)
            ->when($sectionName, fn($q) => $q->where('nama_section', $sectionName))
            ->pluck('id');

        if ($sectionIds->isEmpty()) {
            // Jika section tidak ditemukan, return kosong
            return response()->json([]);
        }

        $now = Carbon::now('Asia/Jakarta');

        // Hapus jadwal yang lewat
        DB::table('jadwal_lapangan')
            ->whereIn('section_id', $sectionIds)
            ->where('tanggal', '<', $now->toDateString())
            ->delete();

        $jadwalQuery = DB::table('jadwal_lapangan as j')
            ->whereIn('j.section_id', $sectionIds)
            ->leftJoin('pemesanan as p', function ($join) {
                $join->on('p.jadwal_id', '=', 'j.id')
                    ->whereIn('p.status', ['keranjang','menunggu','dibayar','selesai']);
            })
            ->leftJoin('pembayaran as pay', 'pay.pemesanan_id', '=', 'p.id');

        // Filter tanggal jika ada
        if ($tanggal) {
            $jadwalQuery->whereDate('j.tanggal', $tanggal);
        } else {
            $jadwalQuery->where('j.tanggal', '>=', $now->toDateString());
        }

        // Filter jam mulai jika ada
        if ($jamMulai) {
            $jadwalQuery->where('j.jam_mulai', '>=', $jamMulai);
        }

        $jadwal = $jadwalQuery
            ->select('j.*', 'p.status as pemesanan_status')
            ->addSelect([
                'p.id as pemesanan_id',
                'p.created_at as pemesanan_created_at',
                'pay.status as payment_status',
                'pay.created_at as payment_created_at',
            ])
            ->orderBy('j.tanggal')
            ->orderBy('j.jam_mulai')
            ->get();

        // Format data sesuai JS
        $jadwalFormatted = $jadwal->map(function($j) use ($now){
            $status = 'tersedia';
            $statusScan = $j->status_scan ?? 'belum_scan';

            if ($j->pemesanan_status === 'dibayar') {
                $status = 'dibayar';
            } elseif ($j->pemesanan_status === 'menunggu') {
                $paymentStatus = $j->payment_status;
                $paymentCreated = $j->payment_created_at ? Carbon::parse($j->payment_created_at) : null;
                $orderCreated = $j->pemesanan_created_at ? Carbon::parse($j->pemesanan_created_at) : null;

                $isExpired =
                    in_array($paymentStatus, ['kadaluarsa', 'batal', 'gagal'], true) ||
                    ($paymentStatus === 'pending' && $paymentCreated && $paymentCreated->addMinutes(15)->lt($now)) ||
                    (!$paymentStatus && $orderCreated && $orderCreated->addMinutes(15)->lt($now));

                $status = $isExpired ? 'tersedia' : 'menunggu';
            } else {
                $status = $j->tersedia ? 'tersedia' : 'tidak_tersedia';
            }

            // Mapping status_scan ke status tampil
            if ($statusScan === 'masuk_arena') {
                $status = 'masuk_arena';
            } elseif (in_array($statusScan, ['masuk_lapang', 'sudah_scan'], true)) {
                $status = 'sedang_main';
            }

            return [
                'id' => $j->id,
                'jam_mulai' => $j->jam_mulai,
                'jam_selesai' => $j->jam_selesai,
                'tanggal' => $j->tanggal,
                'harga_sewa' => $j->harga_sewa ?? 0,
                'section_id' => $j->section_id,
                'booking_status' => $status,
                'status_scan' => $statusScan,
                'status_scan_label' => match ($statusScan) {
                    'masuk_arena' => 'Masuk Arena',
                    'masuk_lapang', 'sudah_scan' => 'Masuk Lapang',
                    default => 'Belum Scan',
                },
            ];
        });

        return response()->json($jadwalFormatted);
    }

    public function getLapanganList()
    {
        $petugas = Auth::user();
        $pemilikId = $petugas->pemilik_id;

        $lapangan = Lapangan::query()
            ->when($pemilikId, fn ($q) => $q->where('pemilik_id', $pemilikId))
            ->select('id', 'nama_lapangan')
            ->orderBy('nama_lapangan')
            ->get();

        if ($lapangan->isEmpty()) {
            $lapangan = Lapangan::select('id', 'nama_lapangan')
                ->orderBy('nama_lapangan')
                ->get();
        }

        return response()->json($lapangan);
    }

    public function getLapanganWithSections()
    {
        $petugas = Auth::user();
        $pemilikId = $petugas->pemilik_id;

        $lapangan = Lapangan::query()
            ->with(['sections:id,lapangan_id,nama_section'])
            ->when($pemilikId, fn ($q) => $q->where('pemilik_id', $pemilikId))
            ->select('id', 'nama_lapangan')
            ->orderBy('nama_lapangan')
            ->get();

        if ($lapangan->isEmpty()) {
            $lapangan = Lapangan::with(['sections:id,lapangan_id,nama_section'])
                ->select('id', 'nama_lapangan')
                ->orderBy('nama_lapangan')
                ->get();
        }

        return response()->json($lapangan);
    }

    public function display(Request $request)
    {
        $petugas = Auth::user();
        $pemilikId = $petugas->pemilik_id;
        $sectionId = $request->integer('section_id');

        $lapangan = Lapangan::query()
            ->with('kategori')
            // Jika lapangan_id diberikan, pakai itu (bisa lintas pemilik).
            ->when($request->lapangan_id, fn ($q) => $q->where('id', $request->lapangan_id))
            // Jika tidak ada lapangan_id, default ke lapangan milik pemilik petugas (jika ada),
            // kalau pemilik_id null maka ambil semua lapangan.
            ->when(!$request->lapangan_id && $pemilikId, fn ($q) => $q->where('pemilik_id', $pemilikId))
            ->get();

        if ($lapangan->isEmpty()) {
            $lapangan = Lapangan::with('kategori')->get();
        }

        if (! $pemilikId && $lapangan->isNotEmpty()) {
            $pemilikId = $lapangan->first()->pemilik_id;
        }

        $sectionQueues = $this->buildSectionQueues($lapangan->pluck('id'));

        // Get all schedules for today (both booked and available)
        $today = Carbon::today()->toDateString();
        $allSchedulesToday = $this->buildAllSchedulesToday($lapangan->pluck('id'), $today);

        // Filter per section jika diberikan
        if ($sectionId) {
            $sectionQueues = $sectionQueues->where('section_id', $sectionId)->values();
            $allSchedulesToday = collect($allSchedulesToday)->where('section_id', $sectionId)->values();
        }

        // Ambil nama lapangan pertama untuk judul display (fallback jika kosong)
        $displayTitle = $lapangan->first()->nama_lapangan ?? 'Layar Display';

        $carouselImages = collect();
        foreach ($lapangan as $l) {
            if (is_array($l->foto)) {
                foreach ($l->foto as $f) $carouselImages->push($f);
            } elseif (is_string($l->foto) && !empty($l->foto)) {
                $carouselImages->push($l->foto);
            }
        }

        return view('petugas.display', [
            'sectionQueues' => $sectionQueues,
            'allSchedulesToday' => $allSchedulesToday,
            'carouselImages' => $carouselImages,
            'petugasName' => $petugas->name,
            'displayTitle' => $displayTitle,
        ]);
    }

    /**
     * List tiket yang sudah dibuat oleh petugas (dibatasi ke lapangan milik pemilik terkait).
     */
    public function tiket()
    {
        $petugas = Auth::user();
        $pemilikId = $petugas->pemilik_id;

        $tiket = Pemesanan::with([
                'penyewa',
                'lapangan',
                'jadwal.section',
                'pembayaran',
            ])
            ->when($pemilikId, function ($q) use ($pemilikId) {
                $q->whereHas('lapangan', function ($l) use ($pemilikId) {
                    $l->where('pemilik_id', $pemilikId);
                });
            })
            ->latest()
            ->limit(200)
            ->get();

        return view('petugas.tiket', [
            'tiket' => $tiket,
            'petugasName' => $petugas->name,
        ]);
    }

    /**
     * Riwayat transaksi per order (berisi item pemesanan di dalamnya).
     */
    public function history()
    {
        $petugas = Auth::user();
        $pemilikId = $petugas->pemilik_id;

        $payments = Pembayaran::with([
                'pemesanan.penyewa',
                'pemesanan.lapangan',
                'pemesanan.jadwal.section',
            ])
            ->when($pemilikId, function ($q) use ($pemilikId) {
                $q->whereHas('pemesanan.lapangan', function ($l) use ($pemilikId) {
                    $l->where('pemilik_id', $pemilikId);
                });
            })
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        $orders = $payments->groupBy(function ($pay) {
            $orderId = $pay->order_id ?? 'TANPA-ORDER';
            // Untuk Midtrans, order_id disimpan sebagai {trx}-{pemesananId}, satukan per transaksi
            return preg_replace('/-\d+$/', '', $orderId);
        });

        return view('petugas.history', [
            'orders' => $orders,
            'petugasName' => $petugas->name,
        ]);
    }

    public function penyewa()
    {
        $petugas = auth()->user();

        // Ambil penyewa milik pemilik dari petugas
        $penyewa = User::where('role', 'penyewa')
                       ->where('pemilik_id', $petugas->pemilik_id)
                       ->orderBy('created_at', 'desc')
                       ->get();

        return view('petugas.penyewa', compact('penyewa'));
    }

    public function storePenyewa(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
        ]);

        $petugas = auth()->user(); // petugas yang login

        User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => bcrypt('12345678'),
            'role'        => 'penyewa',
            'pemilik_id'  => $petugas->pemilik_id,   // <-- INI KUNCI!
            'status'      => 'aktif',
        ]);

        return back()->with('success', 'Penyewa berhasil ditambahkan');
    }

    public function destroyPenyewa($id)
    {
        $user = User::findOrFail($id);

        // Pastikan hanya role 'penyewa' yang bisa dihapus
        if($user->role !== 'penyewa') {
            return redirect()->route('petugas.penyewa')->with('error', 'Hanya penyewa yang bisa dihapus.');
        }

        $user->delete();

        return redirect()->route('petugas.penyewa')->with('success', 'Penyewa berhasil dihapus.');
    }

    public function searchPenyewa(Request $request)
    {
        $keyword = trim($request->q ?? '');
        $petugas = auth()->user();

        $data = User::where('role', 'penyewa')
            ->where(function ($q) use ($petugas) {
                $q->where('pemilik_id', $petugas->pemilik_id)
                  ->orWhereNull('pemilik_id');
            })
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%")
                      ->orWhere('email', 'LIKE', "%{$keyword}%")
                      ->orWhere('no_hp', 'LIKE', "%{$keyword}%");
                })
                ->orderBy('name');
            }, function ($query) {
                $query->orderByDesc('created_at');
            })
            ->limit(10)
            ->get(['id', 'name', 'email', 'no_hp']);

        return response()->json($data);
    }

    public function checkPaymentStatus(Request $request)
    {
        $orderIds = $request->order_ids;
        if(!is_array($orderIds)) return response()->json(['success'=>false]);

        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $updated = 0;
        $receipts = [];

        foreach ($orderIds as $orderId) {
            try {
                $status = Transaction::status($orderId);
                $transactionStatus = $status->transaction_status;
                $fraudStatus = $status->fraud_status;

                $paid = false;
                if ($transactionStatus == 'capture') {
                    if ($fraudStatus == 'accept') {
                        $paid = true;
                    }
                } else if ($transactionStatus == 'settlement') {
                    $paid = true;
                }

                if($paid){
                    $pembayarans = Pembayaran::where('order_id', $orderId)
                        ->orWhere('order_id', 'like', $orderId.'-%')
                        ->with(['pemesanan.penyewa', 'pemesanan.jadwal.section.lapangan', 'pemesanan.lapangan'])
                        ->get();

                    foreach ($pembayarans as $pembayaran) {
                        if($pembayaran->status !== 'berhasil'){
                            $pembayaran->update([
                                'status' => 'berhasil',
                                'tanggal_pembayaran' => now('Asia/Jakarta'),
                            ]);
                        }
                        $pemesanan = $pembayaran->pemesanan;
                        if($pemesanan && $pemesanan->status !== 'dibayar'){
                            $pemesanan->update(['status' => 'dibayar']);
                            $updated++;
                        }
                    }

                    // Siapkan data struk untuk order ini
                    $items = [];
                    $totalAmount = 0;
                    $penyewaName = null;
                    $komunitas = null;

                    foreach ($pembayarans as $pembayaran) {
                        $pemesanan = $pembayaran->pemesanan;
                        $jadwal = $pemesanan?->jadwal;
                        if(!$pemesanan || !$jadwal){
                            continue;
                        }

                        $section = $jadwal->section;
                        $lapanganName = optional($pemesanan->lapangan)->nama_lapangan
                            ?? optional($section?->lapangan)->nama_lapangan
                            ?? '-';
                        $sectionName = optional($section)->nama_section ?? '-';
                        $jamMulai = $jadwal->jam_mulai ? substr($jadwal->jam_mulai, 0, 5) : '-';
                        $jamSelesai = $jadwal->jam_selesai ? substr($jadwal->jam_selesai, 0, 5) : '-';

                        $items[] = [
                            'lapangan' => $lapanganName,
                            'section' => $sectionName,
                            'tanggal' => $jadwal->tanggal ? Carbon::parse($jadwal->tanggal)->format('d M Y') : '-',
                            'jam' => "{$jamMulai} - {$jamSelesai}",
                            'harga' => $pembayaran->jumlah ?? 0,
                        ];

                        $totalAmount += $pembayaran->jumlah ?? 0;
                        $penyewaName = $penyewaName ?? optional($pemesanan->penyewa)->name;
                        $komunitas = $komunitas ?? $pemesanan->nama_komunitas;
                    }

                    if(!empty($items)){
                        $receipts[] = [
                            'order_id' => $orderId,
                            'tanggal' => now('Asia/Jakarta')->format('d M Y H:i'),
                            'kasir' => Auth::user()->name ?? 'Kasir',
                            'penyewa' => $penyewaName ?? 'Penyewa',
                            'komunitas' => $komunitas,
                            'items' => $items,
                            'total' => $totalAmount,
                        ];
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'receipts' => $receipts,
            'receipt' => $receipts[0] ?? null,
        ]);
    }

    public function getSections($lapanganId)
    {
        $sections = DB::table('section_lapangan') // ganti table
            ->where('lapangan_id', $lapanganId)
            ->select('id', 'nama_section')
            ->get();

        return response()->json($sections);
    }
}