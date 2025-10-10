<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PemilikDashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard untuk pemilik.
     */
    public function index()
    {
        $user = Auth::user();

        // Contoh data dummy sementara (bisa diganti dari DB nanti)
        $data = [
            'totalLapangan' => 12,
            'totalPemesanan' => 84,
            'totalPendapatan' => 12500000,
            'totalPengguna' => 32,
        ];

        return view('pemilik.dashboard', compact('user', 'data'));
    }
}
