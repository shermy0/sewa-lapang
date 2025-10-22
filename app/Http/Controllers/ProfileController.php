<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Lapangan;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Kalau role pemilik, ambil juga lapangannya
        $lapangan = [];
        if ($user->role === 'pemilik') {
            $lapangan = Lapangan::where('pemilik_id', $user->id)->get();
        }

        return view('profile.index', compact('user', 'lapangan'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'nullable|string|max:20',
            'password' => 'nullable|min:6|confirmed',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Update data dasar
        $user->name = $validated['nama'];
        $user->no_hp = $validated['no_hp'] ?? $user->no_hp;

        // Update foto
        if ($request->hasFile('foto_profil')) {
            if ($user->foto_profil) {
                Storage::delete('public/' . $user->foto_profil);
            }
            $path = $request->file('foto_profil')->store('profile_photos', 'public');
            $user->foto_profil = $path;
        }

        // Update password (jika diisi)
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function hapusFoto()
    {
        $user = Auth::user();

        if ($user->foto_profil) {
            Storage::delete('public/' . $user->foto_profil);
            $user->foto_profil = null;
            $user->save();
        }

        return response()->json(['success' => true]);
    }
public function updatePassword(Request $request)
{
    $user = Auth::user();

    $request->validate([
        'current_password' => 'required',
        'password' => 'required|min:6|confirmed',
    ]);

    // ✅ Cek password lama
    if (!Hash::check($request->current_password, $user->password)) {
        return back()
            ->withErrors(['current_password' => 'Password lama salah!'])
            ->with('activeTab', 'password'); // Tambahkan ini
    }

    // 🔒 Update password baru
    $user->password = Hash::make($request->password);
    $user->save();

    return back()
        ->with('success', 'Password berhasil diperbarui!')
        ->with('activeTab', 'password'); // Tambahkan ini juga
}


}
