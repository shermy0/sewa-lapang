<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FavoritLapanganController extends Controller
{
    public function store(int $lapanganId): RedirectResponse
    {
        $this->authorizePenyewa();

        DB::table('favorit_lapangan')->updateOrInsert(
            [
                'penyewa_id' => Auth::id(),
                'lapangan_id' => $lapanganId,
            ],
            ['updated_at' => now(), 'created_at' => now()]
        );

        return back()->with('success', 'Lapangan ditambahkan ke favorit.');
    }

    public function destroy(int $lapanganId): RedirectResponse
    {
        $this->authorizePenyewa();

        DB::table('favorit_lapangan')
            ->where('penyewa_id', Auth::id())
            ->where('lapangan_id', $lapanganId)
            ->delete();

        return back()->with('success', 'Lapangan dihapus dari favorit.');
    }

    protected function authorizePenyewa(): void
    {
        abort_unless(Auth::check() && Auth::user()->role === 'penyewa', 403);
    }
}
