<?php

namespace App\Http\Controllers\Penyewa;

use App\Http\Controllers\Controller;
use App\Models\Lapangan;
use App\Models\Ulasan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Halaman beranda penyewa.
     */
    public function beranda(Request $request): View
    {
        if (! Schema::hasTable('lapangan')) {
            return view('penyewa.beranda', [
                'lapangan' => collect(),
                'keyword' => $request->input('search'),
                'kategori' => $request->input('kategori', 'all'),
                'kategoris' => collect(),
            ]);
        }

        $keyword = $request->input('search');
        $selectedKategori = $request->input('kategori', 'all');

        $lapanganQuery = Lapangan::query();

        if ($keyword) {
            $lapanganQuery->where(function ($query) use ($keyword) {
                $query->where('nama_lapangan', 'like', "%{$keyword}%")
                    ->orWhere('lokasi', 'like', "%{$keyword}%");
            });
        }

        $hasKategoriColumn = Schema::hasColumn('lapangan', 'kategori');

        if ($hasKategoriColumn && $selectedKategori !== 'all') {
            $lapanganQuery->where('kategori', $selectedKategori);
        }

        $lapangan = $lapanganQuery->latest()->get();

        $kategoris = $hasKategoriColumn
            ? Lapangan::query()
                ->select('kategori')
                ->whereNotNull('kategori')
                ->distinct()
                ->pluck('kategori')
                ->filter()
                ->values()
            : collect();

        return view('penyewa.beranda', [
            'lapangan' => $lapangan,
            'keyword' => $keyword,
            'kategori' => $selectedKategori,
            'kategoris' => $kategoris,
        ]);
    }

    /**
     * Detail lapangan untuk penyewa.
     */
    public function detail(Lapangan $lapangan): View
    {
        $lainnya = Schema::hasTable('lapangan')
            ? Lapangan::query()
                ->where('id', '!=', $lapangan->getKey())
                ->latest()
                ->limit(6)
                ->get()
            : collect();

        $ulasans = (Schema::hasTable('ulasan') && Schema::hasTable('pemesanan') && Schema::hasTable('users'))
            ? Ulasan::query()
                ->select([
                    'ulasan.id',
                    'ulasan.rating',
                    'ulasan.komentar',
                    'ulasan.created_at',
                    'users.id as user_id',
                    'users.name as username',
                    'users.foto_profil as user_foto',
                ])
                ->join('pemesanan', 'pemesanan.id', '=', 'ulasan.pemesanan_id')
                ->join('users', 'users.id', '=', 'pemesanan.penyewa_id')
                ->where('pemesanan.lapangan_id', $lapangan->getKey())
                ->latest('ulasan.created_at')
                ->get()
            : collect();

        $ratingStat = $ulasans->isEmpty()
            ? (object) ['avg_rating' => 0, 'total' => 0]
            : (object) [
                'avg_rating' => (float) $ulasans->avg('rating'),
                'total' => $ulasans->count(),
            ];

        return view('penyewa.detail', [
            'lapangan' => $lapangan,
            'lainnya' => $lainnya,
            'ulasans' => $ulasans,
            'avgRating' => $ratingStat->avg_rating,
            'totalUlasan' => $ratingStat->total,
        ]);
    }

    /**
     * Halaman pemesanan penyewa.
     */
    public function pemesanan(): View
    {
        return view('penyewa.pemesanan');
    }

    /**
     * Halaman pembayaran penyewa.
     */
    public function pembayaran(): View
    {
        return view('penyewa.pembayaran');
    }

    /**
     * Halaman riwayat sewa penyewa.
     */
    public function riwayat(): View
    {
        return view('penyewa.riwayat');
    }

    /**
     * Halaman pengaturan akun penyewa.
     */
    public function akun(): View
    {
        return view('penyewa.akun');
    }
}
