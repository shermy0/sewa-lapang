<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lapangan;
use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Http\Request;

class PemesananController extends Controller
{
    public function index(Request $request)
    {
        $query = Pemesanan::with(['penyewa', 'lapangan'])->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $pemesanan = $query->paginate(10)->appends($request->query());
        $penyewa = User::where('role', 'penyewa')->pluck('name', 'id');
        $lapangan = Lapangan::pluck('nama_lapangan', 'id');

        return view('admin.pemesanan.index', compact('pemesanan', 'penyewa', 'lapangan'));
    }

    public function update(Request $request, Pemesanan $pemesanan)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:menunggu,dibayar,selesai,batal'],
        ]);

        $pemesanan->update($validated);

        return redirect()
            ->route('admin.pemesanan.index')
            ->with('success', 'Status pemesanan berhasil diperbarui.');
    }
}

