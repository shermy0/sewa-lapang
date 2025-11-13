<?php

namespace App\Http\Controllers\Penyewa;

use App\Http\Controllers\Controller;
use App\Models\Lapangan;
use App\Models\LaporanPenyalahgunaan;
use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LaporanPenyalahgunaanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $reports = LaporanPenyalahgunaan::with(['lapangan', 'terlapor', 'penangan'])
            ->where('pelapor_id', $user->id)
            ->status($request->input('status'))
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('deskripsi', 'like', "%{$search}%")
                        ->orWhereHas('lapangan', function ($lapanganQuery) use ($search) {
                            $lapanganQuery->where('nama_lapangan', 'like', "%{$search}%")
                                ->orWhere('lokasi', 'like', "%{$search}%");
                        })
                        ->orWhereHas('terlapor', function ($terlaporQuery) use ($search) {
                            $terlaporQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        $lapanganOptions = Lapangan::whereHas('pemesanan', function ($query) use ($user) {
                $query->where('penyewa_id', $user->id);
            })
            ->orderBy('nama_lapangan')
            ->get(['id', 'nama_lapangan']);

        return view('penyewa.laporan.index', [
            'reports' => $reports,
            'lapanganOptions' => $lapanganOptions,
            'statuses' => LaporanPenyalahgunaan::STATUSES,
            'categories' => LaporanPenyalahgunaan::CATEGORIES,
            'defaultLapangan' => $request->input('lapangan_id'),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'lapangan_id' => ['required', 'exists:lapangan,id'],
            'kategori' => ['required', Rule::in(array_keys(LaporanPenyalahgunaan::CATEGORIES))],
            'deskripsi' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        $pernahBooking = Pemesanan::where('lapangan_id', $validated['lapangan_id'])
            ->where('penyewa_id', $user->id)
            ->exists();

        if (! $pernahBooking) {
            return back()->withErrors([
                'lapangan_id' => 'Anda hanya dapat melaporkan lapangan yang pernah dipesan.',
            ])->withInput();
        }

        $lapangan = Lapangan::with('pemilik')->findOrFail($validated['lapangan_id']);

        LaporanPenyalahgunaan::create([
            'pelapor_id' => $user->id,
            'terlapor_id' => $lapangan->pemilik_id,
            'lapangan_id' => $lapangan->id,
            'kategori' => $validated['kategori'],
            'deskripsi' => $validated['deskripsi'],
            'status' => 'pending',
            'catatan_admin' => null,
            'ditangani_oleh' => null,
            'ditangani_pada' => null,
        ]);

        return redirect()->back()->with('success', 'Terima kasih, laporan Anda sudah kami terima.');
    }
}
