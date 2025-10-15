@extends('layouts.admin')

@section('title', 'Kelola Pengguna')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h4 class="fw-bold text-dark mb-0">
                <i class="fa-solid fa-users-gear me-2 text-primary"></i> Manajemen Pengguna
            </h4>
            <p class="text-muted mb-0">Kelola akun admin, pemilik, dan penyewa dalam satu tempat.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-user-plus me-1"></i> Tambah Pengguna
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form class="row g-2 align-items-center mb-3" method="GET">
                <div class="col-lg-4 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="Cari nama atau email">
                    </div>
                </div>
                <div class="col-lg-3 col-md-4">
                    <select name="role" class="form-select">
                        <option value="">Semua Role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-4">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-filter me-1"></i> Terapkan
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light border">
                        <i class="fa-solid fa-rotate me-1"></i> Reset
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>No HP</th>
                            <th>Bergabung</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td><span class="badge bg-light text-dark text-uppercase">{{ $user->role }}</span></td>
                                <td>
                                    <span class="badge {{ $user->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td>{{ $user->no_hp ?? '-' }}</td>
                                <td>{{ optional($user->created_at)->format('d M Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fa-solid fa-eye me-1"></i> Detail
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                    </a>
                                    @if ($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.update-status', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $user->status === 'aktif' ? 'nonaktif' : 'aktif' }}">
                                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                                <i class="fa-solid fa-ban me-1"></i>
                                                {{ $user->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fa-solid fa-trash-can me-1"></i> Hapus
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada data pengguna.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
