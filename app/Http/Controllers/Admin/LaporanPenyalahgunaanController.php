<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaporanPenyalahgunaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LaporanPenyalahgunaanController extends Controller
{
    public function index(Request $request)
    {
        $reports = LaporanPenyalahgunaan::with(['pelapor', 'terlapor', 'lapangan', 'penangan'])
            ->status($request->input('status'))
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('deskripsi', 'like', "%{$search}%")
                        ->orWhere('kategori', 'like', "%{$search}%")
                        ->orWhereHas('pelapor', function ($pelaporQuery) use ($search) {
                            $pelaporQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('terlapor', function ($terlaporQuery) use ($search) {
                            $terlaporQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('lapangan', function ($lapanganQuery) use ($search) {
                            $lapanganQuery->where('nama_lapangan', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        return view('admin.laporan.penyalahgunaan.index', [
            'reports' => $reports,
            'statuses' => LaporanPenyalahgunaan::STATUSES,
        ]);
    }

    public function show(LaporanPenyalahgunaan $laporanPenyalahgunaan)
    {
        $laporanPenyalahgunaan->load(['pelapor', 'terlapor', 'lapangan', 'penangan']);

        return view('admin.laporan.penyalahgunaan.show', [
            'report' => $laporanPenyalahgunaan,
            'statuses' => LaporanPenyalahgunaan::STATUSES,
        ]);
    }

    public function updateStatus(Request $request, LaporanPenyalahgunaan $laporanPenyalahgunaan)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(LaporanPenyalahgunaan::STATUSES)],
            'catatan_admin' => ['nullable', 'string'],
        ]);

        $data = [
            'status' => $validated['status'],
            'catatan_admin' => $validated['catatan_admin'] ?? null,
        ];

        if ($validated['status'] === 'pending') {
            $data['ditangani_oleh'] = null;
            $data['ditangani_pada'] = null;
        } else {
            $data['ditangani_oleh'] = Auth::id();
            $data['ditangani_pada'] = now();
        }

        $laporanPenyalahgunaan->update($data);

        return back()->with('success', 'Status laporan berhasil diperbarui.');
    }

    public function destroy(LaporanPenyalahgunaan $laporanPenyalahgunaan)
    {
        $laporanPenyalahgunaan->delete();

        return redirect()
            ->route('admin.laporan.penyalahgunaan.index')
            ->with('success', 'Laporan penyalahgunaan berhasil dihapus.');
    }
}
