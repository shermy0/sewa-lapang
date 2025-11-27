<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use App\Models\Kategori;
use App\Models\JadwalLapangan;
use App\Models\Pemesanan;
use Illuminate\Support\Collection;
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
        $pemilikId = $petugas->pemilik_id;

        // Ambil semua lapangan milik pemilik petugas
        $lapangan = Lapangan::where('pemilik_id', $pemilikId)
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

            $primaryPhoto = '';
            if (! empty($fotoArray)) {
                $primaryPhoto = $fotoArray[0];
            } elseif (is_string($l->foto)) {
                $primaryPhoto = $l->foto;
            }

            return [
                'id' => $l->id,
                'nama' => $l->nama_lapangan,
                'pemilik_id' => $l->pemilik_id,
                'kategori_id' => $l->id_kategori,
                'kategori' => $l->kategori->nama_kategori ?? '',
                'foto' => $primaryPhoto,
                'harga' => $harga ? (int) $harga : 0,
                'durasi' => 'Per jam',
                'lokasi' => $l->lokasi ?? '',
                'status' => $l->status ?? 'available',
            ];
        });

        // Data antrean per section
        $sectionQueues = $this->buildSectionQueues($lapangan->pluck('id'));

        return view('petugas.queue', [
            'kategori' => $kategori,
            'lapangan' => $lapanganData,
            'petugasName' => $petugasName,
            'sectionQueues' => $sectionQueues,
            'pemilikId' => $pemilikId,
        ]);
    }

    /**
     * Ambil antrean pemesanan per section untuk lapangan milik pemilik ini.
     */
    private function buildSectionQueues(Collection $lapanganIds): Collection
    {
        if ($lapanganIds->isEmpty()) {
            return collect();
        }

        $today = Carbon::today()->toDateString();
        $nowTime = Carbon::now()->format('H:i:s');

        $rows = DB::table('pemesanan as p')
            ->join('jadwal_lapangan as j', 'p.jadwal_id', '=', 'j.id')
            ->join('section_lapangan as s', 'j.section_id', '=', 's.id')
            ->join('lapangan as l', 's.lapangan_id', '=', 'l.id')
            ->join('users as u', 'p.penyewa_id', '=', 'u.id')
            ->join('kategori as k', 'l.id_kategori', '=', 'k.id')
            ->whereIn('l.id', $lapanganIds)
            ->whereIn('p.status', ['menunggu', 'dibayar'])
            ->where(function ($q) use ($today, $nowTime) {
                $q->whereDate('j.tanggal', '>', $today)
                  ->orWhere(function ($q2) use ($today, $nowTime) {
                      $q2->whereDate('j.tanggal', $today)
                         ->where('j.jam_selesai', '>', $nowTime);
                  });
            })
            ->select([
                'p.id as pemesanan_id',
                'p.kode_tiket',
                'p.status',
                'p.status_scan',
                'p.waktu_scan',
                'u.name as penyewa',
                'j.tanggal',
                'j.jam_mulai',
                'j.jam_selesai',
                'j.harga_sewa',
                's.id as section_id',
                's.nama_section',
                'l.nama_lapangan',
                'l.lokasi',
                'k.nama_kategori',
            ])
            ->orderBy('j.tanggal')
            ->orderBy('j.jam_mulai')
            ->get();

        return $rows
            ->groupBy('section_id')
            ->map(function ($items) {
                $first = $items->first();

                return [
                    'label' => $first->nama_section ?? 'Section',
                    'lapangan' => $first->nama_lapangan ?? '-',
                    'queue' => $items->map(function ($row) {
                        return [
                            'penyewa' => $row->penyewa,
                            'tanggal' => Carbon::parse($row->tanggal)->format('d M Y'),
                            'jam_mulai' => substr($row->jam_mulai, 0, 5),
                            'jam_selesai' => substr($row->jam_selesai, 0, 5),
                            'status' => $row->status,
                            'status_scan' => $row->status_scan,
                            'kode_tiket' => $row->kode_tiket,
                            'lokasi' => $row->lokasi,
                            'kategori' => $row->nama_kategori,
                            'harga_sewa' => $row->harga_sewa,
                            'nama_lapangan' => $row->nama_lapangan,
                            'nama_section' => $row->nama_section,
                        ];
                    }),
                ];
            });
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
