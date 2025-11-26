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
    public function kasir()
    {
        $petugas = auth()->user();

        // Ambil pemilik yang mempekerjakan petugas
        $pemilikId = $petugas->pemilik_id; // pastikan field pemilik_id ada di users

        // Ambil kategori milik pemilik itu
        $kategori = Kategori::where('pemilik_id', $pemilikId)->get();
        $kategoriIds = $kategori->pluck('id');

        // Ambil lapangan sesuai kategori milik pemilik
        $lapangan = Lapangan::whereIn('id_kategori', $kategoriIds)
            ->with('kategoriData') // relasi kategori
            ->get()
            ->map(function ($l) {
                // Pastikan foto disimpan sebagai array JSON di DB
                $fotoArray = $l->foto_lapangan ? json_decode($l->foto_lapangan, true) : [];
                
                return [
                    'id' => $l->id,
                    'nama' => $l->nama_lapangan,
                    'harga' => $l->harga_sewa,
                    'status' => $l->is_suspended ? 'booked' : 'available',
                    'id_kategori' => $l->id_kategori,
                    'kategori_nama' => $l->kategoriData->nama_kategori ?? 'Kategori Tidak Diketahui',
                    'fotoArray' => $fotoArray, // array foto untuk carousel
                ];
            });

        return view('petugas.index', [
            'lapangan' => $lapangan,
            'kategori' => $kategori,
            'petugasName' => $petugas->name,
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
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('petugas.kasir')->with('sukses', 'Pemesanan berhasil ditambahkan!');
    }
}
