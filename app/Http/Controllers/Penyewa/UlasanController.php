<?php

namespace App\Http\Controllers\Penyewa;

use App\Http\Controllers\Controller;
use App\Models\Lapangan;
use App\Models\Pemesanan;
use App\Models\Ulasan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class UlasanController extends Controller
{
    /**
     * Simpan atau perbarui ulasan penyewa untuk lapangan tertentu.
     */
    public function store(Request $request, Lapangan $lapangan): RedirectResponse
    {
        if (! Schema::hasTable('ulasan') || ! Schema::hasTable('pemesanan')) {
            return back()->with('error', 'Fitur ulasan belum tersedia. Hubungi admin.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'komentar' => ['nullable', 'string', 'max:1000'],
        ]);

        $penyewa = $request->user();

        $pemesanan = Pemesanan::query()
            ->where('penyewa_id', $penyewa->getKey())
            ->where('lapangan_id', $lapangan->getKey())
            ->where('status', 'selesai')
            ->latest('tanggal')
            ->first();

        if (! $pemesanan) {
            return back()->with('error', 'Selesaikan pemesanan terlebih dahulu sebelum memberi ulasan.');
        }

        $ulasan = Ulasan::query()
            ->where('penyewa_id', $penyewa->getKey())
            ->where('pemesanan_id', $pemesanan->getKey())
            ->first();

        if ($ulasan) {
            $ulasan->update([
                'rating' => $validated['rating'],
                'komentar' => $validated['komentar'] ?? null,
            ]);

            $message = 'Ulasan berhasil diperbarui.';
        } else {
            Ulasan::create([
                'pemesanan_id' => $pemesanan->getKey(),
                'penyewa_id' => $penyewa->getKey(),
                'rating' => $validated['rating'],
                'komentar' => $validated['komentar'] ?? null,
            ]);

            $message = 'Ulasan berhasil disimpan.';
        }

        return redirect()
            ->route('penyewa.detail', $lapangan)
            ->with('success', $message);
    }

    /**
     * Hapus ulasan milik penyewa.
     */
    public function destroy(Request $request, Ulasan $ulasan): RedirectResponse
    {
        $penyewa = $request->user();

        if ($ulasan->penyewa_id !== $penyewa->getKey()) {
            abort(403);
        }

        $lapanganId = optional($ulasan->pemesanan)->lapangan_id;

        $ulasan->delete();

        return $lapanganId
            ? redirect()
                ->route('penyewa.detail', $lapanganId)
                ->with('success', 'Ulasan berhasil dihapus.')
            : redirect()
                ->route('penyewa.beranda')
                ->with('success', 'Ulasan berhasil dihapus.');
    }
}
