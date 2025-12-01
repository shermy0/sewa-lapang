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
        $cart = CartTemp::where('user_id', Auth::id())->get();
        return response()->json($cart);
    }

    // Tambah item ke cart
    public function store(Request $request)
    {
        $item = CartTemp::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'lapangan_id' => $request->lapangan_id
            ],
            [
                'nama_penyewa' => $request->nama_penyewa,
                'lapangan_name' => $request->lapangan_name,
                'qty' => $request->qty,
                'harga' => $request->harga
            ]
        );

        return response()->json($item);
    }

    // Hapus item
    public function destroy($id)
    {
        $item = CartTemp::where('user_id', Auth::id())->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }

    // Hapus semua (misal setelah bayar)
    public function clear()
    {
        CartTemp::where('user_id', Auth::id())->delete();
        return response()->json(['success' => true]);
    }
}
