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
                    'label' => $first->nama_section ?? 'Section',
                    'lapangan' => $first->nama_lapangan ?? '-',
                    'queue' => $items->map(function ($row) {
                        $scanMasukLapang = in_array($row->status_scan, ['sudah_scan', 'masuk_lapang'], true);
                        return [
                            'penyewa' => $row->penyewa,
                            'tanggal' => Carbon::parse($row->tanggal)->format('d M Y'),
                            'jam_mulai' => substr($row->jam_mulai, 0, 5),
                            'jam_selesai' => substr($row->jam_selesai, 0, 5),
                            'status' => $scanMasukLapang ? 'sedang_main' : $row->status,
                            'status_scan' => $row->status_scan,
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

            $createdIds = [];

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

                if (! $jadwal->tersedia) {
                    throw new \RuntimeException('Jadwal sudah dibooking.');
                }

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
                ]);

                Pembayaran::create([
                    'pemesanan_id' => $pemesanan->id,
                    'metode' => 'cash',
                    'jumlah' => ($item['harga'] ?? 0) * ($item['durasi'] ?? 1),
                    'status' => 'berhasil',
                    'order_id' => 'CASH-' . strtoupper(Str::random(8)),
                    'tanggal_pembayaran' => now(),
                ]);

                $jadwal->update(['tersedia' => false]);
                $createdIds[] = $pemesanan->id;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'pemesanan_ids' => $createdIds,
                'message' => 'Pemesanan cash berhasil!',
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
            'penyewa_id' => 'required|integer',
            'total' => 'required|numeric',
            'items' => 'required|array|min:1',
            // 'kasir' => 'required|string', // Optional
        ]);

    DB::beginTransaction();

        try {
            $orderIds = [];
            $grossAmount = 0;

            foreach ($validated['items'] as $item) {
                if (empty($item['jadwal_id'])) {
                    throw new \InvalidArgumentException('Jadwal tidak ditemukan.');
                }

                $jadwal = JadwalLapangan::findOrFail($item['jadwal_id']);

                if (! $jadwal->tersedia) {
                    throw new \RuntimeException('Jadwal sudah dibooking.');
                }

                $lapanganId = $item['id'] ?? optional($jadwal->section)->lapangan_id;
                if (! $lapanganId) {
                    throw new \RuntimeException('Lapangan tidak ditemukan.');
                }

                $pemesanan = Pemesanan::create([
                    'penyewa_id' => $validated['penyewa_id'],
                    'lapangan_id' => $lapanganId,
                    'jadwal_id' => $jadwal->id,
                    'status' => 'dibayar',
                    'kode_tiket' => $this->generateTicketCode(),
                    'status_scan' => 'belum_scan',
                ]);

                $orderId = 'MID-' . strtoupper(Str::random(10));
                $amount = ($item['harga'] ?? 0) * ($item['durasi'] ?? 1);
                $grossAmount += $amount;

                Pembayaran::create([
                    'pemesanan_id' => $pemesanan->id,
                    'metode' => 'midtrans',
                    'jumlah' => $amount,
                    'status' => 'berhasil',
                    'order_id' => $orderId,
                    'payment_url' => null,
                ]);

                $jadwal->update(['tersedia' => false]);
                $orderIds[] = $orderId;
            }

            // Config Midtrans
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = true;
            Config::$is3ds = true;

            // Create Snap Token (using the first order ID or a group ID?)
            // For simplicity, let's use a group ID or just the first one.
            // Midtrans expects unique order_id. If we have multiple items, we might need a parent transaction or treat them individually.
            // But here we are returning a single snap token for the whole cart?
            // Midtrans Snap is usually 1 transaction.
            // If we want to pay for multiple items at once, we should group them under one 'order_id' sent to Midtrans.
            // But our DB structure has 1 Pembayaran per Pemesanan.
            // To fix this properly: Create a "Transaction" record that groups multiple "Pemesanan".
            // For now, let's assume we create ONE Midtrans transaction for the TOTAL amount, and link it to the first Pemesanan (or all of them if we had a pivot).
            // Hack: Use the first orderId for Midtrans, but we need to track status for all.
            
            // BETTER APPROACH for this specific codebase state:
            // Just generate one Snap Token for the total amount.
            $transactionId = 'TRX-' . time();
            
            $params = [
                'transaction_details' => [
                    'order_id' => $transactionId,
                    'gross_amount' => $grossAmount,
                ],
                'customer_details' => [
                    'first_name' => User::find($validated['penyewa_id'])->name,
                    'email' => User::find($validated['penyewa_id'])->email,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

        DB::commit();

        // 5. Langsung cek status Midtrans untuk update jadi dibayar jika sudah bayar
        try {
            $status = \Midtrans\Transaction::status($orderId);
            $transactionStatus = $status->transaction_status;
            $fraudStatus = $status->fraud_status;

            $paid = false;
            if ($transactionStatus == 'capture' && $fraudStatus == 'accept') {
                $paid = true;
            } elseif ($transactionStatus == 'settlement') {
                $paid = true;
            }

            if ($paid) {
                // Update pembayaran & pemesanan
                $pembayaran->update(['status' => 'berhasil']);
                foreach ($pemesananIds as $pid) {
                    $pemesanan = Pemesanan::find($pid);
                    if ($pemesanan) {
                        $pemesanan->update(['status' => 'dibayar']);
                    }
                }
            }
        } catch (\Exception $e) {
            // jangan gagal jika cek status Midtrans error
            \Log::warning("Cek status Midtrans gagal: " . $e->getMessage());
        }

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
            });

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
            ->orderBy('j.tanggal')
            ->orderBy('j.jam_mulai')
            ->get();

        $jadwalFormatted = $jadwal->map(function($j){
            $status = $j->pemesanan_status ?? ($j->tersedia ? 'tersedia' : 'tidak_tersedia');

            return [
                'id' => $j->id,
                'jam_mulai' => $j->jam_mulai,
                'jam_selesai' => $j->jam_selesai,
                'tanggal' => $j->tanggal,
                'harga_sewa' => $j->harga_sewa ?? 0,
                'section_id' => $j->section_id,
                'booking_status' => $status,
            ];
        });

        return response()->json($jadwalFormatted);
    }

    public function display()
    {
        $petugas = Auth::user();
        $pemilikId = $petugas->pemilik_id;

        $lapangan = Lapangan::query()
            ->with('kategori')
            ->when($pemilikId, fn ($q) => $q->where('pemilik_id', $pemilikId))
            ->get();

        if ($lapangan->isEmpty()) {
            $lapangan = Lapangan::with('kategori')->get();
        }

        if (! $pemilikId && $lapangan->isNotEmpty()) {
            $pemilikId = $lapangan->first()->pemilik_id;
        }

        $sectionQueues = $this->buildSectionQueues($lapangan->pluck('id'));

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
            'carouselImages' => $carouselImages,
            'petugasName' => $petugas->name,
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

                if ($paid) {
                    $pembayarans = Pembayaran::where('order_id', $orderId)->get();
                    foreach ($pembayarans as $pembayaran) {
                        if ($pembayaran->status !== 'berhasil') {
                            $pembayaran->update(['status' => 'berhasil']);
                        }

                        $pemesanan = $pembayaran->pemesanan;
                        if ($pemesanan && $pemesanan->status !== 'dibayar') {
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
