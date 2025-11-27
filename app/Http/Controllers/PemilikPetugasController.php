<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PemilikPetugasController extends Controller
{
    public function index()
    {
        // Ambil semua petugas milik pemilik yang login
        $petugas = User::where('role', 'petugas')
                       ->where('pemilik_id', Auth::id())
                       ->get();

        return view('pemilik.petugas', compact('petugas'));
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        try {
            // Buat petugas baru
            $petugas = User::create([
                'name'       => $request->name,
                'email'      => $request->email,
                'role'       => 'petugas',
                'pemilik_id' => Auth::id(),
                'password'   => bcrypt('password123'), // default password
                'status'     => 'aktif',
            ]);

            // Verifikasi otomatis akun petugas yang dibuat oleh pemilik
            $petugas->forceFill([
                'email_verified_at' => now(),
            ])->save();

            return redirect()->route('pemilik.petugas')
                             ->with('success', 'Petugas berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()
                             ->with('error', 'Gagal menambahkan petugas: ' . $e->getMessage());
        }
    }
}
