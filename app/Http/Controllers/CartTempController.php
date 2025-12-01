<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CartTempController extends Controller
{
    // Ambil semua cart berdasarkan user login
    public function index()
    {
        $carts = DB::table('cart_temp')
            ->where('user_id', auth()->id())
            ->get();
    
        return response()->json($carts);
    }    

    // Tambah item otomatis saat user klik "Pesan"
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lapangan_id' => 'required|integer',
            'lapangan_name' => 'required|string',
            'harga' => 'required|numeric',
            'nama_penyewa' => 'nullable|string',
        ]);

        // Tambahkan user_id
        $validated['user_id'] = auth()->id(); // ambil user yang login
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        $cart = DB::table('cart_temp')->insertGetId($validated);

        $cartData = DB::table('cart_temp')->where('id', $cart)->first();

        return response()->json([
            'success' => true,
            'cart' => $cartData
        ]);
    }

    // Hapus item cart
    public function destroy($id)
    {
        DB::table('cart_temp')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json(['success' => true]);
    }

    // Clear semua cart user (dipanggil setelah bayar)
    public function clear()
    {
        DB::table('cart_temp')
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json(['success' => true]);
    }

    // Update nama penyewa
    public function updateNama(Request $r)
    {
        DB::table('cart_temp')
            ->where('user_id', Auth::id())
            ->update(['nama_penyewa' => $r->nama_penyewa]);

        return response()->json(['success' => true]);
    }
}
