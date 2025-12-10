<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pemesanan; 
use App\Models\Lapangan;
use App\Models\JadwalLapangan;

class CartTempController extends Controller
{
    // Ambil semua item dengan status keranjang
    public function index()
    {
        $cart = Pemesanan::with(['lapangan', 'jadwal'])
            ->where('status', 'keranjang')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'lapangan_id' => $item->lapangan_id,
                    'jadwal_id' => $item->jadwal_id,
                    'lapangan_name' => $item->lapangan->nama_lapangan ?? 'Lapangan',
                    'harga' => $item->jadwal->harga_sewa ?? 0, // ambil harga dari jadwal
                    'lokasi' => $item->lapangan->lokasi ?? '-',
                    'jam_mulai' => $item->jadwal->jam_mulai ?? '-',
                    'jam_selesai' => $item->jadwal->jam_selesai ?? '-',
                    'tanggal' => $item->jadwal->tanggal ?? '-',
                    'durasi' => $item->durasi_sewa ?? 1,
                    'status' => $item->status,
                    'kode_tiket' => $item->kode_tiket,
                ];
            });
    
        return response()->json($cart);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lapangan_id' => 'required|integer',
            'jadwal_id' => 'required|integer',
            'penyewa_id' => 'required|integer|exists:users,id',
        ]);

        // Cek apakah jadwal sudah ada di keranjang atau sudah dibooking
        $existing = Pemesanan::where('jadwal_id', $validated['jadwal_id'])
            ->whereIn('status', ['keranjang', 'menunggu', 'dibayar'])
            ->first();

        if ($existing) {
            $statusMessage = match($existing->status) {
                'keranjang' => 'Jadwal sudah ada di keranjang',
                'menunggu' => 'Jadwal sedang dalam proses pembayaran',
                'dibayar' => 'Jadwal sudah dibooking',
                default => 'Jadwal tidak tersedia'
            };
            
            return response()->json([
                'success' => false,
                'error' => $statusMessage
            ], 400);
        }

        $data = [
            'penyewa_id' => $validated['penyewa_id'],
            'lapangan_id' => $validated['lapangan_id'],
            'jadwal_id' => $validated['jadwal_id'],
            'status' => 'keranjang',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        try {
            $cart = Pemesanan::create($data);

            return response()->json([
                'success' => true,
                'cart' => $cart->load('lapangan', 'jadwal')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Hapus 1 item
    public function destroy($id)
    {
        $userId = auth()->id();

        DB::table('pemesanan')
            ->where('id', $id)
            ->where('penyewa_id', $userId)
            ->where('status', 'keranjang')
            ->delete();

        return response()->json(['success' => true]);
    }

    // Hapus semua item keranjang user
    public function clear()
    {
        $userId = auth()->id();

        DB::table('pemesanan')
            ->where('penyewa_id', $userId)
            ->where('status', 'keranjang')
            ->delete();

        return response()->json(['success' => true]);
    }

    // Update nama komunitas (jika dipakai)
    public function updateNama(Request $request)
    {
        $request->validate([
            'penyewa_id' => 'required|exists:users,id',
            'nama_komunitas' => 'nullable|string|max:255'
        ]);
    
        // Ambil semua pemesanan 'keranjang' milik petugas ini
        CartTemp::where('penyewa_id', auth()->id()) // awalnya id kasir/petugas
                ->where('status', 'keranjang')
                ->update([
                    'penyewa_id' => $request->penyewa_id,
                    'nama_komunitas' => $request->nama_komunitas,
                ]);
    
        return response()->json(['success' => true]);
    }       
}