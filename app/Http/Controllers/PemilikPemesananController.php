<?php

namespace App\Http\Controllers;

use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PemilikPemesananController extends Controller
{
    /**
     * Tampilkan daftar pemesanan untuk pemilik lapangan.
     */
    public function index(Request $request)
    {
        $pemilikId = Auth::id();
        $status = $request->input('status');
        $search = $request->input('search');

        $pemesananQuery = Pemesanan::with([
                'lapangan:id,nama_lapangan,pemilik_id',
                'penyewa:id,name,email,no_hp',
                'jadwal.section:id,lapangan_id,nama_section',
                'pembayaran:id,pemesanan_id,status,jumlah',
            ])
            ->whereHas('lapangan', function ($query) use ($pemilikId) {
                $query->where('pemilik_id', $pemilikId);
            });

        if ($status) {
            $pemesananQuery->where('status', $status);
        }

        if ($search) {
            $pemesananQuery->where(function ($query) use ($search) {
                $query->where('kode_tiket', 'like', "%{$search}%")
                    ->orWhereHas('lapangan', function ($lapanganQuery) use ($search) {
                        $lapanganQuery->where('nama_lapangan', 'like', "%{$search}%");
                    })
                    ->orWhereHas('penyewa', function ($penyewaQuery) use ($search) {
                        $penyewaQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $pemesanan = $pemesananQuery
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $statusOptions = [
            'menunggu' => 'Menunggu',
            'dibayar' => 'Dibayar',
            'selesai' => 'Selesai',
            'batal' => 'Batal',
        ];

        return view('pemilik.pemesanan', [
            'pemesanan' => $pemesanan,
            'statusOptions' => $statusOptions,
            'selectedStatus' => $status,
            'searchTerm' => $search,
        ]);
    }
}