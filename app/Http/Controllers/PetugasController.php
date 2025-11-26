<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Lapangan;
use App\Models\Kategori;
use App\Models\JadwalLapangan;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PetugasController extends Controller
{
    // Halaman kasir/petugas
    public function index()
    {
        $petugas = auth()->user();
    
        // Ambil semua kategori milik pemilik yang login
        $kategori = Kategori::whereNotNull('pemilik_id')->get();
    
        if ($kategori->isEmpty()) {
            return "Belum ada kategori yang punya pemilik_id!";
        }
    
        // Ambil semua id kategori itu
        $kategoriIds = $kategori->pluck('id');
    
        // Ambil semua lapangan dari kategori tsb
        $lapangan = Lapangan::whereIn('id_kategori', $kategoriIds)
            ->get()
            ->map(function ($l) {
                return [
                    'id' => $l->id,
                    'nama' => $l->nama_lapangan,   // FIX
                    'foto' => $l->foto_utama ?? null,
                    'harga' => $l->harga_sewa,     // FIX
                    'status' => $l->is_suspended ? 'booked' : 'available',
                    'pemilik_id' => $l->kategoriData->pemilik_id,
                ];
            });
    
        return view('petugas.index', [
            'lapangan' => $lapangan,
            'kategori' => $kategori,
            'petugasName' => $petugas->name,
            'pemilikId' => $kategori->first()->pemilik_id,
        ]);
    }    

    // Ambil jadwal berdasarkan lapangan
    public function getJadwal($lapangan_id)
    {
        $jadwal = JadwalLapangan::where('lapangan_id', $lapangan_id)->get();
        return response()->json($jadwal);
    }

    // Simpan pesanan
    public function store(Request $request)
    {
        $request->validate([
            'penyewa_id' => 'required|exists:users,id',
            'lapangan_id' => 'required|exists:lapangan,id',
            'jadwal_id' => 'required|exists:jadwal_lapangan,id',
        ]);

        Pemesanan::create([
            'kode_pemesanan' => 'P-' . time(),
            'penyewa_id' => $request->penyewa_id,
            'lapangan_id' => $request->lapangan_id,
            'jadwal_id' => $request->jadwal_id,
            'status' => 'pending',
            'created_by' => Auth::id(), // petugas yang input
        ]);

        return redirect()->route('petugas.index')->with('sukses', 'Pemesanan berhasil ditambahkan!');
    }
}
