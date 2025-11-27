<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PetugasController extends Controller
{
    public function index()
    {
        $petugas = Auth::user();
        $petugasName = $petugas->name;

        // Ambil semua lapangan milik pemilik petugas
        $lapangan = Lapangan::where('pemilik_id', $petugas->pemilik_id)
            ->with('kategori')
            ->get();

        // Ambil kategori hanya yang punya lapangan milik petugas
        $kategori = Kategori::whereIn('id', $lapangan->pluck('id_kategori')->unique())
            ->orderBy('nama_kategori')
            ->get();

        // Map lapangan supaya JS bisa pakai
        $lapanganData = $lapangan->map(function ($l) {
            // Foto array
            if (is_array($l->foto)) {
                $fotoArray = $l->foto;
            } else {
                $fotoArray = $l->foto ? json_decode($l->foto, true) : [];
                if (!is_array($fotoArray)) $fotoArray = [];
            }

            // Ambil semua section_id untuk lapangan ini
            $sectionIds = DB::table('section_lapangan')
                ->where('lapangan_id', $l->id)
                ->pluck('id');

            // Harga rata-rata dari jadwal_lapangan
            $harga = DB::table('jadwal_lapangan')
                ->whereIn('section_id', $sectionIds)
                ->avg('harga_sewa');

            $hargaFormatted = $harga ? number_format($harga, 0, ',', '.') : '-';

            return [
                'id' => $l->id,
                'nama' => $l->nama_lapangan,
                'deskripsi' => $l->deskripsi ?? '',
                'id_kategori' => $l->id_kategori,
                'kategori_nama' => $l->kategori->nama_kategori ?? '',
                'foto' => $fotoArray,
                'hargaRataRata' => $hargaFormatted,
            ];                
        });

        return view('petugas.index', [
            'kategori' => $kategori,
            'lapangan' => $lapanganData,
            'petugasName' => $petugasName,
        ]);
    }

    public function create()
    {
        $kategori = Kategori::orderBy('nama_kategori')->get();
        return view('petugas.create', compact('kategori'));
    }

    public function store(Request $request)
    {
        try {
            $transaksi = Transaksi::create([
                'kasir' => $request->kasir,
                'metode' => $request->metode,
                'total' => $request->total,
            ]);

            foreach($request->items as $item){
                // Simpan item transaksi
                TransaksiItem::create([
                    'transaksi_id' => $transaksi->id,
                    'lapangan_id' => $item['id'],
                    'nama_lapangan' => $item['nama'],
                    'harga' => $item['harga'],
                    'jam_mulai' => $item['jam_mulai'],
                    'tanggal' => $item['tanggal'],
                    'durasi' => $item['durasi'],
                ]);

                // Update jadwal supaya slot hilang
                DB::table('jadwal')
                    ->where('lapangan_id', $item['id'])
                    ->where('tanggal', $item['tanggal'])
                    ->where('jam_mulai', $item['jam_mulai'])
                    ->update(['booking_status'=>'dibayar']);
            }

            return response()->json(['success'=>true]);
        } catch (\Exception $e) {
            return response()->json(['success'=>false,'error'=>$e->getMessage()]);
        }
    }

    public function getJadwalLapangan($lapanganId)
    {
        // Ambil semua section id untuk lapangan ini
        $sectionIds = DB::table('section_lapangan')
            ->where('lapangan_id', $lapanganId)
            ->pluck('id');

        $now = Carbon::now();

        // Hapus jadwal yang sudah lewat
        DB::table('jadwal_lapangan')
            ->whereIn('section_id', $sectionIds)
            ->where('tanggal', '<', $now->toDateString())
            ->delete();

        // Ambil jadwal tersisa
        $jadwal = DB::table('jadwal_lapangan')
            ->whereIn('section_id', $sectionIds)
            ->where('tanggal', '>=', $now->toDateString())
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        return response()->json($jadwal);
    }
}