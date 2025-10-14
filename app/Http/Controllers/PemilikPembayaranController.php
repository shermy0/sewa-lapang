<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class PemilikPembayaranController extends Controller
{
    public function index(Request $request)
    {
        // Data dummy sementara (simulasi dari database)
        $pembayaran = collect([
            [
                'id' => 1,
                'pemesanan_id' => 'PM001',
                'metode' => 'Transfer BCA',
                'jumlah' => 150000,
                'status' => 'berhasil',
                'order_id' => 'ORD-001',
                'payment_url' => 'https://example.com/payment/ORD-001',
                'tanggal_pembayaran' => '2025-10-12',
            ],
            [
                'id' => 2,
                'pemesanan_id' => 'PM002',
                'metode' => 'QRIS',
                'jumlah' => 100000,
                'status' => 'pending',
                'order_id' => 'ORD-002',
                'payment_url' => 'https://example.com/payment/ORD-002',
                'tanggal_pembayaran' => '2025-10-13',
            ],
            [
                'id' => 3,
                'pemesanan_id' => 'PM003',
                'metode' => 'Transfer Mandiri',
                'jumlah' => 200000,
                'status' => 'gagal',
                'order_id' => 'ORD-003',
                'payment_url' => 'https://example.com/payment/ORD-003',
                'tanggal_pembayaran' => '2025-10-14',
            ],
            [
                'id' => 4,
                'pemesanan_id' => 'PM004',
                'metode' => 'QRIS',
                'jumlah' => 250000,
                'status' => 'berhasil',
                'order_id' => 'ORD-004',
                'payment_url' => 'https://example.com/payment/ORD-004',
                'tanggal_pembayaran' => '2025-10-10',
            ],
        ]);

        // 🔎 FILTER FITUR
        $status = $request->status ?? 'semua';
        $search = strtolower($request->search ?? '');
        $tanggal_mulai = $request->tanggal_mulai;
        $tanggal_selesai = $request->tanggal_selesai;

        // Filter status
        if ($status !== 'semua') {
            $pembayaran = $pembayaran->where('status', $status);
        }

        // Filter pencarian
        if (!empty($search)) {
            $pembayaran = $pembayaran->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['pemesanan_id']), $search)
                    || str_contains(strtolower($item['order_id']), $search)
                    || str_contains(strtolower($item['metode']), $search);
            });
        }

        // Filter tanggal
        if ($tanggal_mulai && $tanggal_selesai) {
            $pembayaran = $pembayaran->filter(function ($item) use ($tanggal_mulai, $tanggal_selesai) {
                return $item['tanggal_pembayaran'] >= $tanggal_mulai &&
                       $item['tanggal_pembayaran'] <= $tanggal_selesai;
            });
        }

        // Statistik
        $stat = [
            'total' => $pembayaran->count(),
            'berhasil' => $pembayaran->where('status', 'berhasil')->count(),
            'pending' => $pembayaran->where('status', 'pending')->count(),
            'gagal' => $pembayaran->where('status', 'gagal')->count(),
            'total_uang' => $pembayaran->where('status', 'berhasil')->sum('jumlah'),
        ];

        return view('pemilikpembayaran.index', compact('pembayaran', 'status', 'search', 'tanggal_mulai', 'tanggal_selesai', 'stat'));
    }
}
