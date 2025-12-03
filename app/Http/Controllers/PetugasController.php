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
            ->leftJoin('pemesanan as p', function($join) {
                $join->on('p.jadwal_id', '=', 'j.id')
                     ->whereIn('p.status', ['menunggu', 'dibayar']);
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
        $validated = $request->validate([
            'penyewa_id' => 'nullable|integer',
            'nama_penyewa' => 'nullable|string',
            'komunitas' => 'required|string',
            'total' => 'required|numeric',
            'items' => 'required|array|min:1',
            'kasir' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $penyewaId = $validated['penyewa_id'] ?? null;
            if (! $penyewaId) {
                $guestName = $validated['nama_penyewa'] ?? 'Tamu';
                $guestEmail = 'guest-' . Str::uuid() . '@guest.local';
                $guest = User::create([
                    'name' => $guestName,
                    'email' => $guestEmail,
                    'password' => bcrypt('12345678'),
                    'role' => 'penyewa',
                    'pemilik_id' => Auth::user()->pemilik_id,
                    'status' => 'aktif',
                ]);
                $penyewaId = $guest->id;
            }

            $orderId = 'CASH-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
            $createdIds = [];
            $receiptItems = [];
            $computedTotal = 0;

            foreach ($validated['items'] as $item) {
                $jadwalId = $item['jadwal_id'] ?? null;
                if (! $jadwalId && isset($item['cart_item_id'])) {
                    $cartRow = DB::table('cart_temp')->where('id', $item['cart_item_id'])->first();
                    $jadwalId = $cartRow->jadwal_id ?? null;
                }

                if (! $jadwalId) {
                    throw new \InvalidArgumentException('Jadwal tidak ditemukan.');
                }

                $jadwal = JadwalLapangan::findOrFail($jadwalId);
                $this->assertSlotAvailable($jadwal);

                $lapanganId = $item['id'] ?? optional($jadwal->section)->lapangan_id;
                if (! $lapanganId) {
                    throw new \RuntimeException('Lapangan tidak ditemukan.');
                }

                $pemesanan = Pemesanan::create([
                    'penyewa_id' => $penyewaId,
                    'lapangan_id' => $lapanganId,
                    'jadwal_id' => $jadwal->id,
                    'status' => 'dibayar',
                    'kode_tiket' => $this->generateTicketCode(),
                    'status_scan' => 'belum_scan',
                    'nama_komunitas' => $validated['komunitas'] ?? null,
                ]);

                $jumlah = ($item['harga'] ?? 0) * ($item['durasi'] ?? 1);
                Pembayaran::create([
                    'pemesanan_id' => $pemesanan->id,
                    'metode' => 'cash',
                    'jumlah' => $jumlah,
                    'status' => 'berhasil',
                    'order_id' => $orderId,
                    'tanggal_pembayaran' => now(),
                ]);

                $jadwal->update(['tersedia' => false]);
                $createdIds[] = $pemesanan->id;

                $lapanganName = optional($pemesanan->lapangan)->nama_lapangan
                    ?? optional($jadwal->section)->lapangan->nama_lapangan
                    ?? '-';
                $sectionName = optional($jadwal->section)->nama_section ?? '-';
                $jamMulai = substr($jadwal->jam_mulai, 0, 5);
                $jamSelesai = substr($jadwal->jam_selesai, 0, 5);

                $receiptItems[] = [
                    'lapangan' => $lapanganName,
                    'section' => $sectionName,
                    'tanggal' => Carbon::parse($jadwal->tanggal)->format('d M Y'),
                    'jam' => "{$jamMulai} - {$jamSelesai}",
                    'harga' => $jumlah,
                ];
                $computedTotal += $jumlah;
            }

            DB::commit();

            $customer = User::find($penyewaId);
            $receiptData = [
                'order_id' => $orderId,
                'tanggal' => now('Asia/Jakarta')->format('d M Y H:i'),
                'kasir' => $validated['kasir'],
                'penyewa' => $customer->name ?? ($validated['nama_penyewa'] ?? 'Tamu'),
                'komunitas' => $validated['komunitas'] ?? null,
                'items' => $receiptItems,
                'total' => $computedTotal,
            ];

            return response()->json([
                'success' => true,
                'pemesanan_ids' => $createdIds,
                'message' => 'Pemesanan cash berhasil!',
                'receipt' => $receiptData,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pembayaran cash: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function storeMidtrans(Request $request)
    {
        $validated = $request->validate([
            'penyewa_id' => 'nullable|integer',
            'nama_penyewa' => 'nullable|string',
            'komunitas' => 'required|string',
            'total' => 'required|numeric',
            'items' => 'required|array|min:1',
            // 'kasir' => 'required|string', // Optional
        ]);

        DB::beginTransaction();

        try {
            $penyewaId = $validated['penyewa_id'] ?? null;
            if (! $penyewaId) {
                $guestName = $validated['nama_penyewa'] ?? 'Tamu';
                $guestEmail = 'guest-' . Str::uuid() . '@guest.local';
                $guest = User::create([
                    'name' => $guestName,
                    'email' => $guestEmail,
                    'password' => bcrypt('12345678'),
                    'role' => 'penyewa',
                    'pemilik_id' => Auth::user()->pemilik_id,
                    'status' => 'aktif',
                ]);
                $penyewaId = $guest->id;
            }

            $grossAmount = 0;
            $transactionId = 'TRX-' . time() . '-' . strtoupper(Str::random(4));

            $slotPlans = [];
            $orderIds = [];

            foreach ($validated['items'] as $item) {
                $jadwalId = $item['jadwal_id'] ?? null;
                if (! $jadwalId && isset($item['cart_item_id'])) {
                    $cartRow = DB::table('cart_temp')->where('id', $item['cart_item_id'])->first();
                    $jadwalId = $cartRow->jadwal_id ?? null;
                }

                if (! $jadwalId) {
                    throw new \InvalidArgumentException('Jadwal tidak ditemukan.');
                }

                $jadwal = JadwalLapangan::findOrFail($jadwalId);
                $lapanganId = $item['id'] ?? optional($jadwal->section)->lapangan_id;
                if (! $lapanganId) {
                    throw new \RuntimeException('Lapangan tidak ditemukan.');
                }

                $amount = ($item['harga'] ?? 0) * ($item['durasi'] ?? 1);

                // Cek existing pemesanan untuk jadwal ini
                $existing = Pemesanan::with('pembayaran')
                    ->where('jadwal_id', $jadwal->id)
                    ->whereIn('status', ['menunggu', 'dibayar'])
                    ->first();

                $expired = false;
                if ($existing) {
                    if ($existing->status === 'dibayar') {
                        throw new \RuntimeException('Jadwal sudah dibooking.');
                    }

                    $payment = $existing->pembayaran;
                    $now = Carbon::now('Asia/Jakarta');

                    if ($payment) {
                        if (in_array($payment->status, ['kadaluarsa', 'batal', 'gagal'], true)) {
                            $expired = true;
                        } elseif ($payment->status === 'pending') {
                            $expired = $payment->created_at && $payment->created_at->addMinutes(15)->lt($now);
                        }
                    } else {
                        $expired = $existing->created_at && $existing->created_at->addMinutes(15)->lt($now);
                    }

                    if (! $expired && $existing->penyewa_id != $penyewaId) {
                        throw new \RuntimeException('Jadwal sudah dibooking.');
                    }

                    if ($expired) {
                        $existing->update(['status' => 'kadaluarsa']);
                        if ($payment && $payment->status === 'pending') {
                            $payment->update(['status' => 'kadaluarsa']);
                        }
                        $jadwal->update(['tersedia' => true]);
                        $existing = null; // treat as new
                    }
                }

                $grossAmount += $amount;
                $slotPlans[] = [
                    'jadwal' => $jadwal,
                    'lapangan_id' => $lapanganId,
                    'amount' => $amount,
                    'reuse' => (bool) $existing,
                    'pemesanan' => $existing,
                    'payment' => $existing?->pembayaran,
                ];
            }

            $orderIds[] = $transactionId;

            // Config Midtrans
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = true;
            Config::$is3ds = true;

            $customer = User::find($penyewaId);

            $params = [
                'transaction_details' => [
                    'order_id' => $transactionId,
                    'gross_amount' => $grossAmount,
                ],
                'customer_details' => [
                    'first_name' => $customer->name ?? 'Penyewa',
                    'email' => $customer->email ?? 'no-reply@example.com',
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            foreach ($slotPlans as $plan) {
                $jadwal = $plan['jadwal'];

                if ($plan['reuse']) {
                    $pemesanan = $plan['pemesanan'];
                    $pemesanan->update([
                        'status' => 'menunggu',
                        'nama_komunitas' => $validated['komunitas'] ?? $pemesanan->nama_komunitas,
                    ]);

                    $payment = $plan['payment'];
                    if ($payment) {
                        $payment->update([
                            'status' => 'pending',
                            'jumlah' => $plan['amount'],
                            'order_id' => $transactionId . '-' . $pemesanan->id,
                        ]);
                    } else {
                        Pembayaran::create([
                            'pemesanan_id' => $pemesanan->id,
                            'metode' => 'midtrans',
                            'jumlah' => $plan['amount'],
                            'status' => 'pending',
                            'order_id' => $transactionId . '-' . $pemesanan->id,
                            'payment_url' => null,
                        ]);
                    }
                } else {
                    $pemesanan = Pemesanan::create([
                        'penyewa_id' => $penyewaId,
                        'lapangan_id' => $plan['lapangan_id'],
                        'jadwal_id' => $jadwal->id,
                        'status' => 'menunggu',
                        'kode_tiket' => $this->generateTicketCode(),
                        'status_scan' => 'belum_scan',
                        'nama_komunitas' => $validated['komunitas'] ?? null,
                    ]);

                    Pembayaran::create([
                        'pemesanan_id' => $pemesanan->id,
                        'metode' => 'midtrans',
                        'jumlah' => $plan['amount'],
                        'status' => 'pending',
                        'order_id' => $transactionId . '-' . $pemesanan->id,
                        'payment_url' => null,
                    ]);
                }

                $jadwal->update(['tersedia' => false]);
            }
            $orderIds = [$transactionId];

            DB::commit();

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'redirect_url' => route('petugas.index'),
                'orders' => $orderIds,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran midtrans: ' . $e->getMessage(),
            ], 500);
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
                    ->whereIn('p.status', ['menunggu', 'dibayar']);
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
                        ->get();

                    foreach ($pembayarans as $pembayaran) {
                        if($pembayaran->status !== 'berhasil'){
                            $pembayaran->update(['status' => 'berhasil']);
                        }
                        $pemesanan = $pembayaran->pemesanan;
                        if($pemesanan && $pemesanan->status !== 'dibayar'){
                            $pemesanan->update(['status' => 'dibayar']);
                            $updated++;
                        }
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return response()->json(['success' => true, 'updated' => $updated]);
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