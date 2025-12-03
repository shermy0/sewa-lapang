<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Lapangan;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index()
    {
        // hanya kategori milik pemilik yang login
        $kategori = Kategori::where('pemilik_id', auth()->id())
            ->withCount('lapangan')
            ->get();

        return view('pemilik.kategori', compact('kategori'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        // simpan kategori berdasarkan pemilik yang login
        Kategori::create([
            'nama_kategori' => $request->nama_kategori,
            'deskripsi' => $request->deskripsi,
            'pemilik_id' => auth()->id(),
        ]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function show($id)
    {
        return response()->json(
            Kategori::where('pemilik_id', auth()->id())->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        // pastikan hanya bisa update kategori miliknya
        $kategori = Kategori::where('pemilik_id', auth()->id())->findOrFail($id);

        $kategori->update($request->only('nama_kategori', 'deskripsi'));

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $kategori = Kategori::where('pemilik_id', auth()->id())->findOrFail($id);

        // cek apakah kategori dipakai oleh lapangan pemilik ini
        $dipakai = Lapangan::where('id_kategori', $id)
            ->where('pemilik_id', auth()->id())
            ->count();

        if ($dipakai > 0) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh ' . $dipakai . ' lapangan.');
        }

        $kategori->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
