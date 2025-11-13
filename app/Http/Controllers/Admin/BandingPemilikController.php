<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BandingPemilik;
use App\Models\Lapangan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class BandingPemilikController extends Controller
{
    public function index(Request $request): View
    {
        $bandingList = BandingPemilik::with(['pemilik'])
            ->when($request->input('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        return view('admin.banding.index', [
            'bandingList' => $bandingList,
            'statuses' => BandingPemilik::STATUSES,
        ]);
    }

    public function show(BandingPemilik $bandingPemilik): View
    {
        $bandingPemilik->load(['pemilik', 'penangan']);

        return view('admin.banding.show', [
            'banding' => $bandingPemilik,
            'statuses' => BandingPemilik::STATUSES,
        ]);
    }

    public function update(Request $request, BandingPemilik $bandingPemilik): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['diterima', 'ditolak'])],
            'tanggapan_admin' => ['nullable', 'string', 'max:2000'],
        ]);

        $updateData = [
            'status' => $validated['status'],
            'tanggapan_admin' => $validated['tanggapan_admin'] ?? null,
            'ditangani_oleh' => $request->user()->id,
            'ditangani_pada' => now(),
        ];

        $bandingPemilik->update($updateData);

        if ($validated['status'] === 'diterima') {
            $this->restorePemilik($bandingPemilik);
        }

        return redirect()
            ->route('admin.banding.show', $bandingPemilik)
            ->with('success', 'Banding berhasil diperbarui.');
    }

    public function lampiran(BandingPemilik $bandingPemilik)
    {
        if (! $bandingPemilik->lampiran_path || ! Storage::disk('public')->exists($bandingPemilik->lampiran_path)) {
            abort(404);
        }

        $mime = Storage::disk('public')->mimeType($bandingPemilik->lampiran_path);

        $inlineTypes = ['image/jpeg', 'image/png', 'image/gif'];

        if (in_array($mime, $inlineTypes, true)) {
            $base64 = base64_encode(Storage::disk('public')->get($bandingPemilik->lampiran_path));
            return view('admin.banding.preview-lampiran', [
                'banding' => $bandingPemilik,
                'dataUri' => "data:{$mime};base64,{$base64}",
            ]);
        }

        return Storage::disk('public')->download($bandingPemilik->lampiran_path);
    }

    protected function restorePemilik(BandingPemilik $banding): void
    {
        $pemilik = $banding->pemilik;

        if (! $pemilik) {
            return;
        }

        if ($pemilik->status !== 'aktif') {
            $pemilik->update(['status' => 'aktif']);
        }

        Lapangan::where('pemilik_id', $pemilik->id)->update(['is_suspended' => false]);
    }
}
