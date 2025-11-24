<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermintaanPerubahan;
use Illuminate\Support\Facades\Auth;
use App\Models\Pemesanan;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;

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

        DB::transaction(function () use ($permintaan, $data) {
            $permintaan->update(['status' => $data['status']]);

            // Jika disetujui, pindah jadwal pemesanan utama, buka jadwal lama, kunci jadwal baru, dan batalkan booking lain yang bentrok
            if ($data['status'] === 'disetujui' && $permintaan->jadwal_baru_id) {
                $pemesananUtama = $permintaan->pemesanan;

                if ($permintaan->jadwalLama) {
                    $permintaan->jadwalLama->update(['tersedia' => true]);
                }

                if ($permintaan->jadwalBaru) {
                    $permintaan->jadwalBaru->update(['tersedia' => false]);
                }

                // Batalkan pemesanan lain yang sudah booking jadwal baru (menunggu/dibayar)
                $pemesananLain = Pemesanan::where('jadwal_id', $permintaan->jadwal_baru_id)
                    ->where('id', '!=', $pemesananUtama->id)
                    ->whereIn('status', ['menunggu', 'dibayar'])
                    ->get();

                foreach ($pemesananLain as $p) {
                    $p->update(['status' => 'batal']);
                    if ($p->jadwal) {
                        $p->jadwal->update(['tersedia' => true]);
                    }
                    Pembayaran::where('pemesanan_id', $p->id)
                        ->whereNotIn('status', ['berhasil', 'gagal'])
                        ->update(['status' => 'batal']);
                }

                // Update jadwal pemesanan utama ke jadwal baru
                $pemesananUtama->update([
                    'jadwal_id' => $permintaan->jadwal_baru_id,
                ]);
            }

            // Jika ditolak, jadwal baru dibuka kembali
            if ($data['status'] === 'ditolak' && $permintaan->jadwalBaru) {
                $permintaan->jadwalBaru->update(['tersedia' => true]);
            }
        });

        return response()->json(['success' => true]);
    }
}
