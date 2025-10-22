@extends('layouts.admin')

@section('title', 'Pengaturan Akun')

@php
    use Illuminate\Support\Facades\Storage;

    $placeholderAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=41A67E&color=fff';
    $photoUrl = $user->foto_profil
        ? (filter_var($user->foto_profil, FILTER_VALIDATE_URL)
            ? $user->foto_profil
            : Storage::disk('public')->url($user->foto_profil))
        : $placeholderAvatar;
@endphp

@section('content')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">

<div class="container py-4">
    <h3 class="mb-4 fw-bold text-primary-custom">Pengaturan Akun</h3>

    @if (session('success'))
        <div class="alert alert-success shadow-sm text-center">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-lg border-0 p-4">
        <form action="{{ route('admin.account.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="text-center mb-4">
                <img
                    id="previewFoto"
                    src="{{ $photoUrl }}"
                    data-original="{{ $photoUrl }}"
                    data-placeholder="{{ $placeholderAvatar }}"
                    alt="Foto Profil"
                    class="rounded-circle border border-3 border-primary-custom shadow-sm object-fit-cover"
                    width="130"
                    height="130"
                >
            </div>

            <div class="text-center mb-4">
                <label for="foto_profil" class="btn btn-edit-foto px-3 py-2">
                    <i class="fa-solid fa-camera me-1"></i> Ganti Foto
                </label>
                <input type="file" id="foto_profil" name="foto_profil" accept="image/*" class="d-none">

                @if ($user->foto_profil)
                    <button type="button" id="removePhotoBtn" class="btn btn-outline-danger ms-2 px-3 py-2">
                        <i class="fa-solid fa-trash-can me-1"></i> Hapus Foto
                    </button>
                @endif

                <input type="hidden" id="remove_photo" name="remove_photo" value="{{ old('remove_photo', '0') }}">
                @error('foto_profil')
                    <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary-custom">Nama Lengkap</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            class="form-control shadow-sm @error('name') is-invalid @enderror"
                            required
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary-custom">Email</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            class="form-control shadow-sm @error('email') is-invalid @enderror"
                            required
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary-custom">No HP</label>
                        <input
                            type="text"
                            name="no_hp"
                            value="{{ old('no_hp', $user->no_hp) }}"
                            class="form-control shadow-sm @error('no_hp') is-invalid @enderror"
                            placeholder="Contoh: 0812xxxxxxx"
                        >
                        @error('no_hp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary-custom">Role</label>
                        <input type="text" class="form-control shadow-sm" value="{{ ucfirst($user->role) }}" disabled>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="row">
                <div class="col-md-12">
                    <h6 class="fw-semibold text-secondary-custom mb-3">Ganti Password (Opsional)</h6>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Password Saat Ini</label>
                        <input
                            type="password"
                            name="current_password"
                            class="form-control shadow-sm bg-light @error('current_password') is-invalid @enderror"
                            placeholder="Masukkan password saat ini"
                        >
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Password Baru</label>
                        <input
                            type="password"
                            name="password"
                            class="form-control shadow-sm @error('password') is-invalid @enderror"
                            placeholder="Minimal 8 karakter"
                        >
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Konfirmasi Password Baru</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            class="form-control shadow-sm"
                            placeholder="Ulangi password baru"
                        >
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <button class="btn btn-save px-4 py-2">
                    <i class="fa-solid fa-check-circle me-1"></i> Simpan Profil
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const photoInput = document.getElementById('foto_profil');
    const previewImg = document.getElementById('previewFoto');
    const removePhotoBtn = document.getElementById('removePhotoBtn');
    const removePhotoInput = document.getElementById('remove_photo');
    const placeholder = previewImg ? previewImg.dataset.placeholder : '';
    const original = previewImg ? previewImg.dataset.original : placeholder;

    if (photoInput) {
        photoInput.addEventListener('change', (event) => {
            const [file] = event.target.files;
            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                if (previewImg) {
                    previewImg.src = e.target.result;
                }
            };
            reader.readAsDataURL(file);
            if (removePhotoInput) {
                removePhotoInput.value = '0';
            }
        });
    }

    if (removePhotoBtn && removePhotoInput && previewImg) {
        removePhotoBtn.addEventListener('click', () => {
            const isMarked = removePhotoInput.value === '1';
            if (isMarked) {
                removePhotoInput.value = '0';
                previewImg.src = original;
                removePhotoBtn.classList.remove('btn-danger');
                removePhotoBtn.classList.add('btn-outline-danger');
                removePhotoBtn.innerHTML = '<i class="fa-solid fa-trash-can me-1"></i> Hapus Foto';
            } else {
                removePhotoInput.value = '1';
                previewImg.src = placeholder;
                removePhotoBtn.classList.remove('btn-outline-danger');
                removePhotoBtn.classList.add('btn-danger');
                removePhotoBtn.innerHTML = '<i class="fa-solid fa-arrow-rotate-left me-1"></i> Batal Hapus';
                if (photoInput) {
                    photoInput.value = '';
                }
            }
        });
    }
});
</script>
@endpush
