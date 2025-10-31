<?php

namespace App\Http\Controllers;

use App\Models\JadwalLapangan;
use App\Models\Lapangan;
use App\Models\Kategori;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LapanganController extends Controller
{
public function index(Request $request)
{
    $lapangan = Lapangan::query()
        ->with('jadwal')
        ->when($request->filled('search'), function ($query) use ($request) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_lapangan', 'like', '%' . $request->search . '%')
                    ->orWhere('lokasi', 'like', '%' . $request->search . '%');
            });
        })
        ->when($request->filled('kategori'), function ($query) use ($request) {
            $query->where('kategori', 'like', '%' . $request->kategori . '%');
        })
        ->when($request->filled('status'), function ($query) use ($request) {
            $query->where('status', $request->status);
        })
        ->when($request->filled('tiket_tersedia'), function ($query) use ($request) {
            if ($request->tiket_tersedia === 'tersedia') {
                $query->where('tiket_tersedia', '>', 0);
            } elseif ($request->tiket_tersedia === 'habis') {
                $query->where('tiket_tersedia', '<=', 0);
            } 
        })
        ->latest()
        ->paginate(6)
        ->appends($request->query());

    $kategori = Kategori::orderBy('nama_kategori')->get();

    return view('lapangan.index', compact('lapangan', 'kategori'));
}


public function store(Request $request)
{
    $request->validate([
        'nama_lapangan' => ['required', 'string', 'max:255'],
        'id_kategori' => ['required', 'integer', 'exists:kategori,id'],
        'lokasi' => ['required', 'string', 'max:255'],
        'deskripsi' => ['nullable', 'string'],
        'tiket_tersedia' => ['required', 'integer', 'min:0'],
        'foto.*' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
    ]);

    $fotoPaths = [];
    if ($request->hasFile('foto')) {
        foreach ($request->file('foto') as $foto) {
            $fotoPaths[] = $foto->store('lapangan', 'public');
        }
    }

    $kategori = \App\Models\Kategori::find($request->id_kategori);

    Lapangan::create([
        'pemilik_id' => auth()->id(),
        'id_kategori' => $request->id_kategori,
        'nama_lapangan' => $request->nama_lapangan,
        'kategori' => $kategori?->nama_kategori, // opsional
        'lokasi' => $request->lokasi,
        'deskripsi' => $request->deskripsi,
        'tiket_tersedia' => $request->tiket_tersedia,
        'status' => $request->input('status', 'standard'),
        'is_verified' => false,
        'foto' => $fotoPaths,
    ]);

    return redirect()->route('lapangan.index')->with('success', 'Lapangan berhasil ditambahkan!');
}




    public function show($id)
    {
        $lapangan = Lapangan::with('jadwal')->findOrFail($id);

        return view('lapangan.show', compact('lapangan'));
    }

    public function update(Request $request, $id)
    {
        $lapangan = Lapangan::findOrFail($id);

        $request->validate([
            'nama_lapangan' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:255'],
            'lokasi' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'tiket_tersedia' => ['required', 'integer', 'min:0'],
            'foto.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        // Get existing photos (already decoded by Laravel)
        $fotoPaths = $lapangan->foto ?? [];

        // Ensure it's an array
        if (!is_array($fotoPaths)) {
            $fotoPaths = [];
        }

        // Handle new photo uploads
        if ($request->hasFile('foto')) {
            // Delete old photos
            foreach ($fotoPaths as $oldFoto) {
                Storage::disk('public')->delete($oldFoto);
            }

            // Upload new photos
            $fotoPaths = [];
            foreach ($request->file('foto') as $foto) {
                $fotoPaths[] = $foto->store('lapangan', 'public');
            }
        }

        $lapangan->update([
            'nama_lapangan' => $request->nama_lapangan,
            'kategori' => $request->kategori,
            'lokasi' => $request->lokasi,
            'deskripsi' => $request->deskripsi,
            'tiket_tersedia' => $request->tiket_tersedia,
            'status' => $request->input('status', $lapangan->status),
            'foto' => $fotoPaths, // Laravel akan otomatis convert ke JSON
        ]);

        return redirect()->route('lapangan.index')->with('success', 'Data lapangan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $lapangan = Lapangan::findOrFail($id);

        // Get photos (already decoded by Laravel)
        $fotoPaths = $lapangan->foto ?? [];

        // Ensure it's an array
        if (is_array($fotoPaths)) {
            foreach ($fotoPaths as $foto) {
                Storage::disk('public')->delete($foto);
            }
        }

        // Delete related schedules
        $lapangan->jadwal()->delete();

        // Delete the lapangan
        $lapangan->delete();

        return redirect()->route('lapangan.index')->with('success', 'Lapangan berhasil dihapus!');
    }

    public function storeJadwal(Request $request, $lapanganId)
    {
        $request->validate([
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'durasi_sewa' => ['nullable', 'numeric', 'min:0.25', 'max:24'],
            'harga_sewa' => ['required', 'numeric', 'min:0'],
            'tersedia' => ['required', 'boolean'],
        ]);

        // Check for time conflicts
        $hasConflict = JadwalLapangan::where('lapangan_id', $lapanganId)
            ->where('tanggal', $request->tanggal)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('jam_mulai', '<', $request->jam_selesai)
                      ->where('jam_selesai', '>', $request->jam_mulai);
                });
            })
            ->exists();

        if ($hasConflict) {
            return redirect()->back()->with('error', 'Rentang waktu bertabrakan dengan jadwal lain!');
        }

        $jamMulai = Carbon::createFromFormat('H:i', $request->jam_mulai);
        $jamSelesai = Carbon::createFromFormat('H:i', $request->jam_selesai);
        $rentangMenit = $jamMulai->diffInMinutes($jamSelesai);

        $durasiMenit = $this->convertDurasiJamKeMenit($request->input('durasi_sewa'));

        if (empty($durasiMenit) || $durasiMenit <= 0) {
            $durasiMenit = max(1, $rentangMenit);
        }

        if (abs($rentangMenit - $durasiMenit) > 1) {
            return redirect()
                ->back()
                ->withErrors([
                    'durasi_sewa' => 'Durasi harus sesuai dengan selisih Jam Mulai dan Jam Selesai (dalam menit).',
                ])
                ->withInput();
        }

        $hargaPerJam = $request->input('harga_sewa');
        if (is_null($hargaPerJam)) {
            $lapangan = Lapangan::select('harga_sewa')->find($lapanganId);
            $hargaPerJam = $lapangan?->harga_sewa ?? 0;
        }
        $hargaPerJam = (float) $hargaPerJam;

        JadwalLapangan::create([
            'lapangan_id' => $lapanganId,
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'durasi_sewa' => $durasiMenit,
            'harga_sewa' => $hargaPerJam,
            'tersedia' => $request->tersedia,
        ]);

        return redirect()->back()->with('success', 'Jadwal berhasil ditambahkan!');
    }

    public function updateJadwal(Request $request, $lapanganId, $jadwalId)
    {
        $jadwal = JadwalLapangan::where('lapangan_id', $lapanganId)
            ->where('id', $jadwalId)
            ->firstOrFail();

        $request->validate([
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'durasi_sewa' => ['nullable', 'numeric', 'min:0.25', 'max:24'],
            'harga_sewa' => ['required', 'numeric', 'min:0'],
            'tersedia' => ['required', 'boolean'],
        ]);

        // Check for conflicts (excluding current jadwal)
        $hasConflict = JadwalLapangan::where('lapangan_id', $lapanganId)
            ->where('id', '!=', $jadwalId)
            ->where('tanggal', $request->tanggal)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('jam_mulai', '<', $request->jam_selesai)
                      ->where('jam_selesai', '>', $request->jam_mulai);
                });
            })
            ->exists();

        if ($hasConflict) {
            return redirect()->back()->with('error', 'Rentang waktu bertabrakan dengan jadwal lain!');
        }

        $jamMulai = Carbon::createFromFormat('H:i', $request->jam_mulai);
        $jamSelesai = Carbon::createFromFormat('H:i', $request->jam_selesai);
        $rentangMenit = $jamMulai->diffInMinutes($jamSelesai);
        $durasiMenit = $this->convertDurasiJamKeMenit($request->input('durasi_sewa'));

        if (empty($durasiMenit) || $durasiMenit <= 0) {
            $durasiMenit = max(1, $rentangMenit);
        }

        if (abs($rentangMenit - $durasiMenit) > 1) {
            return redirect()
                ->back()
                ->withErrors([
                    'durasi_sewa' => 'Durasi harus sesuai dengan selisih Jam Mulai dan Jam Selesai (dalam menit).',
                ])
                ->withInput();
        }

        $jadwal->update([
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'durasi_sewa' => $durasiMenit,
            'harga_sewa' => $request->harga_sewa,
            'tersedia' => $request->tersedia,
        ]);

        return redirect()->back()->with('success', 'Jadwal berhasil diperbarui!');
    }

    public function destroyJadwal(Request $request, $lapanganId, $jadwalId = null)
    {
        $jadwalId = $jadwalId ?? $request->input('jadwal_id');

        if (!$jadwalId) {
            return redirect()->back()->with('error', 'ID jadwal tidak ditemukan.');
        }

        $jadwal = JadwalLapangan::where('lapangan_id', $lapanganId)
            ->where('id', $jadwalId)
            ->firstOrFail();

        $jadwal->delete();

        return redirect()->back()->with('success', 'Jadwal berhasil dihapus!');
    }

    private function convertDurasiJamKeMenit($input): ?int
    {
        if (is_null($input) || $input === '') {
            return null;
        }

        $numeric = (float) str_replace(',', '.', (string) $input);

        if (!is_finite($numeric) || $numeric <= 0) {
            return null;
        }

        return max(1, (int) round($numeric * 60));
    }
}
