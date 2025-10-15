<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use App\Models\JadwalLapangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LapanganController extends Controller
{
    /**
     * Menampilkan semua data lapangan.
     */
    public function index(Request $request)
    {
        $query = Lapangan::with('jadwal');

        // 🔍 Filter pencarian (nama atau lokasi)
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_lapangan', 'like', '%' . $request->search . '%')
                  ->orWhere('lokasi', 'like', '%' . $request->search . '%');
            });
        }

        // 🎯 Filter kategori
        if ($request->filled('kategori')) {
            $query->where('kategori', 'like', '%' . $request->kategori . '%');
        }

        // 🎯 Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 💰 Filter harga minimal dan maksimal
        if ($request->filled('min_harga')) {
            $query->where('harga_sewa', '>=', $request->min_harga);
        }
        if ($request->filled('max_harga')) {
            $query->where('harga_sewa', '<=', $request->max_harga);
        }

        // ⭐ Filter rating
        if ($request->filled('rating')) {
            $query->where('rating', '>=', $request->rating);
        }

        // 🔄 Urutkan berdasarkan harga
        if ($request->filled('sort_harga')) {
            $query->orderBy('harga_sewa', $request->sort_harga);
        } else {
            $query->latest();
        }

        // ✅ Pagination
        $lapangan = $query->paginate(6)->appends($request->query());

        return view('lapangan.index', compact('lapangan'));
    }

    /**
     * Simpan data lapangan baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_lapangan' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'lokasi' => 'required|string|max:500',
            'harga_sewa' => 'required|numeric|min:0',
            'durasi_sewa' => 'required|integer|min:30|max:300',
            'status' => 'required|string|max:255',
            'rating' => 'required|numeric|min:1|max:5',
            'kapasitas' => 'required|integer|min:1',
            'fasilitas' => 'nullable|string|max:500',
            'deskripsi' => 'nullable|string',
            'foto.*' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Proses upload foto
        $fotoPaths = [];
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $foto) {
                $path = $foto->store('lapangan', 'public');
                $fotoPaths[] = $path;
            }
        }

        // Simpan ke database
        Lapangan::create([
            'pemilik_id' => auth()->id(),
            'nama_lapangan' => $request->nama_lapangan,
            'kategori' => $request->kategori,
            'lokasi' => $request->lokasi,
            'harga_sewa' => $request->harga_sewa,
            'durasi_sewa' => $request->durasi_sewa,
            'status' => $request->status,
            'rating' => $request->rating,
            'kapasitas' => $request->kapasitas,
            'fasilitas' => $request->fasilitas,
            'deskripsi' => $request->deskripsi,
            'foto' => json_encode($fotoPaths),
        ]);

        return redirect()->route('lapangan.index')->with('success', 'Lapangan berhasil ditambahkan!');
    }

    /**
     * Update data lapangan.
     */
    public function update(Request $request, $id)
    {
        $lapangan = Lapangan::findOrFail($id);

        $request->validate([
            'nama_lapangan' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'lokasi' => 'required|string|max:500',
            'harga_sewa' => 'required|numeric|min:0',
            'durasi_sewa' => 'required|integer|min:30|max:300',
            'status' => 'required|string|max:255',
            'rating' => 'required|numeric|min:1|max:5',
            'kapasitas' => 'required|integer|min:1',
            'fasilitas' => 'nullable|string|max:500',
            'deskripsi' => 'nullable|string',
            'foto.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $fotoPaths = json_decode($lapangan->foto, true) ?? [];

        // Jika ada foto baru, upload dan ganti yang lama
        if ($request->hasFile('foto')) {
            foreach ($fotoPaths as $oldFoto) {
                Storage::disk('public')->delete($oldFoto);
            }

            $fotoPaths = [];
            foreach ($request->file('foto') as $foto) {
                $path = $foto->store('lapangan', 'public');
                $fotoPaths[] = $path;
            }
        }

        $lapangan->update([
            'nama_lapangan' => $request->nama_lapangan,
            'kategori' => $request->kategori,
            'lokasi' => $request->lokasi,
            'harga_sewa' => $request->harga_sewa,
            'durasi_sewa' => $request->durasi_sewa,
            'status' => $request->status,
            'rating' => $request->rating,
            'kapasitas' => $request->kapasitas,
            'fasilitas' => $request->fasilitas,
            'deskripsi' => $request->deskripsi,
            'foto' => !empty($fotoPaths) ? json_encode($fotoPaths) : $lapangan->foto,
        ]);

        return redirect()->route('lapangan.index')->with('success', 'Data lapangan berhasil diperbarui!');
    }

    /**
     * Hapus data lapangan.
     */
    public function destroy($id)
    {
        $lapangan = Lapangan::findOrFail($id);

        // Hapus foto dari storage
        if ($lapangan->foto) {
            foreach (json_decode($lapangan->foto, true) as $foto) {
                Storage::disk('public')->delete($foto);
            }
        }

        // Hapus jadwal terkait
        $lapangan->jadwal()->delete();

        $lapangan->delete();

        return redirect()->route('lapangan.index')->with('success', 'Lapangan berhasil dihapus!');
    }

    /**
     * Simpan jadwal lapangan.
     */
    public function storeJadwal(Request $request, $lapanganId)
    {
        $request->validate([
            'tanggal' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'tersedia' => 'required|boolean',
        ]);

        $existingJadwal = JadwalLapangan::where('lapangan_id', $lapanganId)
            ->where('tanggal', $request->tanggal)
            ->where('jam_mulai', $request->jam_mulai)
            ->where('jam_selesai', $request->jam_selesai)
            ->first();

        if ($existingJadwal) {
            return redirect()->back()->with('error', 'Jadwal sudah ada!');
        }

        JadwalLapangan::create([
            'lapangan_id' => $lapanganId,
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'tersedia' => $request->tersedia,
        ]);

        return redirect()->back()->with('success', 'Jadwal berhasil ditambahkan!');
    }

    /**
     * Hapus jadwal lapangan.
     */
    public function destroyJadwal($lapanganId, $jadwalId)
    {
        $jadwal = JadwalLapangan::where('lapangan_id', $lapanganId)
            ->where('id', $jadwalId)
            ->firstOrFail();

        $jadwal->delete();

        return redirect()->back()->with('success', 'Jadwal berhasil dihapus!');
    }
}
