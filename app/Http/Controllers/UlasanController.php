<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Ulasan;
use App\Models\Pemesanan;
use App\Models\User;

class UlasanController extends Controller
{
    public function simpan(Request $request, $lapanganId)
    {
        $userId = Auth::id();

        $existingRating = Ulasan::where('penyewa_id', $userId)
            ->whereHas('pemesanan', function ($query) use ($lapanganId) {
                $query->where('lapangan_id', $lapanganId);
            })
            ->whereNotNull('rating')
            ->orderBy('created_at')
            ->value('rating');

        $rules = [
            'komentar' => 'required|string|max:1000',
        ];

        if (is_null($existingRating)) {
            $rules['rating'] = 'required|integer|min:1|max:5';
        } else {
            $rules['rating'] = 'nullable|integer|min:1|max:5';
        }

        $validated = $request->validate($rules);

        // Ambil lapangan
        $lapangan = DB::table('lapangan')->where('id', $lapanganId)->first();

        // Cek apakah user sudah melakukan scan tiket untuk lapangan ini
        $pemesanan = DB::table('pemesanan')
            ->where('penyewa_id', $userId)
            ->where('lapangan_id', $lapanganId)
            ->where('status_scan', 'sudah_scan')
            ->first();

        if (!$pemesanan) {
            return redirect()->back()->with('error', 'Anda belum scan tiket untuk lapangan ini.');
        }

        $ratingValue = is_null($existingRating) ? $validated['rating'] : $existingRating;

        DB::table('ulasan')->insert([
            'pemesanan_id' => $pemesanan->id,
            'penyewa_id' => $userId,
            'rating' => $ratingValue,
            'komentar' => $validated['komentar'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Ulasan berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $ulasan = Ulasan::findOrFail($id);
        if(auth()->id() != $ulasan->pemesanan->penyewa_id) {
            abort(403);
        }

        return view('penyewa.edit', compact('ulasan'));
    }

    public function destroy($id)
    {
        $ulasan = Ulasan::findOrFail($id);

        if(auth()->id() != $ulasan->pemesanan->penyewa_id) {
            abort(403);
        }

        $ulasan->delete();

        return redirect()->back()->with('success', 'Ulasan berhasil dihapus.');
    }

    public function update(Request $request, $id)
    {
        $ulasan = Ulasan::findOrFail($id);

        if(auth()->id() != $ulasan->pemesanan->penyewa_id) {
            abort(403);
        }

        $request->validate([
            'komentar' => 'required|string|max:1000',
        ]);

        $ulasan->update([
            'komentar' => $request->komentar,
        ]);

        return redirect()->route('penyewa.detail', $ulasan->pemesanan->lapangan_id)
                        ->with('success', 'Ulasan berhasil diperbarui.');
    }
}
