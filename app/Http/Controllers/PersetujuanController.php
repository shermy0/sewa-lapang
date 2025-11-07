<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request; // ← ini yang kurang!
use App\Models\PermintaanPerubahan;

class PersetujuanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $permintaan = PermintaanPerubahan::with([
            'pemesanan.user',
            'pemesanan.lapangan',
            'jadwalLama.section',
            'jadwalBaru.section'
        ])
        ->when($search, function ($query, $search) {
            $query->whereHas('pemesanan.user', fn($q) => $q->where('name', 'like', "%$search%"))
                  ->orWhereHas('pemesanan.lapangan', fn($q) => $q->where('nama_lapangan', 'like', "%$search%"));
        })
        ->when($status, fn($query, $status) => $query->where('status', $status))
        ->orderByDesc('created_at')
        ->paginate(8);

        return view('persetujuan.index', compact('permintaan'));
    }

    public function update($id)
    {
        $data = request()->validate([
            'status' => 'required|in:menunggu,disetujui,ditolak',
        ]);

        $permintaan = PermintaanPerubahan::findOrFail($id);
        $permintaan->status = $data['status'];
        $permintaan->save();

        // Jika disetujui, update jadwal pemesanan otomatis
        if ($data['status'] === 'disetujui' && $permintaan->jadwal_baru_id) {
            $pemesanan = $permintaan->pemesanan;
            $pemesanan->jadwal_id = $permintaan->jadwal_baru_id;
            $pemesanan->save();
        }

        return response()->json(['success' => true]);
    }
}
