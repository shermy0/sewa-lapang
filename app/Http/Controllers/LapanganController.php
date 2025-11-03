<?php

namespace App\Http\Controllers;

use App\Models\JadwalLapangan;
use App\Models\Lapangan;
use App\Models\Kategori;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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
            'tiket_tersedia' => ['nullable', 'integer', 'min:0'],
            'sections' => ['nullable', 'array'],
            'sections.*.nama_section' => ['nullable', 'string', 'max:255'],
            'sections.*.deskripsi' => ['nullable', 'string', 'max:255'],
            'foto.*' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $fotoPaths = [];
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $foto) {
                $fotoPaths[] = $foto->store('lapangan', 'public');
            }
        }

        $kategoriModel = Kategori::find($request->id_kategori);
        $tiketTersedia = $request->filled('tiket_tersedia')
            ? max(0, (int) $request->input('tiket_tersedia'))
            : 0;

        $sectionsInput = $request->input('sections', []);

        DB::transaction(function () use ($request, $kategoriModel, $fotoPaths, $tiketTersedia, $sectionsInput) {
            $lapangan = Lapangan::create([
                'pemilik_id' => auth()->id(),
                'id_kategori' => $request->id_kategori,
                'nama_lapangan' => $request->nama_lapangan,
                'kategori' => $kategoriModel?->nama_kategori,
                'lokasi' => $request->lokasi,
                'deskripsi' => $request->deskripsi,
                'tiket_tersedia' => $tiketTersedia,
                'status' => $request->input('status', 'standard'),
                'is_verified' => false,
                'foto' => $fotoPaths,
            ]);

            $this->syncSections($lapangan, $sectionsInput, false);
        });

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
            'id_kategori' => ['required', 'integer', 'exists:kategori,id'],
            'lokasi' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'tiket_tersedia' => ['nullable', 'integer', 'min:0'],
            'sections' => ['nullable', 'array'],
            'sections.*.nama_section' => ['nullable', 'string', 'max:255'],
            'sections.*.deskripsi' => ['nullable', 'string', 'max:255'],
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

        $kategoriModel = Kategori::find($request->id_kategori);
        $tiketTersedia = $request->filled('tiket_tersedia')
            ? max(0, (int) $request->input('tiket_tersedia'))
            : ($lapangan->tiket_tersedia ?? 0);
        $sectionsInput = $request->input('sections', []);

        DB::transaction(function () use ($lapangan, $request, $kategoriModel, $tiketTersedia, $fotoPaths, $sectionsInput) {
            $lapangan->update([
                'nama_lapangan' => $request->nama_lapangan,
                'id_kategori' => $request->id_kategori,
                'kategori' => $kategoriModel?->nama_kategori,
                'lokasi' => $request->lokasi,
                'deskripsi' => $request->deskripsi,
                'tiket_tersedia' => $tiketTersedia,
                'status' => $request->input('status', $lapangan->status),
                'foto' => $fotoPaths, // Laravel akan otomatis convert ke JSON
            ]);

            $this->syncSections($lapangan, $sectionsInput, true);
        });

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

    public function getSectionJadwal($lapanganId, $sectionId)
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Sesi Anda telah berakhir. Silakan login kembali.',
                'jadwal' => [],
            ], 401);
        }

        if (auth()->check() && auth()->user()->role === 'pemilik') {
            $lapangan = Lapangan::with('sections')
                ->where('id', $lapanganId)
                ->where('pemilik_id', auth()->id())
                ->first();
        } else {
            $lapangan = Lapangan::with('sections')->find($lapanganId);
        }

        if (!$lapangan) {
            return response()->json([
                'message' => 'Lapangan tidak ditemukan atau tidak dapat diakses.',
                'jadwal' => [],
            ], 404);
        }

        $section = $lapangan->sections()
            ->with(['jadwal' => function ($query) {
                $query->orderBy('tanggal')->orderBy('jam_mulai');
            }])
            ->where('id', $sectionId)
            ->first();

        if (!$section) {
            return response()->json([
                'message' => 'Section tidak ditemukan.',
                'jadwal' => [],
            ], 404);
        }

        try {
            $jadwalData = $section->jadwal->map(function ($jadwal) {
                return [
                    'id' => $jadwal->id,
                    'tanggal' => $jadwal->tanggal ? Carbon::parse($jadwal->tanggal)->format('Y-m-d') : null,
                    'jam_mulai' => $jadwal->jam_mulai,
                    'jam_selesai' => $jadwal->jam_selesai,
                    'durasi_sewa' => (int) $jadwal->durasi_sewa,
                    'harga_sewa' => (float) $jadwal->harga_sewa,
                    'tersedia' => (bool) $jadwal->tersedia,
                ];
            })->values();
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data jadwal.',
                'jadwal' => [],
            ], 500);
        }

        return response()->json([
            'section' => [
                'id' => $section->id,
                'nama_section' => $section->nama_section,
                'deskripsi' => $section->deskripsi,
            ],
            'jadwal' => $jadwalData,
        ]);
    }

    public function storeJadwal(Request $request, $lapanganId)
    {
        $request->validate([
            'section_id' => [
                'required',
                'integer',
                Rule::exists('section_lapangan', 'id')->where('lapangan_id', $lapanganId),
            ],
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'durasi_sewa' => ['nullable', 'numeric', 'min:0.25', 'max:24'],
            'harga_sewa' => ['required', 'numeric', 'min:0'],
            'tersedia' => ['required', 'boolean'],
        ]);

        // Check for time conflicts
        $hasConflict = JadwalLapangan::where('lapangan_id', $lapanganId)
            ->where('section_id', $request->section_id)
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
            'section_id' => $request->section_id,
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
            'section_id' => [
                'required',
                'integer',
                Rule::exists('section_lapangan', 'id')->where('lapangan_id', $lapanganId),
            ],
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
            ->where('section_id', $request->section_id)
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
            'section_id' => $request->section_id,
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

    private function sanitizeSectionData(?array $section): ?array
    {
        if (!is_array($section)) {
            return null;
        }

        $nama = trim($section['nama_section'] ?? '');
        if ($nama === '') {
            return null;
        }

        $deskripsi = trim($section['deskripsi'] ?? '');

        return [
            'nama_section' => $nama,
            'deskripsi' => $deskripsi !== '' ? $deskripsi : null,
        ];
    }

    private function syncSections(Lapangan $lapangan, array $sectionsInput, bool $replaceExisting = true): void
    {
        $processedIds = [];

        foreach ($sectionsInput as $key => $sectionRaw) {
            $sectionData = $this->sanitizeSectionData($sectionRaw);
            if (!$sectionData) {
                continue;
            }

            if ($replaceExisting && ctype_digit((string) $key)) {
                $existing = $lapangan->sections()->where('id', (int) $key)->first();
                if ($existing) {
                    $existing->update($sectionData);
                    $processedIds[] = (int) $key;
                    continue;
                }
            }

            $newSection = $lapangan->sections()->create($sectionData);
            $processedIds[] = $newSection->id;
        }

        if (empty($processedIds)) {
            $default = $lapangan->sections()->create([
                'nama_section' => 'Lapangan Utama',
                'deskripsi' => null,
            ]);
            $processedIds[] = $default->id;
        }

        if ($replaceExisting) {
            $lapangan->sections()
                ->whereNotIn('id', $processedIds)
                ->delete();
        }
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


