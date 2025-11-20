<?php

namespace App\Http\Controllers;

use App\Models\JadwalLapangan;
use App\Models\Lapangan;
use App\Models\Kategori;
use App\Models\SectionLapangan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LapanganController extends Controller
{
    private const MAX_SIMPLE_RANGE_DAYS = 90;
    private const MAX_GENERATED_SLOTS = 500;

    private function ownedLapanganQuery()
    {
        return Lapangan::query()->where('pemilik_id', auth()->id());
    }

    private function findOwnedLapanganOrFail(int $id, array $with = []): Lapangan
    {
        $query = $this->ownedLapanganQuery();

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->findOrFail($id);
    }

    public function index(Request $request)
    {
        $lapangan = $this->ownedLapanganQuery()
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
            'sections' => ['nullable', 'array'],
            'sections.*.nama_section' => ['nullable', 'string', 'max:255'],
            'sections.*.deskripsi' => ['nullable', 'string', 'max:255'],
            'sections.*.harga_per_jam' => ['nullable', 'numeric', 'min:0'],
            'foto.*' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $fotoPaths = [];
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $foto) {
                $fotoPaths[] = $foto->store('lapangan', 'public');
            }
        }

        $kategoriModel = Kategori::find($request->id_kategori);
        $sectionsInput = $request->input('sections', []);

        DB::transaction(function () use ($request, $kategoriModel, $fotoPaths, $sectionsInput) {
            $lapangan = Lapangan::create([
                'pemilik_id' => auth()->id(),
                'id_kategori' => $request->id_kategori,
                'nama_lapangan' => $request->nama_lapangan,
                'kategori' => $kategoriModel?->nama_kategori,
                'lokasi' => $request->lokasi,
                'deskripsi' => $request->deskripsi,
                'status' => $request->input('status', 'standard'),
                'foto' => $fotoPaths,
            ]);

            $this->syncSections($lapangan, $sectionsInput, false);
        });

        return redirect()->route('lapangan.index')->with('success', 'Lapangan berhasil ditambahkan!');
    }

    public function show($id)
    {
        $lapangan = $this->findOwnedLapanganOrFail($id, [
            'jadwal',
            'sections' => function ($query) {
                $query->with([
                    'jadwal' => function ($jadwalQuery) {
                        $jadwalQuery->orderBy('tanggal')->orderBy('jam_mulai');
                    },
                ]);
            },
        ]);

        return view('lapangan.show', compact('lapangan'));
    }

    public function update(Request $request, $id)
    {
        $lapangan = $this->findOwnedLapanganOrFail($id);

        $request->validate([
            'nama_lapangan' => ['required', 'string', 'max:255'],
            'id_kategori' => ['required', 'integer', 'exists:kategori,id'],
            'lokasi' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'sections' => ['nullable', 'array'],
            'sections.*.nama_section' => ['nullable', 'string', 'max:255'],
            'sections.*.deskripsi' => ['nullable', 'string', 'max:255'],
            'foto.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $fotoPaths = $lapangan->foto ?? [];
        if (!is_array($fotoPaths)) {
            $fotoPaths = [];
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

        $kategoriModel = Kategori::find($request->id_kategori);
        $sectionsInput = $request->input('sections', []);

        DB::transaction(function () use ($lapangan, $request, $kategoriModel, $fotoPaths, $sectionsInput) {
            $lapangan->update([
                'nama_lapangan' => $request->nama_lapangan,
                'id_kategori' => $request->id_kategori,
                'kategori' => $kategoriModel?->nama_kategori,
                'lokasi' => $request->lokasi,
                'deskripsi' => $request->deskripsi,
                'status' => $request->input('status', $lapangan->status),
                'foto' => $fotoPaths,
            ]);

            $this->syncSections($lapangan, $sectionsInput, true);
        });

        return redirect()->route('lapangan.index')->with('success', 'Data lapangan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $lapangan = $this->findOwnedLapanganOrFail($id);
        $fotoPaths = $lapangan->foto ?? [];
        if (is_array($fotoPaths)) {
            foreach ($fotoPaths as $foto) {
                Storage::disk('public')->delete($foto);
            }
        }
        $lapangan->jadwal()->delete();
        $lapangan->delete();

        return redirect()->route('lapangan.index')->with('success', 'Lapangan berhasil dihapus!');
    }

    public function getSectionJadwal($lapanganId, $sectionId)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Sesi Anda telah berakhir.', 'jadwal' => []], 401);
        }

        $lapangan = $this->findOwnedLapanganOrFail($lapanganId);
        $section = $lapangan->sections()->with(['jadwal' => function ($query) {
            $query->orderBy('tanggal')->orderBy('jam_mulai');
        }])->where('id', $sectionId)->first();

        if (!$section) {
            return response()->json(['message' => 'Section tidak ditemukan.', 'jadwal' => []], 404);
        }

        $defaultHargaSection = (float) ($section->harga_per_jam ?? 0);
        $jadwalData = $section->jadwal->map(function ($jadwal) use ($defaultHargaSection) {
            return [
                'id' => $jadwal->id,
                'tanggal' => $jadwal->tanggal ? Carbon::parse($jadwal->tanggal)->format('Y-m-d') : null,
                'jam_mulai' => $jadwal->jam_mulai,
                'jam_selesai' => $jadwal->jam_selesai,
                'durasi_sewa' => (int) $jadwal->durasi_sewa,
                'harga_sewa' => (float) $jadwal->harga_sewa,
                'tersedia' => (bool) $jadwal->tersedia,
                'is_default_price' => $defaultHargaSection > 0 && (float) $jadwal->harga_sewa === $defaultHargaSection,
                'section_harga_per_jam' => $defaultHargaSection,
            ];
        })->values();

        return response()->json([
            'section' => [
                'id' => $section->id,
                'nama_section' => $section->nama_section,
                'deskripsi' => $section->deskripsi,
                'harga_per_jam' => $defaultHargaSection,
            ],
            'jadwal' => $jadwalData,
        ]);
    }

    public function storeJadwal(Request $request, $lapanganId)
    {
        $this->findOwnedLapanganOrFail($lapanganId);
        $tipeJadwal = $request->input('tipe_jadwal', 'simple');

        if ($tipeJadwal === 'custom') {
            return $this->storeJadwalCustom($request, $lapanganId);
        }
        return $this->storeJadwalSimple($request, $lapanganId);
    }

    private function storeJadwalCustom(Request $request, $lapanganId)
    {
        $request->validate([
            'section_id' => ['required', 'integer', Rule::exists('section_lapangan', 'id')->where('lapangan_id', $lapanganId)],
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'durasi_sewa' => ['nullable', 'numeric', 'min:0.25', 'max:24'],
            'harga_sewa' => ['nullable', 'numeric', 'min:0'],
            'tersedia' => ['required', 'boolean'],
        ]);

        if ($this->hasJadwalConflict($request->section_id, $request->tanggal, $request->jam_mulai, $request->jam_selesai)) {
            return redirect()->back()->with('error', 'Rentang waktu bertabrakan dengan jadwal lain!');
        }

        try {
            $durasiMenit = $this->resolveDurasiMenit($request->jam_mulai, $request->jam_selesai, $request->input('durasi_sewa'));
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['durasi_sewa' => $e->getMessage()])->withInput();
        }

        $hargaPerJam = $this->resolveHargaPerJam($request->input('harga_sewa'), $request->section_id, $lapanganId);

        JadwalLapangan::create([
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

    private function storeJadwalSimple(Request $request, $lapanganId)
    {
        $hariMapping = ['senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4, 'jumat' => 5, 'sabtu' => 6, 'minggu' => 7];

        $request->validate([
            'section_id' => ['required', 'integer', Rule::exists('section_lapangan', 'id')->where('lapangan_id', $lapanganId)],
            'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'hari_repetisi' => ['required', 'array', 'min:1'],
            'hari_repetisi.*' => [Rule::in(array_keys($hariMapping))],
            'jam_mulai_harian' => ['required', 'date_format:H:i'],
            'jam_selesai_harian' => ['required', 'date_format:H:i', 'after:jam_mulai_harian'],
            'durasi_slot' => ['required', 'integer', 'min:1', 'max:8'],
            'harga_sewa' => ['nullable', 'numeric', 'min:0'],
            'tersedia' => ['required', 'boolean'],
        ]);

        $mulai = Carbon::createFromFormat('Y-m-d', $request->tanggal_mulai);
        $selesai = Carbon::createFromFormat('Y-m-d', $request->tanggal_selesai);

        if ($mulai->diffInDays($selesai) > self::MAX_SIMPLE_RANGE_DAYS) {
            return redirect()->back()->withErrors(['tanggal_selesai' => 'Rentang tanggal maksimal ' . self::MAX_SIMPLE_RANGE_DAYS . ' hari.'])->withInput();
        }

        $slotMenit = $this->convertDurasiJamKeMenit($request->input('durasi_slot'));
        $jamMulaiHarian = Carbon::createFromFormat('H:i', $request->jam_mulai_harian);
        $jamSelesaiHarian = Carbon::createFromFormat('H:i', $request->jam_selesai_harian);

        if ($slotMenit > $jamMulaiHarian->diffInMinutes($jamSelesaiHarian)) {
            return redirect()->back()->withErrors(['durasi_slot' => 'Durasi slot melebihi rentang jam buka dan tutup.'])->withInput();
        }

        $selectedIso = array_unique(array_map(fn($day) => $hariMapping[$day], $request->hari_repetisi));
        $generatedSlots = [];
        $currentDate = $mulai->copy();
        $hargaPerJam = $this->resolveHargaPerJam($request->input('harga_sewa'), $request->section_id, $lapanganId);

        while ($currentDate->lte($selesai)) {
            if (in_array($currentDate->isoWeekday(), $selectedIso, true)) {
                $slotStart = Carbon::createFromFormat('Y-m-d H:i', $currentDate->format('Y-m-d') . ' ' . $request->jam_mulai_harian);
                $closingTime = Carbon::createFromFormat('Y-m-d H:i', $currentDate->format('Y-m-d') . ' ' . $request->jam_selesai_harian);

                while ($slotStart->copy()->addMinutes($slotMenit)->lte($closingTime)) {
                    $slotEnd = $slotStart->copy()->addMinutes($slotMenit);
                    $generatedSlots[] = [
                        'tanggal' => $slotStart->format('Y-m-d'),
                        'jam_mulai' => $slotStart->format('H:i'),
                        'jam_selesai' => $slotEnd->format('H:i'),
                    ];

                    if (count($generatedSlots) >= self::MAX_GENERATED_SLOTS) break 2;
                    $slotStart = $slotEnd->copy();
                }
            }
            $currentDate->addDay();
        }

        if (empty($generatedSlots)) return redirect()->back()->withErrors(['durasi_slot' => 'Tidak ada slot yang dapat dibuat.'])->withInput();

        foreach ($generatedSlots as $slot) {
            if ($this->hasJadwalConflict($request->section_id, $slot['tanggal'], $slot['jam_mulai'], $slot['jam_selesai'])) {
                return redirect()->back()->with('error', 'Bentrok dengan jadwal lain pada ' . $slot['tanggal'] . ' ' . $slot['jam_mulai'])->withInput();
            }
        }

        $payload = array_map(function ($slot) use ($request, $slotMenit, $hargaPerJam) {
            return array_merge($slot, [
                'section_id' => $request->section_id,
                'durasi_sewa' => $slotMenit,
                'harga_sewa' => $hargaPerJam,
                'tersedia' => $request->tersedia,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, $generatedSlots);

        JadwalLapangan::insert($payload);
        return redirect()->back()->with('success', 'Berhasil membuat ' . count($generatedSlots) . ' slot otomatis!');
    }

    public function updateJadwal(Request $request, $lapanganId, $jadwalId)
    {
        $this->findOwnedLapanganOrFail($lapanganId);
        $jadwal = JadwalLapangan::where('id', $jadwalId)
            ->whereHas('section', function ($query) use ($lapanganId) {
                $query->where('lapangan_id', $lapanganId);
            })->firstOrFail();

        // [BARU] Validasi status
        if (!$jadwal->tersedia) {
            return redirect()->back()->with('error', 'Gagal edit! Jadwal sudah terisi (booked).');
        }

        $request->validate([
            'section_id' => ['required', 'integer', Rule::exists('section_lapangan', 'id')->where('lapangan_id', $lapanganId)],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'durasi_sewa' => ['nullable', 'numeric', 'min:0.25', 'max:24'],
            'harga_sewa' => ['nullable', 'numeric', 'min:0'],
            'tersedia' => ['required', 'boolean'],
        ]);

        if ($this->hasJadwalConflict($request->section_id, $request->tanggal, $request->jam_mulai, $request->jam_selesai, $jadwalId)) {
            return redirect()->back()->with('error', 'Rentang waktu bertabrakan dengan jadwal lain!');
        }

        try {
            $durasiMenit = $this->resolveDurasiMenit($request->jam_mulai, $request->jam_selesai, $request->input('durasi_sewa'));
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['durasi_sewa' => $e->getMessage()])->withInput();
        }

        $hargaPerJamUpdate = $this->resolveHargaPerJam($request->input('harga_sewa'), $request->section_id, $lapanganId, $jadwal->section?->lapangan?->harga_sewa);

        $jadwal->update([
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'durasi_sewa' => $durasiMenit,
            'harga_sewa' => $hargaPerJamUpdate,
            'tersedia' => $request->tersedia,
            'section_id' => $request->section_id,
        ]);

        return redirect()->back()->with('success', 'Jadwal berhasil diperbarui!');
    }

    public function destroyJadwal(Request $request, $lapanganId, $jadwalId = null)
    {
        $this->findOwnedLapanganOrFail($lapanganId);
        $jadwalId = $jadwalId ?? $request->input('jadwal_id');

        if (!$jadwalId) return redirect()->back()->with('error', 'ID jadwal tidak ditemukan.');

        $jadwal = JadwalLapangan::where('id', $jadwalId)
            ->whereHas('section', function ($query) use ($lapanganId) {
                $query->where('lapangan_id', $lapanganId);
            })->firstOrFail();

        // [BARU] Validasi status
        if (!$jadwal->tersedia) {
            return redirect()->back()->with('error', 'Gagal menghapus! Jadwal sudah terisi (booked).');
        }

        $jadwal->delete();
        return redirect()->back()->with('success', 'Jadwal berhasil dihapus!');
    }

    // ================== METHOD BARU UNTUK MODAL ================== //

    /**
     * Update harga jadwal dari modal (hanya harga_sewa)
     */
    public function updateHargaJadwal(Request $request, $id)
    {
        $jadwal = JadwalLapangan::with('section.lapangan')->findOrFail($id);

        // Otorisasi: Pastikan pemilik lapangan sama dengan user login
        if ($jadwal->section->lapangan->pemilik_id !== auth()->id()) {
            abort(403, 'Aksi tidak diizinkan.');
        }

        // [BARU] Validasi status
        if (!$jadwal->tersedia) {
            return redirect()->back()->with('error', 'Gagal edit! Jadwal sudah terisi (booked).');
        }

        $request->validate([
            'harga_sewa' => ['required', 'numeric', 'min:0'],
        ]);

        $jadwal->update([
            'harga_sewa' => $request->harga_sewa
        ]);

        return redirect()->back()->with('success', 'Harga jadwal berhasil diperbarui!');
    }

    /**
     * Hapus jadwal single dari modal
     */
    public function destroyJadwalSingle($id)
    {
        $jadwal = JadwalLapangan::with('section.lapangan')->findOrFail($id);

        // Otorisasi
        if ($jadwal->section->lapangan->pemilik_id !== auth()->id()) {
            abort(403, 'Aksi tidak diizinkan.');
        }

        // [BARU] Validasi status tersedia
        if (!$jadwal->tersedia) {
            return redirect()->back()->with('error', 'Gagal menghapus! Jadwal sudah terisi (booked).');
        }

        $jadwal->delete();

        return redirect()->back()->with('success', 'Jadwal berhasil dihapus!');
    }

    // ================== END METHOD BARU ================== //

    private function hasJadwalConflict(int $sectionId, string $tanggal, string $jamMulai, string $jamSelesai, ?int $ignoreJadwalId = null): bool
    {
        return JadwalLapangan::when($ignoreJadwalId, fn($query) => $query->where('id', '!=', $ignoreJadwalId))
            ->where('section_id', $sectionId)
            ->where('tanggal', $tanggal)
            ->where(fn($query) => $query->where('jam_mulai', '<', $jamSelesai)->where('jam_selesai', '>', $jamMulai))
            ->exists();
    }

    private function resolveHargaPerJam($inputHarga, int $sectionId, int $lapanganId, ?float $lapanganFallback = null): float
    {
        if ($inputHarga !== null && $inputHarga !== '' && (float) $inputHarga > 0) return (float) $inputHarga;
        $sectionDefault = SectionLapangan::where('id', $sectionId)->value('harga_per_jam');
        if (!is_null($sectionDefault) && $sectionDefault > 0) return (float) $sectionDefault;
        return (float) ($lapanganFallback ?? Lapangan::where('id', $lapanganId)->value('harga_sewa') ?? 0);
    }

    private function resolveDurasiMenit(string $jamMulai, string $jamSelesai, $durasiInput): int
    {
        $jamMulaiCarbon = Carbon::createFromFormat('H:i', $jamMulai);
        $jamSelesaiCarbon = Carbon::createFromFormat('H:i', $jamSelesai);
        $rentangMenit = $jamMulaiCarbon->diffInMinutes($jamSelesaiCarbon);
        $durasiMenit = $this->convertDurasiJamKeMenit($durasiInput);

        if (empty($durasiMenit) || $durasiMenit <= 0) $durasiMenit = max(1, $rentangMenit);
        if (abs($rentangMenit - $durasiMenit) > 1) throw new \InvalidArgumentException('Durasi harus sesuai dengan selisih Jam Mulai dan Jam Selesai.');

        return $durasiMenit;
    }

    private function sanitizeSectionData(?array $section): ?array
    {
        if (!is_array($section)) return null;
        $nama = trim($section['nama_section'] ?? '');
        if ($nama === '') return null;
        return [
            'nama_section' => $nama,
            'deskripsi' => !empty(trim($section['deskripsi'] ?? '')) ? trim($section['deskripsi']) : null,
            'harga_per_jam' => $this->normalizeCurrencyValue($section['harga_per_jam'] ?? null),
        ];
    }

    private function syncSections(Lapangan $lapangan, array $sectionsInput, bool $replaceExisting = true): void
    {
        $processedIds = [];
        foreach ($sectionsInput as $key => $sectionRaw) {
            $sectionData = $this->sanitizeSectionData($sectionRaw);
            if (!$sectionData) continue;
            if (is_null($sectionData['harga_per_jam'])) $sectionData['harga_per_jam'] = $lapangan->harga_sewa;

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
                'harga_per_jam' => $lapangan->harga_sewa,
            ]);
            $processedIds[] = $default->id;
        }

        if ($replaceExisting) $lapangan->sections()->whereNotIn('id', $processedIds)->delete();
    }

    private function normalizeCurrencyValue($value): ?float
    {
        if ($value === null || $value === '') return null;
        if (is_numeric($value)) return max(0, (float) $value);
        $clean = preg_replace('/[^\d,\.]/', '', (string) $value);
        if ($clean === '') return null;
        $clean = str_replace(',', '.', $clean);
        return is_numeric($clean) ? max(0, (float) $clean) : null;
    }

    private function convertDurasiJamKeMenit($input): ?int
    {
        if (is_null($input) || $input === '') return null;
        $numeric = (float) str_replace(',', '.', (string) $input);
        if (!is_finite($numeric) || $numeric <= 0) return null;
        return max(1, (int) round($numeric * 60));
    }
}
