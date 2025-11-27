<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use App\Models\Kategori;
use Illuminate\Http\Request;
use App\Models\User; 
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
            $data = $request->all(); // Pastikan data dari JS masuk
            // contoh: $cart = $data['cart'];
            // Simpan transaksi
            foreach($data['cart'] as $item){
                DB::table('transaksi')->insert([
                    'lapangan_id' => $item['id'],
                    'petugas_name' => auth()->user()->name,
                    'harga' => $item['harga'],
                    'jam_mulai' => $item['jam_mulai'],
                    'tanggal' => $item['tanggal'],
                    'durasi' => $item['durasi'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // update jadwal jadi booked
                DB::table('jadwal')
                    ->where('lapangan_id', $item['id'])
                    ->where('tanggal', $item['tanggal'])
                    ->where('jam_mulai', $item['jam_mulai'])
                    ->update(['booking_status' => 'dibayar']);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e){
            \Log::error($e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
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

    public function penyewa()
    {
        $penyewa = User::where('role', 'penyewa')->orderBy('created_at', 'desc')->get();
        return view('petugas.penyewa', compact('penyewa'));
    }

    // Menyimpan penyewa baru
    public function storePenyewa(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:6'
        ]);

        $password = $request->password ?? 'password123';

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'role' => 'penyewa'
        ]);

        return redirect()->route('petugas.penyewa')->with('success', 'Penyewa berhasil ditambahkan!');
    }
}