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

class LapanganController extends Controller
{
    private const MAX_SIMPLE_RANGE_DAYS = 90;
    private const MAX_GENERATED_SLOTS = 500;

    // List lapangan + kategori filter
    public function index(Request $request)
    {
        $userId = auth()->id();

        $lapangan = Lapangan::with('jadwal')
            ->where('pemilik_id', $userId)
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('nama_lapangan', 'like', '%' . $request->search . '%')
                      ->orWhere('lokasi', 'like', '%' . $request->search . '%');
                });
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

        // Ambil semua kategori milik user, bukan hanya yang sudah punya lapangan
        $kategori = Kategori::where('pemilik_id', $userId)
                            ->orderBy('nama_kategori')
                            ->get();

        return view('lapangan.index', compact('lapangan', 'kategori'));
    }

    // Tampil form tambah
    public function create()
    {
        $kategori = Kategori::where('pemilik_id', auth()->id())
                            ->orderBy('nama_kategori')
                            ->get();

        return view('pemilik.lapangan.create', compact('kategori'));
    }

    // Simpan lapangan
    public function store(Request $request)
    {
        $request->validate([
            'nama_lapangan' => 'required|string|max:255',
            'id_kategori' => 'required|integer|exists:kategori,id',
            'lokasi' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'sections' => 'nullable|array',
            'sections.*.nama_section' => 'nullable|string|max:255',
            'sections.*.deskripsi' => 'nullable|string|max:255',
            'sections.*.harga_per_jam' => 'nullable|numeric|min:0',
            'foto.*' => 'required|image|mimes:jpeg,png,jpg|max:2048',
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
                'kategori' => $kategoriModel?->nama_kategori,
                'nama_lapangan' => $request->nama_lapangan,
                'lokasi' => $request->lokasi,
                'deskripsi' => $request->deskripsi,
                'status' => $request->input('status', 'standard'),
                'foto' => $fotoPaths,
            ]);

            $this->syncSections($lapangan, $sectionsInput, false);
        });

        return redirect()->route('lapangan.index')->with('success', 'Lapangan berhasil ditambahkan!');
    }

    // Helper: sanitasi section
    private function sanitizeSectionData(?array $section): ?array
    {
        if (!is_array($section)) return null;

        $nama = trim($section['nama_section'] ?? '');
        if ($nama === '') return null;

        $deskripsi = trim($section['deskripsi'] ?? '');
        $hargaPerJam = $this->normalizeCurrencyValue($section['harga_per_jam'] ?? null);

        return [
            'nama_section' => $nama,
            'deskripsi' => $deskripsi !== '' ? $deskripsi : null,
            'harga_per_jam' => $hargaPerJam,
        ];
    }

    // Helper: simpan / update sections
    private function syncSections(Lapangan $lapangan, array $sectionsInput, bool $replaceExisting = true)
    {
        $processedIds = [];

        foreach ($sectionsInput as $key => $sectionRaw) {
            $sectionData = $this->sanitizeSectionData($sectionRaw);
            if (!$sectionData) continue;

            if (is_null($sectionData['harga_per_jam'])) {
                $sectionData['harga_per_jam'] = $lapangan->harga_sewa ?? 0;
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
                'harga_per_jam' => $lapangan->harga_sewa ?? 0,
            ]);
            $processedIds[] = $default->id;
        }

        if ($replaceExisting) {
            $lapangan->sections()->whereNotIn('id', $processedIds)->delete();
        }
    }

    private function normalizeCurrencyValue($value): ?float
    {
        if ($value === null || $value === '') return null;
        $clean = preg_replace('/[^\d,\.]/', '', (string)$value);
        $clean = str_replace(',', '.', $clean);
        return is_numeric($clean) ? max(0, (float)$clean) : null;
    }
}
