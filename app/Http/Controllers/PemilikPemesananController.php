<?php

namespace App\Http\Controllers;

use App\Models\Pemesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class PemilikPemesananController extends Controller
{
    /**
     * Tampilkan daftar pemesanan untuk pemilik lapangan.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        abort_if(!$user || $user->role !== 'pemilik', 403, 'Anda tidak memiliki akses.');

        $statusFilter = $request->query('status');
        $search = $request->query('search');

        $baseQuery = Pemesanan::query()
            ->with(['penyewa', 'lapangan', 'jadwal'])
            ->whereHas('lapangan', function ($query) use ($user) {
                $query->where('pemilik_id', $user->id);
            });

        if ($statusFilter && in_array($statusFilter, ['menunggu', 'dibayar', 'selesai', 'batal'], true)) {
            $baseQuery->where('status', $statusFilter);
        }

        if ($search) {
            $baseQuery->where(function ($query) use ($search) {
                $query->whereHas('penyewa', function ($penyewaQuery) use ($search) {
                    $penyewaQuery->where('name', 'like', '%' . $search . '%')
                                 ->orWhere('email', 'like', '%' . $search . '%');
                })->orWhereHas('lapangan', function ($lapanganQuery) use ($search) {
                    $lapanganQuery->where('nama_lapangan', 'like', '%' . $search . '%')
                                  ->orWhere('lokasi', 'like', '%' . $search . '%');
                });
            });
        }

        $pemesanan = $baseQuery
            ->latest('updated_at')
            ->paginate(10)
            ->appends($request->query());

        $summaryQuery = Pemesanan::query()
            ->whereHas('lapangan', function ($query) use ($user) {
                $query->where('pemilik_id', $user->id);
            });

        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'menunggu' => (clone $summaryQuery)->where('status', 'menunggu')->count(),
            'dibayar' => (clone $summaryQuery)->where('status', 'dibayar')->count(),
            'selesai' => (clone $summaryQuery)->where('status', 'selesai')->count(),
            'batal' => (clone $summaryQuery)->where('status', 'batal')->count(),
        ];

        if ($summary['total'] === 0) {
            $dummy = $this->buildDummyPemesananData($request);
            $pemesanan = $dummy['pemesanan'];
            $summary = $dummy['summary'];
        }

        return view('pemilik.pemesanan.index', [
            'pemesanan' => $pemesanan,
            'summary' => $summary,
            'statusFilter' => $statusFilter,
            'search' => $search,
        ]);
    }

    /**
     * Perbarui status pemesanan tertentu.
     */
    public function updateStatus(Request $request, Pemesanan $pemesanan)
    {
        $user = Auth::user();

        abort_if(!$user || $user->role !== 'pemilik', 403, 'Anda tidak memiliki akses.');
        $pemesanan->loadMissing(['lapangan', 'jadwal']);

        abort_unless(
            $pemesanan->lapangan && $pemesanan->lapangan->pemilik_id === $user->id,
            403,
            'Pemesanan tidak ditemukan.'
        );

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['menunggu', 'dibayar', 'selesai', 'batal']),
            ],
        ]);

        $pemesanan->update([
            'status' => $validated['status'],
        ]);

        if ($pemesanan->jadwal) {
            $pemesanan->jadwal->update([
                'tersedia' => $validated['status'] === 'batal',
            ]);
        }

        return back()->with('success', 'Status pemesanan berhasil diperbarui.');
    }

    /**
     * Data dummy pemesanan untuk tampilan awal sebelum integrasi database.
     */
    protected function buildDummyPemesananData(Request $request): array
    {
        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $fullCollection = collect([
            $this->makeDummyPemesanan(
                1,
                'menunggu',
                'Andi Saputra',
                'andi@example.com',
                'Lapangan Futsal Garuda',
                'Jakarta Selatan',
                Carbon::now()->locale(app()->getLocale())->addDays(2),
                Carbon::today()->addDays(2)->setTime(9, 0),
                Carbon::today()->addDays(2)->setTime(11, 0)
            ),
            $this->makeDummyPemesanan(
                2,
                'dibayar',
                'Budi Hartono',
                'budi@example.com',
                'Arena Badminton Victory',
                'Bandung',
                Carbon::now()->locale(app()->getLocale())->addDays(3),
                Carbon::today()->addDays(3)->setTime(14, 0),
                Carbon::today()->addDays(3)->setTime(16, 0)
            ),
            $this->makeDummyPemesanan(
                3,
                'selesai',
                'Clara Wijaya',
                'clara@example.com',
                'Soccer Field Champion',
                'Surabaya',
                Carbon::now()->locale(app()->getLocale())->subDays(1),
                Carbon::today()->subDays(1)->setTime(18, 0),
                Carbon::today()->subDays(1)->setTime(20, 0)
            ),
            $this->makeDummyPemesanan(
                4,
                'batal',
                'Doni Kurniawan',
                'doni@example.com',
                'Lapangan Basket Galaxy',
                'Yogyakarta',
                Carbon::now()->locale(app()->getLocale())->addDays(5),
                Carbon::today()->addDays(5)->setTime(8, 0),
                Carbon::today()->addDays(5)->setTime(10, 0)
            ),
        ]);

        $summary = [
            'total' => $fullCollection->count(),
            'menunggu' => $fullCollection->where('status', 'menunggu')->count(),
            'dibayar' => $fullCollection->where('status', 'dibayar')->count(),
            'selesai' => $fullCollection->where('status', 'selesai')->count(),
            'batal' => $fullCollection->where('status', 'batal')->count(),
        ];

        $filtered = $fullCollection;

        if ($status = $request->query('status')) {
            $filtered = $filtered->where('status', $status)->values();
        }

        if ($search = $request->query('search')) {
            $search = mb_strtolower($search);
            $filtered = $filtered->filter(function ($item) use ($search) {
                $haystack = mb_strtolower(
                    ($item->penyewa->name ?? '') . ' ' .
                    ($item->penyewa->email ?? '') . ' ' .
                    ($item->lapangan->nama_lapangan ?? '') . ' ' .
                    ($item->lapangan->lokasi ?? '')
                );

                return str_contains($haystack, $search);
            })->values();
        }

        $paginator = new LengthAwarePaginator(
            $filtered->forPage($currentPage, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return [
            'pemesanan' => $paginator,
            'summary' => $summary,
        ];
    }

    /**
     * Helper untuk membuat objek pemesanan dummy.
     */
    protected function makeDummyPemesanan(
        int $id,
        string $status,
        string $penyewaName,
        string $penyewaEmail,
        string $lapanganName,
        string $lapanganLokasi,
        Carbon $tanggal,
        Carbon $jamMulai,
        Carbon $jamSelesai
    ): object {
        return (object) [
            'id' => $id,
            'status' => $status,
            'penyewa' => (object) [
                'name' => $penyewaName,
                'email' => $penyewaEmail,
            ],
            'lapangan' => (object) [
                'nama_lapangan' => $lapanganName,
                'lokasi' => $lapanganLokasi,
            ],
            'jadwal' => (object) [
                'tanggal' => $tanggal,
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
            ],
        ];
    }
}
