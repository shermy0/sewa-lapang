<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\Lapangan;
use App\Models\Kategori;
use App\Models\Ulasan;
use App\Models\Pemesanan;
use App\Models\User;
use App\Models\Banner;
use Carbon\Carbon;

class BerandaController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('search');
        $kategori = $request->input('kategori');
        $banners = Banner::where('status', 'aktif')->get();

        // Ambil semua kategori
        $kategoris = Kategori::all();

        $hasSuspensionColumn = Schema::hasColumn('lapangan', 'is_suspended');

        $lapangan = Lapangan::with(['sections.jadwal', 'kategori'])
            ->when($hasSuspensionColumn, fn ($query) => $query->where('is_suspended', false))
            ->get();

        // Ambil semua lapangan + relasi kategori, sections, dan jadwal
        $lapangan = Lapangan::with(['kategori', 'sections.jadwal'])
            ->when($hasSuspensionColumn, fn ($query) => $query->where('is_suspended', false))
            ->when($kategori && $kategori !== 'all', function ($query) use ($kategori) {
                $query->where('id_kategori', $kategori);
            })
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('nama_lapangan', 'like', "%$keyword%");
            })
            ->orderBy('id', 'asc')
            ->limit(12)
            ->get();

        return view('penyewa.beranda', compact('lapangan', 'keyword', 'kategori', 'kategoris', 'banners'));
    }

    public function detail($id)
    {
        // Ambil data lapangan berdasarkan id
        $lapangan = Lapangan::with(['sections.jadwal', 'kategori'])
            ->where('id', $id)
            ->firstOrFail();

        $hasSuspensionColumn = Schema::hasColumn('lapangan', 'is_suspended');

        if ($hasSuspensionColumn && $lapangan->is_suspended) {
            abort(404);
        }

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
            ->when($hasSuspensionColumn, fn ($query) => $query->where('is_suspended', false))
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        // Kirim semua variabel ke view
        return view('penyewa.detail', compact('lapangan', 'ulasans', 'avgRating', 'totalUlasan', 'lapanganLainnya'));
    }
}
