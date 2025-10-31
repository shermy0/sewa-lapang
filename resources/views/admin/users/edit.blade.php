@extends('layouts.admin')

@section('title', 'Edit Pengguna')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 d-flex align-items-center">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-light border me-3">
                        <i class="fa-solid fa-arrow-left-long"></i>
                    </a>
                    <div>
                        <h5 class="fw-bold mb-0">Edit Pengguna</h5>
                        <small class="text-muted">Perbarui data akun, peran, dan status pengguna.</small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <img src="{{ $user->foto_profil ? (filter_var($user->foto_profil, FILTER_VALIDATE_URL) ? $user->foto_profil : asset('storage/' . $user->foto_profil)) : asset('images/profile.jpg') }}"
                            alt="Foto Profil" class="rounded-circle border" width="72" height="72" style="object-fit: cover;">
                        <div>
                            <h6 class="mb-0">{{ $user->name }}</h6>
                            <small class="text-muted text-uppercase">{{ $user->role }}</small>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                class="form-control @error('name') is-invalid @enderror">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                class="form-control @error('email') is-invalid @enderror">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nomor HP</label>
                            <input type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}"
                                class="form-control @error('no_hp') is-invalid @enderror" placeholder="Opsional">
                            @error('no_hp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Role</label>
                                    <select name="role" class="form-select @error('role') is-invalid @enderror">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>
                                                {{ ucfirst($role) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status Akun</label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status }}" @selected(old('status', $user->status) === $status)>
                                                {{ ucfirst($status) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                      
                        <div class="mb-4">
                            <label class="form-label">Foto Profil</label>
                            <input type="file" name="foto_profil" accept="image/*"
                                class="form-control @error('foto_profil') is-invalid @enderror">
                            <small class="text-muted d-block mt-1">Format JPG/PNG maksimal 2MB. Biarkan kosong untuk mempertahankan foto lama.</small>
                            @error('foto_profil')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
