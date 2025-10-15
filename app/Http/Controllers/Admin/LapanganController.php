<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lapangan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LapanganController extends Controller
{
    private const STATUSES = ['pending', 'standard', 'promo', 'nonaktif'];

    public function index(Request $request)
    {
        $query = Lapangan::with('pemilik')->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($verification = $request->input('verification')) {
            if ($verification === 'verified') {
                $query->where('is_verified', true);
            } elseif ($verification === 'unverified') {
                $query->where('is_verified', false);
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lapangan', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        $lapangan = $query->paginate(10)->appends($request->query());
        $owners = User::where('role', 'pemilik')->pluck('name', 'id');

        return view('admin.lapangan.index', [
            'lapangan' => $lapangan,
            'owners' => $owners,
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(Request $request, Lapangan $lapangan)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
            'pemilik_id' => ['nullable', 'exists:users,id'],
            'kategori' => ['required', 'string', 'max:255'],
            'harga_per_jam' => ['required', 'numeric', 'min:0'],
            'is_verified' => ['required', 'boolean'],
        ]);

        $lapangan->update([
            'status' => $validated['status'],
            'pemilik_id' => $request->filled('pemilik_id') ? $validated['pemilik_id'] : null,
            'kategori' => $validated['kategori'],
            'harga_per_jam' => $validated['harga_per_jam'],
            'is_verified' => $validated['is_verified'],
        ]);

        return redirect()
            ->route('admin.lapangan.index')
            ->with('success', 'Data lapangan berhasil diperbarui.');
    }
}
