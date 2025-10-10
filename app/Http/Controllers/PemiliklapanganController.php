<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PemilikLapanganController extends Controller
{
    public function index()
    {
        // Ambil semua lapangan milik user yang login (pemilik)
        $lapangan = Lapangan::where('pemilik_id', Auth::id())->paginate(10);

        return view('pemilik.lapangan.index', compact('lapangan'));
    }
}
