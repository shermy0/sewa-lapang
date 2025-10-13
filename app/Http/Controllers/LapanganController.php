<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LapanganController extends Controller
{
    /**
     * Menampilkan semua data lapangan.
     */
public function index(Request $request)
{
    $query = Lapangan::query();

    // 🔍 Filter pencarian (nama atau lokasi)
    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('nama_lapangan', 'like', '%' . $request->search . '%')
              ->orWhere('lokasi', 'like', '%' . $request->search . '%');
        });
    }

    // 🎯 Filter kategori
    if ($request->filled('kategori')) {
        $query->where('kategori', $request->kategori);
    }

    // 💰 Filter harga minimal dan maksimal
    if ($request->filled('min_harga')) {
        $query->where('harga_per_jam', '>=', $request->min_harga);
    }
    if ($request->filled('max_harga')) {
        $query->where('harga_per_jam', '<=', $request->max_harga);
    }

    // ⭐ Filter rating
    if ($request->filled('rating')) {
        $query->where('rating', '>=', $request->rating);
    }

    // ✅ INI BAGIAN PENTING — GUNAKAN paginate(), BUKAN get()
    $lapangan = $query->latest()->paginate(6)->appends($request->query());

    return view('lapangan.index', compact('lapangan'));
}



    /**
     * Menampilkan form tambah lapangan.
     */
    public function create()
    {
        return view('lapangan.create');
    }
public function show($id)
{
    $lapangan = Lapangan::findOrFail($id);
    return view('lapangan.show', compact('lapangan'));
}
    /**
     * Simpan data lapangan baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_lapangan' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'harga_per_jam' => 'required|numeric',
            'status' => 'required|string|max:255',
            'rating' => 'required|numeric|min:1|max:5',
            'deskripsi' => 'nullable|string',
            'foto.*' => 'image|mimes:jpeg,png,jpg|max:2048',
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
            'pemilik_id' => auth()->id(), // ✅ otomatis dari user login
            'nama_lapangan' => $request->nama_lapangan,
            'kategori' => $request->kategori,
            'lokasi' => $request->lokasi,
            'harga_per_jam' => $request->harga_per_jam,
            'status' => $request->status,
            'rating' => $request->rating,
            'deskripsi' => $request->deskripsi,
            'foto' => json_encode($fotoPaths),
        ]);

        return redirect()->route('lapangan.index')->with('success', 'Lapangan berhasil ditambahkan!');
    }

    /**
     * Menampilkan form edit lapangan.
     */
    public function edit($id)
    {
        $lapangan = Lapangan::findOrFail($id);
        return view('lapangan.edit', compact('lapangan'));
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
            'lokasi' => 'required|string|max:255',
            'harga_per_jam' => 'required|numeric',
            'status' => 'required|string|max:255',
            'rating' => 'required|numeric|min:1|max:5',
            'deskripsi' => 'nullable|string',
            'foto.*' => 'image|mimes:jpeg,png,jpg|max:2048',
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
            'harga_per_jam' => $request->harga_per_jam,
            'status' => $request->status,
            'rating' => $request->rating,
            'deskripsi' => $request->deskripsi,
            'foto' => json_encode($fotoPaths),
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

        $lapangan->delete();

        return redirect()->route('lapangan.index')->with('success', 'Lapangan berhasil dihapus!');
    }
}
