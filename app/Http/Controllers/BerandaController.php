<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Lapangan;
use App\Models\Kategori;
use App\Models\Ulasan;
use App\Models\Pemesanan;
use App\Models\User;

class BerandaController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('search');
        $kategori = $request->input('kategori');

        // Ambil semua kategori
        $kategoris = Kategori::all();
        $lapangan = Lapangan::with(['sections.jadwal', 'kategori'])->get();

        // Ambil semua lapangan + relasi kategori, sections, dan jadwal
        $lapangan = Lapangan::with(['kategori', 'sections.jadwal'])
            ->when($kategori && $kategori !== 'all', function ($query) use ($kategori) {
                $query->where('id_kategori', $kategori);
            })
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('nama_lapangan', 'like', "%$keyword%");
            })
            ->orderBy('id', 'asc')
            ->limit(12)
            ->get();

        return view('penyewa.beranda', compact('lapangan', 'keyword', 'kategori', 'kategoris'));
    }

    public function detail($id)
    {
        // Ambil data lapangan berdasarkan id
        $lapangan = Lapangan::with(['sections.jadwal', 'kategori'])
            ->where('id', $id)
            ->firstOrFail();

        // Ambil ulasan dan data lain yang sudah ada
        $ulasans = Ulasan::with(['pemesanan.penyewa'])
            ->whereHas('pemesanan', function ($query) use ($id) {
                $query->where('lapangan_id', $id);
            })
            ->get();

        $avgRating = $ulasans->avg('rating');
        $totalUlasan = $ulasans->count();

        // 🔹 Tambahkan bagian ini
        $lapanganLainnya = Lapangan::where('id', '!=', $id)
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        // Kirim semua variabel ke view
        return view('penyewa.detail', compact('lapangan', 'ulasans', 'avgRating', 'totalUlasan', 'lapanganLainnya'));
    }
}