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
            'jadwal_id'   => 'required|integer',
            'penyewa_id'  => 'required|integer|exists:users,id',
        ]);

        $existing = Pemesanan::where('jadwal_id', $validated['jadwal_id'])
            ->whereIn('status', ['keranjang', 'menunggu', 'dibayar'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'error' => 'Jadwal tidak tersedia'
            ], 400);
        }

        // 🔥 AMBIL DARI SESSION
        $namaKomunitas = session('cart.nama_komunitas');

        $data = [
            'penyewa_id'     => $validated['penyewa_id'],
            'lapangan_id'    => $validated['lapangan_id'],
            'jadwal_id'      => $validated['jadwal_id'],
            'nama_komunitas' => $namaKomunitas, // 👈 PENTING
            'status'         => 'keranjang',
            'created_at'     => now(),
            'updated_at'     => now(),
        ];

        $cart = Pemesanan::create($data);

        return response()->json([
            'success' => true,
            'cart' => $cart->load('lapangan', 'jadwal')
        ]);
    }

    // Hapus 1 item
    public function destroy($id)
    {
        Pemesanan::where('id', $id)
            ->where('status', 'keranjang')
            ->delete();
    
        return response()->json(['success' => true]);
    }    

    // Hapus semua item keranjang user
    public function clear()
    {
        Pemesanan::where('status', 'keranjang')->delete();
    
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
    
    public function updateNamaPenyewa(Request $request)
    {
        $request->validate([
            'nama_penyewa' => 'nullable|string|max:255',
        ]);
    
        // Ambil penyewa aktif yang dipilih dari FE
        $nama = $request->nama_penyewa;
    
        // Cari user berdasarkan nama (FE kamu kirim name)
        $penyewa = \App\Models\User::where('name', $nama)->first();
    
        // Update cart-temp (status = keranjang)
        Pemesanan::where('status', 'keranjang')->update([
            'nama_komunitas' => $nama,
            'penyewa_id'     => $penyewa ? $penyewa->id : null,
            'updated_at'     => now()
        ]);
    
        return response()->json([
            'success' => true,
            'message' => 'Nama penyewa & penyewa_id berhasil disinkronkan'
        ]);
    } 
    
    public function setKomunitas(Request $request)
    {
        $request->validate([
            'nama_komunitas' => 'nullable|string|max:255'
        ]);

        session([
            'cart.nama_komunitas' => $request->nama_komunitas
        ]);

        return response()->json([
            'success' => true,
            'nama_komunitas' => $request->nama_komunitas
        ]);
    }

    public function syncKomunitasToCart()
    {
        $namaKomunitas = session('cart.nama_komunitas');

        Pemesanan::where('status', 'keranjang')
            ->update([
                'nama_komunitas' => $namaKomunitas,
                'updated_at' => now()
            ]);

        return response()->json(['success' => true]);
    }
}