<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BerandaController extends Controller
{
    /**
     * Halaman utama penyewa (beranda)
     */
    public function index(Request $request)
    {
        $keyword = $request->input('search');
        $kategori = $request->input('kategori');
        $kategoris = [];

        try {
            // 1) Coba ambil info kolom via SHOW COLUMNS untuk kategori (tipe enum)
            $enumQuery = DB::select("SHOW COLUMNS FROM lapangan LIKE 'kategori'");

            if (!empty($enumQuery) && isset($enumQuery[0]->Type)) {
                $type = $enumQuery[0]->Type;

                // Ambil hanya bagian enum('a','b',...) tanpa COLLATE
                if (stripos($type, 'enum(') !== false) {
                    $start = stripos($type, 'enum(');
                    $posClose = strpos($type, ')', $start);
                    $typeEnumOnly = $posClose !== false
                        ? substr($type, $start, $posClose - $start + 1)
                        : $type;
                } else {
                    $typeEnumOnly = $type;
                }

                // Ambil nilai-nilai enum di dalam tanda kurung
                if (preg_match("/^enum\((.*)\)$/i", $typeEnumOnly, $matches)) {
                    $raw = trim($matches[1]);
                    $raw = str_replace("'", "", $raw);
                    $kategoris = array_map('trim', explode(',', $raw));
                } else {
                    Log::warning('BerandaController:index - Gagal parse enum kategori', [
                        'TypeRaw' => $type,
                    ]);
                }
            } else {
                Log::warning('BerandaController:index - SHOW COLUMNS kosong / tanpa properti Type', [
                    'enumQuery' => $enumQuery,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('BerandaController:index - Error ambil enum kategori', [
                'message' => $e->getMessage(),
            ]);
        }

        // 2) Fallback jika enum tidak ditemukan — ambil distinct kategori dari DB
        if (empty($kategoris)) {
            try {
                $distinct = DB::table('lapangan')
                    ->select('kategori')
                    ->whereNotNull('kategori')
                    ->distinct()
                    ->pluck('kategori')
                    ->filter()
                    ->values()
                    ->all();

                $kategoris = !empty($distinct) ? $distinct : [];
            } catch (\Throwable $e) {
                Log::error('BerandaController:index - fallback distinct kategori gagal', [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        // 3) Ambil data lapangan dengan filter pencarian dan kategori
        $lapangan = DB::table('lapangan')
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('nama_lapangan', 'like', "%{$keyword}%")
                      ->orWhere('lokasi', 'like', "%{$keyword}%");
            })
            ->when($kategori && $kategori !== 'all', function ($query) use ($kategori) {
                $query->where('kategori', $kategori);
            })
            ->get();

        // 4) Kirim data ke view
        return view('penyewa.beranda', compact('lapangan', 'keyword', 'kategori', 'kategoris'));
    }

    /**
     * Halaman detail lapangan (penyewa/detail/{id})
     */
    public function detail($id)
    {
        $lapangan = DB::table('lapangan')->where('id', $id)->first();

        if (!$lapangan) {
            abort(404, 'Data lapangan tidak ditemukan');
        }

        $lainnya = DB::table('lapangan')
            ->where('id', '!=', $id)
            ->limit(4)
            ->get();

        $isFavorit = false;

        if (Auth::check() && Auth::user()->role === 'penyewa') {
            $isFavorit = DB::table('favorit_lapangan')
                ->where('penyewa_id', Auth::id())
                ->where('lapangan_id', $lapangan->id)
                ->exists();
        }

        return view('penyewa.detail', [
            'lapangan' => $lapangan,
            'lainnya' => $lainnya,
            'isFavorit' => $isFavorit,
        ]);
    }
}
