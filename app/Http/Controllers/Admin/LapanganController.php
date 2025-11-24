<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lapangan;
use Illuminate\Http\Request;

class LapanganController extends Controller
{
    public function index(Request $request)
    {
        $query = Lapangan::with('pemilik')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lapangan', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        if ($kategori = $request->input('kategori')) {
            $query->where('kategori', $kategori);
        }

        $lapangan = $query->paginate(10)->appends($request->query());
        $categories = Lapangan::whereNotNull('kategori')
            ->select('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        return view('admin.lapangan.index', [
            'lapangan' => $lapangan,
            'categories' => $categories,
            'activeCategory' => $kategori,
        ]);
    }

    public function show(Lapangan $lapangan)
    {
        $lapangan->load([
            'pemilik',
            'sections.jadwal',
            'laporanPenyalahgunaan' => fn ($query) => $query->latest()->with(['pelapor', 'terlapor']),
        ]);

        return view('admin.lapangan.show', [
            'lapangan' => $lapangan,
            'totalJadwal' => $lapangan->sections->sum(fn ($section) => $section->jadwal->count()),
            'recentReports' => $lapangan->laporanPenyalahgunaan->take(5),
        ]);
    }
}
