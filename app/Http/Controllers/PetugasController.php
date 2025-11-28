<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use App\Models\Kategori;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use App\Models\User; 
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
                'foto' => $fotoArray, // Pass array for carousel
                'hargaRataRata' => $harga ? (int) $harga : 0,
                'durasi' => 'Per jam',
                'lokasi' => $l->lokasi ?? '',
                'status' => $l->status ?? 'available',
                'deskripsi' => $l->deskripsi ?? '',
                'kategori_nama' => $l->kategori->nama_kategori ?? '',
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

<<<<<<< HEAD
    public function store(Request $request)
    {
        try {
            $data = $request->all(); 
            foreach($data['items'] as $item){ // Changed from $data['cart'] to $data['items'] based on JS
                // Logic simpan transaksi (sesuaikan dengan tabel Anda)
                // Disini saya asumsikan ada tabel pemesanan atau transaksi
                // ...
                
                // Update jadwal jadi booked/dibayar
                DB::table('jadwal_lapangan') // Sesuaikan nama tabel
                    ->where('lapangan_id', $item['id'])
                    ->where('tanggal', $item['tanggal'])
                    ->where('jam_mulai', $item['jam_mulai'])
                    ->update(['booking_status' => 'dibayar']);
            }
=======
    public function storeCash(Request $request)
{
    $request->validate([
        'penyewa_id' => 'required|integer',
        'total' => 'required|numeric',
        'items' => 'required|array|min:1',
        'kasir' => 'required|string',
    ]);

    try {
        $data = $request->all();
>>>>>>> 8199335fcb8fe5374666589d32bfc8f28c10279c

        $pemesanan = Pemesanan::create([
            'kasir' => $data['kasir'],
            'penyewa_id' => $data['penyewa_id'],
            'metode' => 'cash',
            'total' => $data['total']
        ]);

        foreach($data['items'] as $item){
            PemesananItem::create([
                'pemesanan_id' => $pemesanan->id,
                'lapangan_id' => $item['id'],
                'jadwal_id' => $item['jadwal_id'],
                'harga' => $item['harga'],
                'durasi' => $item['durasi']
            ]);
        }

        return response()->json(['success'=>true]);

    } catch(\Exception $e){
        \Log::error($e->getMessage());
        return response()->json(['success'=>false,'message'=>$e->getMessage()]);
    }
}

public function storeMidtrans(Request $request)
{
    // Kirim ke Midtrans (payment gateway) dulu
    // Contoh: generate Snap token, redirect, dll
    $request->validate([
        'penyewa_id' => 'required|integer',
        'total' => 'required|numeric',
        'items' => 'required|array|min:1',
        'kasir' => 'required|string',
    ]);

    // TODO: Integrasi Midtrans
    return response()->json([
        'success' => true,
        'redirect_url' => '/midtrans/payment-page' // misal nanti redirect ke page midtrans
    ]);
}


    public function getJadwalLapangan($lapanganId)
    {
        $sectionIds = DB::table('section_lapangan')
            ->where('lapangan_id', $lapanganId)
            ->pluck('id');

        $now = Carbon::now('Asia/Jakarta');

        // Hapus jadwal lewat
        DB::table('jadwal_lapangan')
            ->whereIn('section_id', $sectionIds)
            ->where('tanggal', '<', $now->toDateString())
            ->delete();

        $jadwal = DB::table('jadwal_lapangan')
            ->whereIn('section_id', $sectionIds)
            ->where('tanggal', '>=', $now->toDateString())
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        // Format data sesuai JS
        $jadwalFormatted = $jadwal->map(function($j){
            return [
                'jam_mulai' => $j->jam_mulai,
                'jam_selesai' => $j->jam_selesai,
                'tanggal' => $j->tanggal,
                'harga_sewa' => $j->harga_sewa ?? 0,
                'booking_status' => $j->booking_status ?? 'tersedia', // default 'tersedia' jika null
            ];
        });

        return response()->json($jadwalFormatted);
    }

    public function display()
    {
        $petugas = Auth::user();
        $pemilikId = $petugas->pemilik_id;

        $lapangan = Lapangan::where('pemilik_id', $pemilikId)
            ->with('kategori')
            ->get();

        $sectionQueues = $this->buildSectionQueues($lapangan->pluck('id'));

        $carouselImages = collect();
        foreach ($lapangan as $l) {
            if (is_array($l->foto)) {
                foreach ($l->foto as $f) $carouselImages->push($f);
            } elseif (is_string($l->foto) && !empty($l->foto)) {
                $carouselImages->push($l->foto);
            }
        }
        
        return view('petugas.display', [
            'sectionQueues' => $sectionQueues,
            'carouselImages' => $carouselImages,
            'petugasName' => $petugas->name,
        ]);
    }

    public function penyewa()
    {
        $petugas = auth()->user();
    
        // Ambil penyewa milik pemilik dari petugas
        $penyewa = User::where('role', 'penyewa')
                       ->where('pemilik_id', $petugas->pemilik_id)
                       ->orderBy('created_at', 'desc')
                       ->get();
    
        return view('petugas.penyewa', compact('penyewa'));
    }    

    public function storePenyewa(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
        ]);
    
        $petugas = auth()->user(); // petugas yang login
    
        User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => bcrypt('12345678'),
            'role'        => 'penyewa',
            'pemilik_id'  => $petugas->pemilik_id,   // <-- INI KUNCI!
            'status'      => 'aktif',
        ]);
    
        return back()->with('success', 'Penyewa berhasil ditambahkan');
    }    

    public function destroyPenyewa($id)
    {
        $user = User::findOrFail($id);

        // Pastikan hanya role 'penyewa' yang bisa dihapus
        if($user->role !== 'penyewa') {
            return redirect()->route('petugas.penyewa')->with('error', 'Hanya penyewa yang bisa dihapus.');
        }

        $user->delete();

        return redirect()->route('petugas.penyewa')->with('success', 'Penyewa berhasil dihapus.');
    }

    public function searchPenyewa(Request $request)
    {
        $keyword = $request->q ?? ''; // ambil query param 'q' dari JS
        $petugas = auth()->user();
    
        $data = User::where('role', 'penyewa')
            ->where('pemilik_id', $petugas->pemilik_id)
            ->when($keyword, function($query, $keyword){
                return $query->where('name', 'LIKE', "%$keyword%");
            })
            ->get();
    
        return response()->json($data);
    }    
}