<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ulasan;
use App\Models\Pemesanan;
use App\Models\User;
use App\Models\Kategori;


class BerandaController extends Controller
{

    public function index(Request $request)
    {
        $keyword = $request->input('search');
        $kategori = $request->input('kategori');

        // Ambil semua kategori dari tabel kategori
        $kategoris = Kategori::all();

        // Ambil data lapangan + kategori + jadwal
        $lapangan = DB::table('lapangan')
            ->leftJoin('kategori', 'lapangan.id_kategori', '=', 'kategori.id')
            ->leftJoin('jadwal_lapangan', 'lapangan.id', '=', 'jadwal_lapangan.lapangan_id')
            ->select(
                'lapangan.id as lapangan_id',
                'lapangan.nama_lapangan',
                'lapangan.lokasi',
                'lapangan.foto',
                'lapangan.id_kategori',
                'kategori.nama_kategori',
                'jadwal_lapangan.id as jadwal_id',
                'jadwal_lapangan.tanggal',
                'jadwal_lapangan.jam_mulai',
                'jadwal_lapangan.jam_selesai',
                'jadwal_lapangan.harga_sewa',
                'jadwal_lapangan.tersedia'
            )
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('lapangan.nama_lapangan', 'like', "%{$keyword}%")
                      ->orWhere('lapangan.lokasi', 'like', "%{$keyword}%");
                });
            })
            ->when($kategori && $kategori !== 'all', function ($query) use ($kategori) {
                $query->where('lapangan.id_kategori', $kategori);
            })
            ->orderBy('lapangan.id', 'asc')
            ->limit(12)
            ->get();

        return view('penyewa.beranda', compact('lapangan', 'keyword', 'kategori', 'kategoris'));
    }

    public function detail($id)
    {
        $lapangan = DB::table('lapangan')
            ->leftJoin('kategori', 'lapangan.id_kategori', '=', 'kategori.id')
            ->select('lapangan.*', 'kategori.nama_kategori')
            ->where('lapangan.id', $id)
            ->first();

        $ulasans = DB::table('ulasan')
            ->join('pemesanan', 'ulasan.pemesanan_id', '=', 'pemesanan.id')
            ->join('users', 'pemesanan.penyewa_id', '=', 'users.id')
            ->where('pemesanan.lapangan_id', $id)
            ->select(
                'ulasan.*',
                'users.name as username',
                'users.foto_profil as user_foto',
                'pemesanan.penyewa_id as user_id'
            )
            ->get();

        $avgRating = $ulasans->avg('rating');
        $totalUlasan = $ulasans->count();

        $lainnya = DB::table('lapangan')
            ->where('id', '!=', $id)
            ->orderBy('id', 'desc')
            ->take(6)
            ->get();

        return view('penyewa.detail', compact('lapangan', 'ulasans', 'lainnya', 'avgRating', 'totalUlasan'));
    }
}