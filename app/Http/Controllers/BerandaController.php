<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ulasan;
use App\Models\Pemesanan;
use App\Models\User;

class BerandaController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('search');
        $kategori = $request->input('kategori');

        // Ambil daftar kategori unik dari tabel lapangan (tahan terhadap tipe kolom non-enum)
        $kategoris = DB::table('lapangan')
            ->whereNotNull('kategori')
            ->distinct()
            ->pluck('kategori')
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        // Query data lapangan
        $lapangan = DB::table('lapangan')
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('nama_lapangan', 'like', "%{$keyword}%")
                      ->orWhere('lokasi', 'like', "%{$keyword}%");
            })
            ->when($kategori && $kategori !== 'all', function ($query) use ($kategori) {
                $query->where('kategori', $kategori);
            })
            ->get()
            ->map(function ($item) {
                $item->harga_display = $item->harga_per_jam ?? $item->harga_sewa ?? $item->harga ?? 0;
                return $item;
            });

        return view('penyewa.beranda', compact('lapangan', 'keyword', 'kategori', 'kategoris'));
    }

    public function detail($id)
    {
        $lapangan = DB::table('lapangan')->where('id', $id)->first();

        if (! $lapangan) {
            abort(404);
        }

        $lapangan->harga_display = $lapangan->harga_per_jam ?? $lapangan->harga_sewa ?? $lapangan->harga ?? 0;

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
            ->get()
            ->map(function ($item) {
                $item->harga_display = $item->harga_per_jam ?? $item->harga_sewa ?? $item->harga ?? 0;
                return $item;
            });

        return view('penyewa.detail', compact('lapangan', 'ulasans', 'lainnya', 'avgRating', 'totalUlasan'));
    }
}
