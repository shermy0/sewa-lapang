<?php

namespace App\Http\Controllers;

use App\Models\BandingPemilik;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BandingPemilikController extends Controller
{
    public function create(Request $request): View
    {
        return view('pemilik.banding.form', [
            'prefillEmail' => $request->query('email'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'alasan' => ['required', 'string', 'min:20'],
            'lampiran' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $pemilik = User::where('email', $validated['email'])
            ->where('role', 'pemilik')
            ->first();

        if (! $pemilik) {
            return back()->withErrors([
                'email' => 'Email tidak terdaftar sebagai akun pemilik.',
            ])->onlyInput('email');
        }

        if ($pemilik->status !== 'nonaktif') {
            return back()->withErrors([
                'email' => 'Akun ini belum diblokir sehingga tidak memerlukan banding.',
            ])->onlyInput('email');
        }

        $sudahMengajukan = BandingPemilik::where('pemilik_id', $pemilik->id)
            ->where('status', 'pending')
            ->exists();

        if ($sudahMengajukan) {
            return back()->withErrors([
                'email' => 'Banding sebelumnya masih diproses. Harap menunggu tindak lanjut admin.',
            ])->onlyInput('email');
        }

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('banding_lampiran', 'public');
        }

        BandingPemilik::create([
            'pemilik_id' => $pemilik->id,
            'alasan' => $validated['alasan'],
            'lampiran_path' => $lampiranPath,
        ]);

        return redirect()
            ->route('banding.create', ['email' => $pemilik->email])
            ->with('success', 'Banding berhasil diajukan. Kami akan menghubungi Anda setelah proses peninjauan.');
    }
}
