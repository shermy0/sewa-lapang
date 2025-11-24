<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Lapangan;

class FavoritController extends Controller
{
    public function index()
    {
        $pemilik = Auth::user();

        // Ambil semua lapangan milik pemilik
        $lapanganFavorit = Lapangan::with(['favoritedBy:id,name'])
            ->where('pemilik_id', $pemilik->id)
            ->get();

        // Tambahin perhitungan rating via join ke tabel pemesanan
        foreach ($lapanganFavorit as $lapangan) {
            $lapangan->totalUlasan = DB::table('ulasan')
                ->join('pemesanan', 'ulasan.pemesanan_id', '=', 'pemesanan.id')
                ->where('pemesanan.lapangan_id', $lapangan->id)
                ->count();

            $ratingSummary = DB::table('ulasan')
                ->join('pemesanan', 'ulasan.pemesanan_id', '=', 'pemesanan.id')
                ->select('ulasan.penyewa_id', DB::raw('MAX(ulasan.rating) as rating'))
                ->where('pemesanan.lapangan_id', $lapangan->id)
                ->groupBy('ulasan.penyewa_id')
                ->get();

            $lapangan->avgRating = $ratingSummary->avg('rating');
        }

        return view('pemilik.favorit', compact('lapanganFavorit'));
    }
}
