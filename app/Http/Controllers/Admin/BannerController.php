<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    // Tampilkan semua banner
    public function index()
    {
        $banners = Banner::orderBy('id', 'asc')->get();
        return view('admin.banners.index', compact('banners'));
    }

    // Simpan banner baru
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'nullable|string|max:100',
            'gambar' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $banner = new Banner();
        $banner->judul = $request->judul;

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time().'_'.Str::slug($request->judul ?? 'banner').'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);
            $banner->gambar = 'uploads/'.$filename;
        }

        $banner->status = 'aktif';
        $banner->save();

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil ditambahkan.');
    }

    // Update banner (judul dan gambar)
    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'judul' => 'nullable|string|max:100',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $banner->judul = $request->judul;

        if ($request->hasFile('gambar')) {
            // hapus file lama jika ada
            if ($banner->gambar && file_exists(public_path($banner->gambar))) {
                unlink(public_path($banner->gambar));
            }

            $file = $request->file('gambar');
            $filename = time().'_'.Str::slug($request->judul ?? 'banner').'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);
            $banner->gambar = 'uploads/'.$filename;
        }

        $banner->save();

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil diperbarui.');
    }

    // Toggle status aktif/nonaktif
    public function toggle(Banner $banner)
    {
        $banner->status = $banner->status === 'aktif' ? 'nonaktif' : 'aktif';
        $banner->save();

        return redirect()->route('admin.banners.index')->with('success', 'Status banner berhasil diubah.');
    }
}
