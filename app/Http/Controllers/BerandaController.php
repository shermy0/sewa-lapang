<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BerandaController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('search');
        $kategori = $request->input('kategori');

        // Ambil semua nilai enum kategori langsung dari struktur tabel
        $enumQuery = DB::select("SHOW COLUMNS FROM lapangan LIKE 'kategori'");
        preg_match("/^enum\('(.*)'\)$/", $enumQuery[0]->Type, $matches);
        $kategoris = explode("','", $matches[1]);

        // Query data lapangan
        $lapangan = DB::table('lapangan')
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('nama_lapangan', 'like', "%{$keyword}%")
                      ->orWhere('lokasi', 'like', "%{$keyword}%");
            })
            ->when($kategori && $kategori !== 'all', function ($query) use ($kategori) {
                $query->where('kategori', $kategori);
            })
            ->get();

        return view('penyewa.beranda', compact('lapangan', 'keyword', 'kategori', 'kategoris'));
    }
    
    public function detail($id)
    {
        $lapangan = \DB::table('lapangan')->where('id', $id)->first();
        $lainnya = \DB::table('lapangan')
            ->where('id', '!=', $id)
            ->limit(4)
            ->get();

        return view('penyewa.detail', compact('lapangan', 'lainnya'));
    }     
}