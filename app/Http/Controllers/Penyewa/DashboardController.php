<?php

namespace App\Http\Controllers\Penyewa;

use App\Http\Controllers\Controller;
use App\Models\Lapangan;
use App\Models\Pemesanan;
use App\Models\Ulasan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $penyewa = Auth::user();
        $ulasans = collect();
        $avgRating = 0;
        $totalUlasan = 0;
        $existingUlasan = null;
        $canReview = false;

        if (Schema::hasTable('ulasan') && Schema::hasTable('pemesanan') && Schema::hasTable('users')) {
            $ulasans = Ulasan::query()
                ->with('penyewa')
                ->whereHas('pemesanan', function ($query) use ($lapangan) {
                    $query->where('lapangan_id', $lapangan->getKey());
                })
                ->latest()
                ->get();

            $avgRating = (float) $ulasans->avg('rating');
            $totalUlasan = $ulasans->count();

            if ($penyewa && $penyewa->role === 'penyewa') {
                $ulasans = $ulasans->map(function ($ulasan) use ($penyewa) {
                    $ulasan->is_mine = $penyewa->getKey() === $ulasan->penyewa_id;
                    return $ulasan;
                });

                if (Schema::hasTable('pemesanan')) {
                    $completedBookingExists = Pemesanan::query()
                        ->where('penyewa_id', $penyewa->getKey())
                        ->where('lapangan_id', $lapangan->getKey())
                        ->where('status', 'selesai')
                        ->exists();

                    $existingUlasan = Ulasan::query()
                        ->where('penyewa_id', $penyewa->getKey())
                        ->whereHas('pemesanan', function ($query) use ($lapangan) {
                            $query->where('lapangan_id', $lapangan->getKey());
                        })
                        ->latest()
                        ->first();

                    $canReview = $completedBookingExists || (bool) $existingUlasan;
                }
            }
        }

        return view('penyewa.detail', [
            'lapangan' => $lapangan,
            'lainnya' => $lainnya,
            'ulasans' => $ulasans,
            'avgRating' => $avgRating,
            'totalUlasan' => $totalUlasan,
            'existingUlasan' => $existingUlasan,
            'canReview' => $canReview,
        ]);
    }

    /**
     * Halaman pemesanan penyewa.
     */
    public function pemesanan(Request $request): View
    {
        $penyewa = $request->user();

        if (! Schema::hasTable('pemesanan')) {
            return view('penyewa.pemesanan', [
                'pemesanan' => collect(),
            ]);
        }

        $pemesanan = Pemesanan::query()
            ->with(['lapangan', 'ulasan'])
            ->where('penyewa_id', $penyewa->getKey())
            ->latest('tanggal')
            ->latest('jam_mulai')
            ->get();

        return view('penyewa.pemesanan', [
            'pemesanan' => $pemesanan,
        ]);
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
