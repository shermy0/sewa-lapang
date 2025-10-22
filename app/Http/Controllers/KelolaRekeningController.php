<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RekeningPemilik;
use App\Models\PencairanDana;

class KelolaRekeningController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $rekening = RekeningPemilik::where('pemilik_id', $user->id)->first();
        $riwayat = PencairanDana::where('pemilik_id', $user->id)->latest()->get();

        return view('rekening.index', compact('rekening', 'riwayat'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nama_bank' => 'required|string|max:100',
            'nomor_rekening' => 'required|string|max:50',
            'atas_nama' => 'required|string|max:100',
        ]);

        RekeningPemilik::updateOrCreate(
            ['pemilik_id' => $user->id],
            $validated
        );

        return back()->with('success', 'Rekening berhasil disimpan!');
    }
}
