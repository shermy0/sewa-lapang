<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermintaanPerubahan;
use Illuminate\Support\Facades\Auth;

class PersetujuanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $pemilikId = Auth::id();

        $permintaan = PermintaanPerubahan::with([
            'pemesanan.user',
            'pemesanan.lapangan',
            'jadwalLama.section',
            'jadwalBaru.section'
        ])
        ->whereHas('pemesanan.lapangan', fn ($q) => $q->where('pemilik_id', $pemilikId))
        ->when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('pemesanan.user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%");
                })->orWhereHas('pemesanan.lapangan', function ($lapanganQuery) use ($search) {
                    $lapanganQuery->where('nama_lapangan', 'like', "%{$search}%");
                });
            });
        })
        ->when($status !== null && $status !== '', fn($query) => $query->where('status', $status))
        ->orderByDesc('created_at')
        ->paginate(8);

        return view('persetujuan.index', compact('permintaan'));
    }

    public function update($id)
    {
        $data = request()->validate([
            'status' => 'required|in:menunggu,disetujui,ditolak',
        ]);

        $permintaan = PermintaanPerubahan::with([
            'pemesanan.lapangan',
            'jadwalLama',
            'jadwalBaru',
        ])->findOrFail($id);

        if ($permintaan->pemesanan->lapangan->pemilik_id !== Auth::id()) {
            abort(403);
        }

        $permintaan->status = $data['status'];
        $permintaan->save();

        if ($data['status'] === 'disetujui' && $permintaan->jadwal_baru_id) {
            if ($permintaan->jadwalLama) {
                $permintaan->jadwalLama->update(['tersedia' => true]);
            }

            if ($permintaan->jadwalBaru) {
                $permintaan->jadwalBaru->update(['tersedia' => false]);
            }

            $permintaan->pemesanan->update([
                'jadwal_id' => $permintaan->jadwal_baru_id,
            ]);
        }

        if ($data['status'] === 'ditolak' && $permintaan->jadwalBaru) {
            $permintaan->jadwalBaru->update(['tersedia' => true]);
        }

        return response()->json(['success' => true]);
    }
}
