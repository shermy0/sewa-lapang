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

        // Ambil semua kategori milik pemilik yang petugas ini kerja
        $kategori = Kategori::where('pemilik_id', $petugas->pemilik_id)->get();

        if ($kategori->isEmpty()) {
            return "Belum ada kategori yang dimiliki pemilik ini!";
        }

        // Ambil semua id kategori tsb
        $kategoriIds = $kategori->pluck('id');

        // Ambil semua lapangan dari kategori tsb
        $lapangan = Lapangan::whereIn('id_kategori', $kategoriIds)
            ->get()
            ->map(function ($l) {
                $fotoArray = is_array($l->foto) ? $l->foto : (json_decode($l->foto, true) ?? []);

                return [
                    'id' => $l->id,
                    'nama' => $l->nama_lapangan,
                    'foto' => count($fotoArray) ? $fotoArray[0] : null,
                    'fotoArray' => $fotoArray,
                    'harga' => $l->harga_sewa ?? 0,
                    'pemilik_id' => $l->kategoriData->pemilik_id ?? null,
                    'id_kategori' => $l->id_kategori                ];
            });

        return view('petugas.index', [
            'lapangan' => $lapangan,
            'kategori' => $kategori,
            'petugasName' => $petugas->name,
            'pemilikId' => $petugas->pemilik_id, // ambil langsung dari petugas
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
            'created_by' => Auth::id(), // petugas yang input
        ]);

        return redirect()->route('petugas.index')->with('sukses', 'Pemesanan berhasil ditambahkan!');
    }
}