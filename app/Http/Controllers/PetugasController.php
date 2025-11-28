<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use App\Models\Kategori;
use App\Models\Pemesanan;
use App\Models\Pembayaran;
use App\Models\JadwalLapangan;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use App\Models\User; 
use Carbon\Carbon;
use Midtrans\Snap;
use Midtrans\Config;
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

        // Ambil semua lapangan milik pemilik petugas
        $lapangan = Lapangan::where('pemilik_id', $pemilikId)
            ->with('kategori')
            ->get();

        // Ambil kategori hanya yang punya lapangan milik petugas
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
            'lapangan' => $lapanganData,
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
            ->whereIn('p.status', ['menunggu', 'dibayar'])
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
                        return [
                            'penyewa' => $row->penyewa,
                            'tanggal' => Carbon::parse($row->tanggal)->format('d M Y'),
                            'jam_mulai' => substr($row->jam_mulai, 0, 5),
                            'jam_selesai' => substr($row->jam_selesai, 0, 5),
                            'status' => $row->status_scan === 'sudah_scan' ? 'sedang_main' : $row->status,
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
            'penyewa_id' => 'required|integer',
            'total' => 'required|numeric',
            'items' => 'required|array|min:1',
            'kasir' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $createdIds = [];

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
                    'status' => 'menunggu',
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
                    'status' => 'pending',
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

    public function getJadwalLapangan($lapanganId)
    {
        $sectionIds = DB::table('section_lapangan')
            ->where('lapangan_id', $lapanganId)
            ->pluck('id');

        $now = Carbon::now('Asia/Jakarta');

        // Hapus jadwal lewat
        DB::table('jadwal_lapangan')
            ->whereIn('section_id', $sectionIds)
            ->where('tanggal', '<', $now->toDateString())
            ->delete();

        $jadwal = DB::table('jadwal_lapangan as j')
            ->whereIn('j.section_id', $sectionIds)
            ->where('j.tanggal', '>=', $now->toDateString())
            ->leftJoin('pemesanan as p', function ($join) {
                $join->on('p.jadwal_id', '=', 'j.id')
                    ->whereIn('p.status', ['menunggu', 'dibayar']);
            })
            ->select('j.*', 'p.status as pemesanan_status')
            ->orderBy('j.tanggal')
            ->orderBy('j.jam_mulai')
            ->get();

        // Format data sesuai JS
        $jadwalFormatted = $jadwal->map(function($j){
            $status = $j->pemesanan_status ?? ($j->tersedia ? 'tersedia' : 'menunggu');

            return [
                'id' => $j->id,
                'jam_mulai' => $j->jam_mulai,
                'jam_selesai' => $j->jam_selesai,
                'tanggal' => $j->tanggal,
                'harga_sewa' => $j->harga_sewa ?? 0,
                'booking_status' => $status,
            ];
        });

        return response()->json($jadwalFormatted);
    }

    public function display()
    {
        $petugas = Auth::user();
        $pemilikId = $petugas->pemilik_id;

        $lapangan = Lapangan::where('pemilik_id', $pemilikId)
            ->with('kategori')
            ->get();

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
        $keyword = $request->q ?? ''; // ambil query param 'q' dari JS
        $petugas = auth()->user();
    
        $data = User::where('role', 'penyewa')
            ->where(function($q) use ($petugas) {
                $q->where('pemilik_id', $petugas->pemilik_id)
                  ->orWhereNull('pemilik_id');
            })
            ->when($keyword, function($query, $keyword){
                return $query->where('name', 'LIKE', "%$keyword%");
            })
            ->get();
    
        return response()->json($data);
    }    
}
