<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    /**
     * Tampilkan halaman pengaturan akun admin.
     */
    public function edit(Request $request)
    {
        return view('admin.account.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Perbarui profil admin yang sedang login.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'foto_profil' => ['nullable', 'image', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
            'current_password' => ['required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'no_hp' => $validated['no_hp'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        if ($request->boolean('remove_photo')) {
            $this->deleteProfilePhoto($user->foto_profil);
            $data['foto_profil'] = null;
        }

        if ($request->hasFile('foto_profil')) {
            $this->deleteProfilePhoto($user->foto_profil);
            $data['foto_profil'] = $request->file('foto_profil')->store('profile_photos', 'public');
        }

        $user->update($data);

        return redirect()
            ->route('admin.account.edit')
            ->with('success', 'Pengaturan akun berhasil diperbarui.');
    }

    private function deleteProfilePhoto(?string $path): void
    {
        if (!$path || filter_var($path, FILTER_VALIDATE_URL)) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
