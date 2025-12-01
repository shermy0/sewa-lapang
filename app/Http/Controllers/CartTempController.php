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
        return DB::table('cart_temp')
            ->where('user_id', Auth::id())
            ->orderBy('id','desc')
            ->get();
    }

    // Tambah item otomatis saat user klik "Pesan"
    public function store(Request $r)
    {
        $id = DB::table('cart_temp')->insertGetId([
            'user_id'       => Auth::id(),
            'nama_penyewa'  => $r->nama_penyewa, // boleh null
            'lapangan_id'   => $r->lapangan_id,
            'lapangan_name' => $r->lapangan_name ?? $r->nama,
            'qty'           => 1,
            'harga'         => $r->harga,
            'tanggal'       => $r->tanggal,
            'jam_mulai'     => $r->jam_mulai,
            'jadwal_id'     => $r->jadwal_id,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json(
            DB::table('cart_temp')->find($id)
        );
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
