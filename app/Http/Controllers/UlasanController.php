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
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'required|string|max:1000',
        ]);

        $userId = Auth::id();

        // Ambil lapangan
        $lapangan = DB::table('lapangan')->where('id', $lapanganId)->first();

        // Ambil pemesanan user untuk lapangan ini yang sudah selesai
        $pemesanan = DB::table('pemesanan')
            ->where('penyewa_id', $userId)
            ->where('lapangan_id', $lapanganId)
            ->where('status', 'selesai')
            ->first();

        if (!$pemesanan) {
            return redirect()->back()->with('error', 'Anda belum pernah pesan lapangan. Pesan ' . $lapangan->nama_lapangan . ' untuk memberi ulasan');
        }

        // Simpan ulasan
        DB::table('ulasan')->insert([
            'pemesanan_id' => $pemesanan->id,
            'penyewa_id' => $userId,
            'rating' => $request->rating,
            'komentar' => $request->komentar,
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

        $ulasan->update([
            'rating' => $request->rating,
            'komentar' => $request->komentar,
        ]);

        return redirect()->route('penyewa.detail', $ulasan->pemesanan->lapangan_id)
                        ->with('success', 'Ulasan berhasil diperbarui.');
    }
}