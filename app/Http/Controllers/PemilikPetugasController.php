<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PemilikPetugasController extends Controller
{
    public function index()
    {
        // Ambil semua petugas yang dimiliki pemilik ini
        $petugas = User::where('role', 'petugas')
                        ->where('pemilik_id', Auth::id())
                        ->get();

        return view('pemilik.petugas', compact('petugas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'role'       => 'petugas',
            'pemilik_id' => Auth::id(),
            'password'   => bcrypt('password123'),
            'status'     => 'aktif',
        ]);        

        return redirect()->route('pemilik.petugas')->with('success', 'Petugas berhasil ditambahkan.');
    }
}