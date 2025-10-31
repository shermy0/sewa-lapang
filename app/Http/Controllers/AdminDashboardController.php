<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use App\Models\LaporanPenyalahgunaan;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\User;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'totalUsers' => User::count(),
            'totalPemilik' => User::where('role', 'pemilik')->count(),
            'totalPenyewa' => User::where('role', 'penyewa')->count(),
            'totalLapangan' => Lapangan::count(),
            'totalPemesanan' => Pemesanan::count(),
            'totalPendapatan' => Pembayaran::where('status', 'berhasil')->sum('jumlah'),
            'pendingReports' => LaporanPenyalahgunaan::where('status', 'pending')->count(),
        ];

        $monthlyLabels = [];
        $monthlyPemesanan = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);

            $monthlyLabels[] = $month->translatedFormat('M Y');
            $monthlyPemesanan[] = Pemesanan::whereBetween('created_at', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ])->count();
        }

        $latestUsers = User::latest()->take(5)->get();
        $latestLapangan = Lapangan::with('pemilik')->latest()->take(5)->get();
        $recentPemesanan = Pemesanan::with(['penyewa', 'lapangan'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'monthlyLabels' => $monthlyLabels,
            'monthlyPemesanan' => $monthlyPemesanan,
            'latestUsers' => $latestUsers,
            'latestLapangan' => $latestLapangan,
            'recentPemesanan' => $recentPemesanan,
        ]);
    }
}
