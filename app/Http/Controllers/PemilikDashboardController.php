<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use App\Models\Pemesanan;
use App\Models\Pembayaran;
use Carbon\Carbon;
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
        Carbon::setLocale('id');

        $lapanganIds = Lapangan::where('pemilik_id', $user->id)->pluck('id');

        $totalLapangan = $lapanganIds->count();

        $pemesananQuery = Pemesanan::with(['lapangan', 'penyewa', 'jadwal.section', 'pembayaran'])
            ->whereIn('lapangan_id', $lapanganIds);

        $totalPemesanan = (clone $pemesananQuery)->count();

        $totalPendapatan = Pembayaran::whereHas('pemesanan', function ($query) use ($lapanganIds) {
                $query->whereIn('lapangan_id', $lapanganIds);
            })
            ->where('status', 'berhasil')
            ->sum('jumlah');

        $totalPengguna = (clone $pemesananQuery)->distinct('penyewa_id')->count('penyewa_id');

        $recentPemesanan = (clone $pemesananQuery)
            ->latest()
            ->take(5)
            ->get();

        $monthlyLabels = [];
        $monthlyCounts = [];
        $monthlyRevenue = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $monthlyLabels[] = $month->translatedFormat('M Y');
            $monthlyCounts[] = (clone $pemesananQuery)
                ->whereBetween('created_at', [$start, $end])
                ->count();

            $monthlyRevenue[] = Pembayaran::whereHas('pemesanan', function ($query) use ($lapanganIds, $start, $end) {
                    $query->whereIn('lapangan_id', $lapanganIds)
                        ->whereBetween('created_at', [$start, $end]);
                })
                ->where('status', 'berhasil')
                ->sum('jumlah');
        }

        $stats = [
            'totalLapangan' => $totalLapangan,
            'totalPemesanan' => $totalPemesanan,
            'totalPendapatan' => $totalPendapatan,
            'totalPengguna' => $totalPengguna,
        ];

        return view('pemilik.dashboard', [
            'user' => $user,
            'stats' => $stats,
            'recentPemesanan' => $recentPemesanan,
            'monthlyLabels' => $monthlyLabels,
            'monthlyCounts' => $monthlyCounts,
            'monthlyRevenue' => $monthlyRevenue,
        ]);
    }
}