<?php
namespace App\Http\Controllers;

use App\Models\PermintaanPerubahan;

class PersetujuanController extends Controller
{
    public function index()
    {
        $permintaan = PermintaanPerubahan::with([
            'pemesanan.user',
            'pemesanan.lapangan',
            'jadwalLama.section',
            'jadwalBaru.section'
        ])
        ->orderByDesc('created_at')
        ->get();

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
