<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use App\Models\Kategori;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
use App\Models\User; 
use Carbon\Carbon;
use Midtrans\Snap;
use App\Models\Pembayaran;
use Midtrans\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\JadwalLapangan;

class PetugasController extends Controller
{
    public function index()
    {
        $petugas = Auth::user();
        $petugasName = $petugas->name;

        // Ambil semua lapangan milik pemilik petugas
        $lapangan = Lapangan::where('pemilik_id', $petugas->pemilik_id)
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

            $hargaFormatted = $harga ? number_format($harga, 0, ',', '.') : '-';

            return [
                'id' => $l->id,
                'nama' => $l->nama_lapangan,
                'deskripsi' => $l->deskripsi ?? '',
                'id_kategori' => $l->id_kategori,
                'kategori_nama' => $l->kategori->nama_kategori ?? '',
                'foto' => $fotoArray,
                'hargaRataRata' => $hargaFormatted,
            ];                
        });

        return view('petugas.index', [
            'kategori' => $kategori,
            'lapangan' => $lapanganData,
            'petugasName' => $petugasName,
        ]);
    }

    public function tiket()
    {
        // Bisa kirim data tiket kalau perlu
        $tiket = []; // Contoh, bisa diganti query dari DB
        return view('petugas.tiket', compact('tiket'));
    }

    public function create()
    {
        $kategori = Kategori::orderBy('nama_kategori')->get();
        return view('petugas.create', compact('kategori'));
    }

    public function storeCash(Request $request)
{
    $request->validate([
        'penyewa_id' => 'required|exists:users,id',
        'items' => 'required|array|min:1',
        'items.*.lapangan_id' => 'required|exists:lapangan,id',
        'items.*.jadwal_id' => 'required|exists:jadwal_lapangan,id',
        'items.*.harga' => 'required|numeric',
    ]);

    $item = $request->items[0]; // ambil item pertama
    $lapangan = Lapangan::findOrFail($item['lapangan_id']);
    $jadwal = JadwalLapangan::findOrFail($item['jadwal_id']);    

    try {
        foreach($request->items as $item){
            $kodeTiket = 'TKT-' . strtoupper(uniqid());

            Pemesanan::create([
                'penyewa_id'  => $request->penyewa_id,
                'lapangan_id' => $item['id'],
                'jadwal_id'   => $item['jadwal_id'],
                'status'      => 'dibayar',
                'kode_tiket'  => $kodeTiket,
                'status_scan' => 'belum_scan',
                'waktu_scan'  => null
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pemesanan cash berhasil!',
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ]);
    }
}

    public function getJadwalLapangan($lapanganId)
    {
        // Ambil semua section id untuk lapangan ini
        $sectionIds = DB::table('section_lapangan')
            ->where('lapangan_id', $lapanganId)
            ->pluck('id');

        $now = Carbon::now('Asia/Jakarta');

        // Hapus jadwal yang sudah lewat
        DB::table('jadwal_lapangan')
            ->whereIn('section_id', $sectionIds)
            ->where('tanggal', '<', $now->toDateString())
            ->delete();

        // Ambil jadwal tersisa
        $jadwal = DB::table('jadwal_lapangan')
            ->whereIn('section_id', $sectionIds)
            ->where('tanggal', '>=', $now->toDateString())
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        // Format data sesuai JS
        $jadwalFormatted = $jadwal->map(function($j){
            return [
                'jam_mulai' => $j->jam_mulai,
                'jam_selesai' => $j->jam_selesai,
                'tanggal' => $j->tanggal,
                'harga_sewa' => $j->harga_sewa ?? 0,
                'booking_status' => $j->booking_status ?? 'tersedia', // default 'tersedia' jika null
            ];
        });

        return response()->json($jadwalFormatted);
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
            ->where('pemilik_id', $petugas->pemilik_id)
            ->when($keyword, function($query, $keyword){
                return $query->where('name', 'LIKE', "%$keyword%");
            })
            ->get();
    
        return response()->json($data);
    }    

    public function storeMidtrans(Request $request)
    {
        try {
            \Log::info('📦 Request ke getSnapToken', $request->all());

            $item = $request->items[0]; // ambil item pertama

            $lapangan = Lapangan::findOrFail($item['lapangan_id']);
            $jadwal = JadwalLapangan::findOrFail($item['jadwal_id']);            

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

            try {
                Config::$serverKey = config('midtrans.server_key');
                Config::$isProduction = config('midtrans.is_production', false);
            
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
            
            } catch (\Exception $e) {
                \Log::error('🔥 Midtrans Error: ' . $e->getMessage(), [
                    'order_id' => $orderId,
                    'gross_amount' => $hargaSewa,
                    'server_key' => config('midtrans.server_key'),
                ]);
                return response()->json(['error' => $e->getMessage()], 500);
            }
            

        } catch (\Exception $e) {
            \Log::error('🔥 ERROR getSnapToken: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}