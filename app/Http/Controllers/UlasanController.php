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

    // ❗ CEK: penyewa sudah pernah mengirim ulasan untuk lapangan ini?
    $ulasanExisting = Ulasan::where('penyewa_id', $userId)
        ->whereHas('pemesanan', function ($q) use ($lapanganId) {
            $q->where('lapangan_id', $lapanganId);
        })
        ->first();

    if ($ulasanExisting) {
        return redirect()->back()->with('error', 'Anda sudah pernah mengirim ulasan untuk lapangan ini.');
    }

    // Validasi input
    $validated = $request->validate([
        'rating' => 'required|integer|min:1|max:5',
        'komentar' => 'required|string|max:1000',
    ]);

    // ❗ CEK: sudah scan tiket?
    $pemesanan = Pemesanan::where('penyewa_id', $userId)
        ->where('lapangan_id', $lapanganId)
        ->where('status_scan', 'sudah_scan')
        ->first();

    if (!$pemesanan) {
        return redirect()->back()->with('error', 'Anda belum scan tiket untuk lapangan ini.');
    }

    // Simpan ulasan baru
    Ulasan::create([
        'pemesanan_id' => $pemesanan->id,
        'penyewa_id' => $userId,
        'rating' => $validated['rating'],
        'komentar' => $validated['komentar'],
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

        $validated = $request->validate([
            'rating' => 'nullable|integer|min:1|max:5',
            'komentar' => 'required|string|max:1000',
        ]);

        $payload = [
            'komentar' => $validated['komentar'],
        ];

        if (isset($validated['rating']) && $validated['rating'] !== null) {
            $payload['rating'] = $validated['rating'];
        }

        $ulasan->update($payload);

        return redirect()->route('penyewa.detail', $ulasan->pemesanan->lapangan_id)
                        ->with('success', 'Ulasan berhasil diperbarui.');
    }
}
