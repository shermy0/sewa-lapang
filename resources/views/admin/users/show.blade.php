@extends('layouts.admin')

@section('title', 'Detail Pengguna')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 d-flex align-items-center">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-light border me-3">
                        <i class="fa-solid fa-arrow-left-long"></i>
                    </a>
                    <div>
                        <h5 class="fw-bold mb-0">Detail Pengguna</h5>
                        <small class="text-muted">Informasi lengkap akun pengguna.</small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="{{ $user->foto_profil ? (filter_var($user->foto_profil, FILTER_VALIDATE_URL) ? $user->foto_profil : asset('storage/' . $user->foto_profil)) : asset('images/profile.jpg') }}"
                            alt="Foto Profil" class="rounded-circle border mb-3" width="96" height="96" style="object-fit: cover;">
                        <h5 class="fw-bold mb-0">{{ $user->name }}</h5>
                        <div class="d-flex justify-content-center gap-2 mt-2">
                            <span class="badge bg-primary text-uppercase">{{ $user->role }}</span>
                            <span class="badge {{ $user->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="list-group list-group-flush mb-4">
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">Email</span>
                            <span>{{ $user->email }}</span>
                        </div>
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">Nomor HP</span>
                            <span>{{ $user->no_hp ?? '-' }}</span>
                        </div>
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">Tgl. Bergabung</span>
                            <span>{{ optional($user->created_at)->format('d M Y H:i') }}</span>
                        </div>
                        <div class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">Terakhir Diperbarui</span>
                            <span>{{ optional($user->updated_at)->diffForHumans() }}</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-light border">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
