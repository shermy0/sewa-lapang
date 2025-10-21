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

            $lapangan->avgRating = DB::table('ulasan')
                ->join('pemesanan', 'ulasan.pemesanan_id', '=', 'pemesanan.id')
                ->where('pemesanan.lapangan_id', $lapangan->id)
                ->avg('rating');
        }

        return view('pemilik.favorit', compact('lapanganFavorit'));
    }
}