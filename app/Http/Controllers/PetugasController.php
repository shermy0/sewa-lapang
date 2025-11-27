<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use App\Models\Kategori;
use Illuminate\Http\Request;
use App\Models\User; 
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

    public function create()
    {
        $kategori = Kategori::orderBy('nama_kategori')->get();
        return view('petugas.create', compact('kategori'));
    }

    public function storeCash(Request $request)
{
    $request->validate([
        'penyewa_id' => 'required|integer',
        'total' => 'required|numeric',
        'items' => 'required|array|min:1',
        'kasir' => 'required|string',
    ]);

    try {
        $data = $request->all();

        $pemesanan = Pemesanan::create([
            'kasir' => $data['kasir'],
            'penyewa_id' => $data['penyewa_id'],
            'metode' => 'cash',
            'total' => $data['total']
        ]);

        foreach($data['items'] as $item){
            PemesananItem::create([
                'pemesanan_id' => $pemesanan->id,
                'lapangan_id' => $item['id'],
                'jadwal_id' => $item['jadwal_id'],
                'harga' => $item['harga'],
                'durasi' => $item['durasi']
            ]);
        }

        return response()->json(['success'=>true]);

    } catch(\Exception $e){
        \Log::error($e->getMessage());
        return response()->json(['success'=>false,'message'=>$e->getMessage()]);
    }
}

public function storeMidtrans(Request $request)
{
    // Kirim ke Midtrans (payment gateway) dulu
    // Contoh: generate Snap token, redirect, dll
    $request->validate([
        'penyewa_id' => 'required|integer',
        'total' => 'required|numeric',
        'items' => 'required|array|min:1',
        'kasir' => 'required|string',
    ]);

    // TODO: Integrasi Midtrans
    return response()->json([
        'success' => true,
        'redirect_url' => '/midtrans/payment-page' // misal nanti redirect ke page midtrans
    ]);
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
}