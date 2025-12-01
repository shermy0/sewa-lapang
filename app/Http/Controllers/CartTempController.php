<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartTemp;
use Illuminate\Support\Facades\Auth;

class CartTempController extends Controller
{
    // Ambil semua cart user login
    public function index()
    {
        return DB::table('cart_temp')->orderBy('id','desc')->get();
    }    

    // Tambah item ke cart
    public function store(Request $r)
    {
        $item = DB::table('cart_temp')->insertGetId([
            'lapangan_id'   => $r->lapangan_id,
            'lapangan_name' => $r->lapangan_name ?? $r->nama,
            'harga'         => $r->harga,
            'durasi'        => $r->durasi ?? 1,
            'tanggal'       => $r->tanggal,
            'jam_mulai'     => $r->jam_mulai,
            'jadwal_id'     => $r->jadwal_id,
            'nama_penyewa'  => $r->nama_penyewa,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json(
            DB::table('cart_temp')->where('id', $item)->first()
        );
    }

    // Hapus item
    public function destroy($id)
    {
        DB::table('cart_temp')->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }    

    // Hapus semua (misal setelah bayar)
    public function clear()
    {
        CartTemp::where('user_id', Auth::id())->delete();
        return response()->json(['success' => true]);
    }

    public function updateNama(Request $r)
    {
        DB::table('cart_temp')->update(['nama_penyewa' => $r->nama_penyewa]);
        return response()->json(['success' => true]);
    }

}
