<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const ROLES = ['admin', 'pemilik', 'penyewa'];
    private const STATUSES = ['aktif', 'nonaktif'];

    public function index(Request $request)
    {
        $query = User::query()->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $users = $query->paginate(10)->appends($request->query());

        return view('admin.users.index', [
            'users' => $users,
            'roles' => self::ROLES,
            'statuses' => self::STATUSES,
        ]);
    }

    public function create()
    {
        return view('admin.users.create', [
            'roles' => self::ROLES,
            'statuses' => self::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['required', Rule::in(self::ROLES)],
            'status' => ['required', Rule::in(self::STATUSES)],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'foto_profil' => ['nullable', 'image', 'max:2048'],
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto_profil')) {
            $fotoPath = $request->file('foto_profil')->store('profile_photos', 'public');
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status' => $validated['status'],
            'no_hp' => $validated['no_hp'] ?? null,
            'foto_profil' => $fotoPath,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    public function show(User $user)
    {
        return view('admin.users.show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => self::ROLES,
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(self::ROLES)],
            'status' => ['required', Rule::in(self::STATUSES)],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'foto_profil' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($user->id === auth()->id() && $validated['status'] === 'nonaktif') {
            return back()
                ->withInput()
                ->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'status' => $validated['status'],
            'no_hp' => $validated['no_hp'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        if ($request->hasFile('foto_profil')) {
            $this->deleteProfilePhoto($user);
            $data['foto_profil'] = $request->file('foto_profil')->store('profile_photos', 'public');
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function updateStatus(Request $request, User $user)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa mengubah status akun sendiri.');
        }

        $user->update(['status' => $validated['status']]);

        return back()->with('success', 'Status pengguna diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun yang sedang digunakan.');
        }

        $this->deleteProfilePhoto($user);

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    private function deleteProfilePhoto(User $user): void
    {
        if (!$user->foto_profil) {
            return;
        }

        if (filter_var($user->foto_profil, FILTER_VALIDATE_URL)) {
            return;
        }

        Storage::disk('public')->delete($user->foto_profil);
    }
}
