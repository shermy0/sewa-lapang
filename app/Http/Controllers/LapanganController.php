<?php

namespace App\Http\Controllers;

use App\Models\JadwalLapangan;
use App\Models\Lapangan;
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
            ->when($request->filled('min_harga'), function ($query) use ($request) {
                $query->where('harga_sewa', '>=', $request->min_harga);
            })
            ->when($request->filled('max_harga'), function ($query) use ($request) {
                $query->where('harga_sewa', '<=', $request->max_harga);
            })
            ->when($request->filled('sort_harga'), function ($query) use ($request) {
                $query->orderBy('harga_sewa', $request->sort_harga);
            }, function ($query) {
                $query->latest();
            })
            ->paginate(6)
            ->appends($request->query());

        return view('lapangan.index', compact('lapangan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lapangan' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:255'],
            'lokasi' => ['required', 'string', 'max:500'],
            // 'harga_sewa' => ['required', 'numeric', 'min:0'],
            'durasi_sewa' => ['required', 'numeric', 'min:0.5', 'max:5'],
            // 'status' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'foto.*' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $fotoPaths = [];
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $foto) {
                $fotoPaths[] = $foto->store('lapangan', 'public');
            }
        }

        $durasiMenit = $this->convertJamKeMenit($request->durasi_sewa);

        Lapangan::create([
            'pemilik_id' => auth()->id(),
            'nama_lapangan' => $request->nama_lapangan,
            'kategori' => $request->kategori,
            'lokasi' => $request->lokasi,
            // 'harga_sewa' => $request->harga_sewa,
            'durasi_sewa' => $durasiMenit,
            // 'status' => $request->status,
            'deskripsi' => $request->deskripsi,
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
            'lokasi' => ['required', 'string', 'max:500'],
            // 'harga_sewa' => ['required', 'numeric', 'min:0'],
            'durasi_sewa' => ['required', 'numeric', 'min:0.5', 'max:5'],
            // 'status' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'foto.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $fotoPaths = $lapangan->foto ?? [];
        if (!is_array($fotoPaths)) {
            $decoded = json_decode($lapangan->foto, true);
            $fotoPaths = is_array($decoded) ? $decoded : [];
        }

        if ($request->hasFile('foto')) {
            foreach ($fotoPaths as $oldFoto) {
                Storage::disk('public')->delete($oldFoto);
            }

            $fotoPaths = [];
            foreach ($request->file('foto') as $foto) {
                $fotoPaths[] = $foto->store('lapangan', 'public');
            }
        }

        $durasiMenit = $this->convertJamKeMenit($request->durasi_sewa);

        $lapangan->update([
            'nama_lapangan' => $request->nama_lapangan,
            'kategori' => $request->kategori,
            'lokasi' => $request->lokasi,
            // 'harga_sewa' => $request->harga_sewa,
            'durasi_sewa' => $durasiMenit,
            // 'status' => $request->status,
            'deskripsi' => $request->deskripsi,
            'foto' => $fotoPaths,
        ]);

        return redirect()->route('lapangan.index')->with('success', 'Data lapangan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $lapangan = Lapangan::findOrFail($id);

        $fotoPaths = $lapangan->foto ?? [];
        if (!is_array($fotoPaths)) {
            $decoded = json_decode($lapangan->foto, true);
            $fotoPaths = is_array($decoded) ? $decoded : [];
        }

        foreach ($fotoPaths as $foto) {
            Storage::disk('public')->delete($foto);
        }

        $lapangan->jadwal()->delete();
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
            'harga_sewa' => ['nullable', 'numeric', 'min:0'],
            'tersedia' => ['required', 'boolean'],
        ]);

        $hasConflict = JadwalLapangan::where('lapangan_id', $lapanganId)
            ->where('tanggal', $request->tanggal)
            ->where('jam_mulai', '<', $request->jam_selesai)
            ->where('jam_selesai', '>', $request->jam_mulai)
            ->exists();

        if ($hasConflict) {
            return redirect()->back()->with('error', 'Rentang waktu bertabrakan dengan jadwal lain!');
        }

        $jamMulaiCarbon = Carbon::createFromFormat('H:i', $request->jam_mulai);
        $jamSelesaiCarbon = Carbon::createFromFormat('H:i', $request->jam_selesai);

        $durasiInput = $request->input('durasi_sewa');
        $durasiSewa = null;

        if (!is_null($durasiInput) && $durasiInput !== '') {
            $durasiNumeric = (float) str_replace(',', '.', (string) $durasiInput);
            $durasiNumeric = max(0, $durasiNumeric);

            if ($durasiNumeric > 24) {
                $durasiSewa = (int) round($durasiNumeric);
            } else {
                $durasiSewa = (int) round($durasiNumeric * 60);
            }
        }

        $rentangMenit = $jamMulaiCarbon->diffInMinutes($jamSelesaiCarbon);

        if (empty($durasiSewa) || $durasiSewa <= 0) {
            $durasiSewa = max(1, $rentangMenit);
        }

        if (abs($rentangMenit - $durasiSewa) > 1) {
            return redirect()
                ->back()
                ->withErrors([
                    'durasi_sewa' => 'Durasi harus sesuai dengan selisih Jam Mulai dan Jam Selesai.',
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
            'durasi_sewa' => $durasiSewa,
            'harga_sewa' => $hargaPerJam,
            'tersedia' => $request->tersedia,
        ]);

        return redirect()->back()->with('success', 'Jadwal berhasil ditambahkan!');
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

    private function convertJamKeMenit($input): int
    {
        if (is_null($input) || $input === '') {
            return 60;
        }

        $numeric = (float) str_replace(',', '.', (string) $input);

        if ($numeric <= 0) {
            return 60;
        }

        if ($numeric > 24) {
            $menit = (int) round($numeric);
        } else {
            $menit = (int) round($numeric * 60);
        }

        return max(30, min(300, $menit));
    }
}