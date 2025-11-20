<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Lapangan;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = Kategori::all(); // hapus where('pemilik_id', auth()->id())
        return view('pemilik.kategori', compact('kategori'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        Kategori::create($request->only('nama_kategori', 'deskripsi'));

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function show($id)
    {
        return response()->json(Kategori::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $kategori = Kategori::where('pemilik_id', auth()->id())->findOrFail($id);

        $kategori->update($request->only('nama_kategori', 'deskripsi'));

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $kategori = Kategori::where('pemilik_id', auth()->id())->findOrFail($id);

        // Cek apakah kategori masih dipakai oleh lapangan milik pemilik ini
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
