<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembayaran::with('pemesanan.penyewa', 'pemesanan.lapangan')->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $pembayaran = $query->paginate(10)->appends($request->query());

        return view('admin.pembayaran.index', compact('pembayaran'));
    }

    public function update(Request $request, Pembayaran $pembayaran)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,berhasil,gagal'],
        ]);

        $pembayaran->update($validated);

        return redirect()
            ->route('admin.pembayaran.index')
            ->with('success', 'Status pembayaran berhasil diperbarui.');
    }
}

