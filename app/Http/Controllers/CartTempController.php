<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CartTempController extends Controller
{
    // Ambil semua cart berdasarkan pemilik
    public function index()
    {
        $pemilikId = auth()->user()->pemilik_id;

        $carts = DB::table('cart_temp')
            ->where('pemilik_id', $pemilikId)
            ->get();
    
        return response()->json($carts);
    }   

    // Tambah item otomatis saat user klik "Pesan"
    public function store(Request $request)
    {
        $pemilikId = auth()->user()->pemilik_id;

        if (!$pemilikId) {
            return response()->json(['success' => false, 'message' => 'User tidak punya pemilik_id'], 400);
        }

        // Validasi minimal
        $validated = $request->validate([
            'lapangan_id' => 'required|integer',
            'lapangan_name' => 'required|string',
            'harga' => 'required|numeric',
            'nama_penyewa' => 'nullable|string',
            'jam_mulai' => 'nullable|string',
            'tanggal' => 'nullable|date',
            'jadwal_id' => 'nullable|integer',
        ]);

        // Data untuk insert
        $data = [
            'pemilik_id' => $pemilikId,
            'lapangan_id' => $validated['lapangan_id'],
            'lapangan_name' => $validated['lapangan_name'],
            'harga' => $validated['harga'],
            'qty' => 1,
            'nama_penyewa' => $validated['nama_penyewa'] ?? null,
            'jadwal_id' => $validated['jadwal_id'] ?? null,
            'jam_mulai' => $validated['jam_mulai'] ?? null,
            'tanggal' => $validated['tanggal'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        try {
            // Insert ke DB
            $cartId = DB::table('cart_temp')->insertGetId($data);

            // Ambil data yang baru masuk
            $cartData = DB::table('cart_temp')->where('id', $cartId)->first();

            return response()->json(['success' => true, 'cart' => $cartData]);

        } catch (\Exception $e) {
            // Kalau gagal, kasih tahu error
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Hapus item cart
    public function destroy($id)
    {
        $pemilikId = auth()->user()->pemilik_id;

        DB::table('cart_temp')
            ->where('id', $id)
            ->where('pemilik_id', $pemilikId)
            ->delete();

        return response()->json(['success' => true]);
    }

    // Clear semua cart pemilik (dipanggil setelah bayar)
    public function clear()
    {
        $pemilikId = auth()->user()->pemilik_id;

        DB::table('cart_temp')
            ->where('pemilik_id', $pemilikId)
            ->delete();

        return response()->json(['success' => true]);
    }

    // Update nama penyewa
    public function updateNama(Request $r)
    {
        $pemilikId = auth()->user()->pemilik_id;

        DB::table('cart_temp')
            ->where('pemilik_id', $pemilikId)
            ->update(['nama_penyewa' => $r->nama_penyewa]);

        return response()->json(['success' => true]);
    }
}