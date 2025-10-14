<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PemilikPembayaranController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->status ?? 'semua';
        $search = $request->search ?? '';
        $tanggal_mulai = $request->tanggal_mulai;
        $tanggal_selesai = $request->tanggal_selesai;

        // Query dasar
        $query = DB::table('pembayaran');

        // 🔍 Filter status
        if ($status !== 'semua') {
            $query->where('status', $status);
        }

        // 🔍 Filter pencarian (id / metode / order_id)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('pemesanan_id', 'like', "%{$search}%")
                  ->orWhere('metode', 'like', "%{$search}%")
                  ->orWhere('order_id', 'like', "%{$search}%");
            });
        }

        // 📅 Filter tanggal
        if ($tanggal_mulai && $tanggal_selesai) {
            $query->whereBetween('tanggal_pembayaran', [$tanggal_mulai, $tanggal_selesai]);
        }

        // 🔽 Ambil hasilnya
        $pembayaran = $query->orderBy('tanggal_pembayaran', 'desc')->get();

        // 📊 Statistik
        $stat = [
            'total' => DB::table('pembayaran')->count(),
            'berhasil' => DB::table('pembayaran')->where('status', 'berhasil')->count(),
            'pending' => DB::table('pembayaran')->where('status', 'pending')->count(),
            'gagal' => DB::table('pembayaran')->where('status', 'gagal')->count(),
            'total_uang' => DB::table('pembayaran')->where('status', 'berhasil')->sum('jumlah'),
        ];

        return view('pemilikpembayaran.index', compact(
            'pembayaran',
            'status',
            'search',
            'tanggal_mulai',
            'tanggal_selesai',
            'stat'
        ));
    }
}
