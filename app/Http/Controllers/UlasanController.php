<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Ulasan;
use App\Models\Pemesanan;
use App\Models\User;
use Carbon\Carbon;

class UlasanController extends Controller
{
    public function simpan(Request $request, $lapanganId)
    {
        $userId = Auth::id();

        // 1. CEK: penyewa sudah pernah mengirim ulasan untuk lapangan ini?
        $ulasanExisting = Ulasan::where('penyewa_id', $userId)
            ->whereHas('pemesanan', function ($q) use ($lapanganId) {
                $q->where('lapangan_id', $lapanganId);
            })
            ->first();

        if ($ulasanExisting) {
            // PERBAIKAN 1: Return JSON Error (bukan redirect)
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah pernah mengirim ulasan untuk lapangan ini.'
            ], 422);
        }

        // 2. Validasi input
        // Karena di JS kita sudah pakai header 'Accept: application/json',
        // Jika validasi gagal, Laravel otomatis return JSON error, jadi ini aman.
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'required|string|max:1000',
        ]);

        // 3. CEK: sudah scan tiket?
        $pemesanan = Pemesanan::where('penyewa_id', $userId)
            ->where('lapangan_id', $lapanganId)
            ->whereIn('status_scan', ['sudah_scan', 'masuk_lapang'])
            ->first();

        if (!$pemesanan) {
            // PERBAIKAN 2: Return JSON Error (bukan redirect)
            return response()->json([
                'success' => false,
                'message' => 'Anda belum scan tiket untuk lapangan ini (atau belum ada riwayat sewa).'
            ], 422);
        }

        // 4. Simpan ulasan baru
        Ulasan::create([
            'pemesanan_id' => $pemesanan->id,
            'penyewa_id' => $userId,
            'rating' => $validated['rating'],
            'komentar' => $validated['komentar'],
        ]);

        // PERBAIKAN 3: Return JSON Sukses (bukan redirect)
        return response()->json([
            'success' => true,
            'message' => 'Ulasan berhasil ditambahkan!'
        ]);
    }

    // Method di bawah ini TIDAK PERLU DIUBAH (karena diakses lewat load halaman biasa, bukan AJAX)
    public function edit($id)
    {
        $ulasan = Ulasan::find($id);
        if (!$ulasan) {
            return redirect()->back()->with('error', 'Ulasan tidak ditemukan.');
        }
        if(auth()->id() != $ulasan->pemesanan->penyewa_id) {
            abort(403);
        }

        return view('penyewa.edit', compact('ulasan'));
    }

    public function destroy($id)
    {
        $ulasan = Ulasan::find($id);
        if (!$ulasan) {
            return redirect()->back()->with('error', 'Ulasan tidak ditemukan.');
        }

        if(auth()->id() != $ulasan->pemesanan->penyewa_id) {
            abort(403);
        }

        $ulasan->delete();

        return redirect()->back()->with('success', 'Ulasan berhasil dihapus.');
    }

public function update(Request $request, $id)
{
    $ulasan = Ulasan::find($id);
    if (!$ulasan) {
        return response()->json(['success'=>false,'message'=>'Ulasan tidak ditemukan'],404);
    }

    if(auth()->id() != $ulasan->pemesanan->penyewa_id) {
        return response()->json(['success'=>false,'message'=>'Akses ditolak'],403);
    }

    $validated = $request->validate([
        'rating' => 'nullable|integer|min:1|max:5',
        'komentar' => 'required|string|max:1000',
    ]);

    $payload = ['komentar'=>$validated['komentar']];
    if(isset($validated['rating'])) $payload['rating'] = $validated['rating'];

    $ulasan->update($payload);

    return response()->json(['success'=>true,'message'=>'Ulasan berhasil diperbarui']);
}

}
