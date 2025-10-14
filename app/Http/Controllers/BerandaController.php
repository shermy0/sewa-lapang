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
        $kategoris = [];

        // Ambil semua nilai enum kategori langsung dari struktur tabel
        $enumQuery = DB::select("SHOW COLUMNS FROM lapangan LIKE 'kategori'");

        if (!empty($enumQuery) && isset($enumQuery[0]->Type)) {
            // Cocokkan pola enum('...') secara fleksibel
            if (preg_match("/^enum\((.*)\)$/i", $enumQuery[0]->Type, $matches)) {
                // Hilangkan tanda kutip tunggal dan spasi berlebih
                $raw = str_replace("'", "", $matches[1]);
                $kategoris = array_map('trim', explode(',', $raw));
            }
        }

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
        $lapangan = DB::table('lapangan')->where('id', $id)->first();

        $lainnya = DB::table('lapangan')
            ->where('id', '!=', $id)
            ->limit(4)
            ->get();

        return view('penyewa.detail', compact('lapangan', 'lainnya'));
    }
}
