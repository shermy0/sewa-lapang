<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lapangan;
use Illuminate\Http\Request;

class LapanganController extends Controller
{
    private const STATUSES = ['pending', 'standard', 'promo', 'nonaktif'];

    public function index(Request $request)
    {
        $query = Lapangan::with('pemilik')->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lapangan', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        $lapangan = $query->paginate(10)->appends($request->query());

        return view('admin.lapangan.index', [
            'lapangan' => $lapangan,
            'statuses' => self::STATUSES,
        ]);
    }
}
