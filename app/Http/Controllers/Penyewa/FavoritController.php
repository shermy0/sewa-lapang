<?php

namespace App\Http\Controllers\Penyewa;

use App\Http\Controllers\Controller;
use App\Models\Lapangan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class FavoritController extends Controller
{
    /**
     * Daftar lapangan favorit penyewa.
     */
    public function index(Request $request): View
    {
        $penyewa = $request->user();

        $favoritLapangan = Schema::hasTable('favorit_lapangan')
            ? $penyewa->favoritLapangan()->with('pemilik')->orderByPivot('created_at', 'desc')->get()
            : collect();

        return view('penyewa.favorit', [
            'favoritLapangan' => $favoritLapangan,
        ]);
    }

    /**
     * Tambahkan lapangan ke favorit penyewa.
     */
    public function store(Request $request, Lapangan $lapangan): RedirectResponse
    {
        $penyewa = $request->user();

        $penyewa->favoritLapangan()->syncWithoutDetaching([
            $lapangan->getKey() => [
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        return back()->with('success', 'Lapangan ditambahkan ke favorit.');
    }

    /**
     * Hapus lapangan dari favorit penyewa.
     */
    public function destroy(Request $request, Lapangan $lapangan): RedirectResponse
    {
        $penyewa = $request->user();

        $penyewa->favoritLapangan()->detach($lapangan->getKey());

        return back()->with('success', 'Lapangan dihapus dari favorit.');
    }
}
