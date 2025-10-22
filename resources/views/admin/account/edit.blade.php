@extends('layouts.admin')

@section('title', 'Pengaturan Akun')

@php
    use Illuminate\Support\Facades\Storage;

    $photoUrl = $user->foto_profil
        ? (filter_var($user->foto_profil, FILTER_VALIDATE_URL)
            ? $user->foto_profil
            : Storage::disk('public')->url($user->foto_profil))
        : asset('images/profile.jpg');
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h4 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-user-gear me-2 text-success"></i> Pengaturan Akun
            </h4>
            <p class="text-muted mb-0">Perbarui informasi profil dan kredensial admin Anda.</p>
        </div>
    </div>

    <form action="{{ route('admin.account.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row g-4 align-items-start">
            <div class="col-lg-4 col-md-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0">
                        <h6 class="fw-semibold mb-0">Foto Profil</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="d-flex flex-column align-items-center">
                            <img src="{{ $photoUrl }}" alt="Foto Profil" class="rounded-circle mb-3 shadow-sm" width="128" height="128" id="profilePreview" data-original="{{ $photoUrl }}">
                            <p class="text-muted small mb-3">
                                Format JPG, JPEG, atau PNG &mdash; ukuran maksimal 2 MB.
                            </p>
                            <div class="d-grid gap-2 w-100">
                                <label for="foto_profil" class="btn btn-outline-success">
                                    <i class="fa-solid fa-upload me-1"></i> Unggah Foto Baru
                                </label>
                                <button type="button" class="btn btn-outline-danger" id="removePhotoBtn">
                                    <i class="fa-solid fa-trash-can me-1"></i> Hapus Foto
                                </button>
                                <input type="hidden" name="remove_photo" id="remove_photo" value="{{ old('remove_photo') ? '1' : '0' }}">
                                <small id="removePhotoHint" class="text-danger {{ old('remove_photo') ? '' : 'd-none' }}">
                                    Foto akan dihapus setelah menyimpan perubahan.
                                </small>
                                <input type="file" name="foto_profil" id="foto_profil" class="d-none" accept="image/*">
                                @error('foto_profil')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 col-md-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">Informasi Profil</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="no_hp" class="form-label">Nomor HP</label>
                                <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp', $user->no_hp) }}" class="form-control @error('no_hp') is-invalid @enderror" placeholder="Contoh: 0812xxxxxxx">
                                @error('no_hp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="{{ ucfirst($user->role) }}" disabled>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-semibold mb-3">Ganti Password</h6>
                        <p class="text-muted small mb-4">
                            Isi password baru bila ingin menggantinya. Pastikan memasukkan password saat ini untuk verifikasi.
                        </p>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6 col-lg-5">
                                <label for="current_password" class="form-label">Password Saat Ini</label>
                                <input type="password" name="current_password" id="current_password" class="form-control bg-light @error('current_password') is-invalid @enderror" placeholder="Masukkan password saat ini">
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="password" class="form-label">Password Baru</label>
                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 8 karakter">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 d-flex justify-content-end gap-2">
                        <a href="{{ route('dashboard.admin') }}" class="btn btn-light border">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const photoInput = document.getElementById('foto_profil');
    const previewImg = document.getElementById('profilePreview');
    const removePhotoInput = document.getElementById('remove_photo');
    const removePhotoBtn = document.getElementById('removePhotoBtn');
    const removePhotoHint = document.getElementById('removePhotoHint');
    const defaultPhoto = "{{ asset('images/profile.jpg') }}";
    let latestPhoto = previewImg ? (previewImg.dataset.original || previewImg.src) : defaultPhoto;

    const setRemoveState = (active) => {
        if (!removePhotoInput || !removePhotoBtn) {
            return;
        }

        removePhotoInput.value = active ? '1' : '0';
        removePhotoBtn.classList.toggle('btn-outline-danger', !active);
        removePhotoBtn.classList.toggle('btn-danger', active);

        if (removePhotoHint) {
            removePhotoHint.classList.toggle('d-none', !active);
        }

        if (previewImg) {
            previewImg.src = active ? defaultPhoto : latestPhoto;
        }

        if (active && photoInput) {
            photoInput.value = '';
        }
    };

    if (photoInput) {
        photoInput.addEventListener('change', (event) => {
            const [file] = event.target.files;
            if (!file) {
                return;
            }

            latestPhoto = URL.createObjectURL(file);
            if (previewImg) {
                previewImg.src = latestPhoto;
            }
            setRemoveState(false);
        });
    }

    if (removePhotoBtn) {
        removePhotoBtn.addEventListener('click', () => {
            const isActive = removePhotoInput?.value === '1';
            setRemoveState(!isActive);
        });
    }

    setRemoveState(removePhotoInput?.value === '1');
</script>
@endpush
