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
                $jadwal = $item->jadwal;
                
                // Format tanggal ke format Indonesia
                $tanggalFormatted = '-';
                if ($jadwal && $jadwal->tanggal) {
                    $tanggalFormatted = \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('d M Y');
                }
                
                // Format waktu tanpa detik
                $jamMulai = '-';
                $jamSelesai = '-';
                if ($jadwal) {
                    $jamMulai = $jadwal->jam_mulai ? substr($jadwal->jam_mulai, 0, 5) : '-';
                    $jamSelesai = $jadwal->jam_selesai ? substr($jadwal->jam_selesai, 0, 5) : '-';
                }
                
                return [
                    'id' => $item->id,
                    'lapangan_id' => $item->lapangan_id,
                    'jadwal_id' => $item->jadwal_id,
                    'lapangan_name' => $item->lapangan->nama_lapangan ?? 'Lapangan',
                    'harga' => $jadwal->harga_sewa ?? 0,
                    'lokasi' => $item->lapangan->lokasi ?? '-',
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'tanggal' => $tanggalFormatted,
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
            'penyewa_id' => auth()->id(), // user login
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
        $pemilikId = auth()->user()->pemilik_id;

        DB::table('pemesanan')
            ->where('penyewa_id', $pemilikId)
            ->where('status', 'keranjang')
            ->update(['nama_komunitas' => $request->nama_komunitas]);

        return response()->json(['success' => true]);
    }
}