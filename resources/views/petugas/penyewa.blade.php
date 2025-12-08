@extends('layouts.master')

@section('title', 'Penyewa')

@section('content')
@push('styles')
<style>
    .page-hero {
        background: linear-gradient(135deg, #41a67e 0%, #2f8d6a 100%);
        color: #fff;
        border-radius: 16px;
        padding: 20px 24px;
        box-shadow: 0 10px 30px rgba(65, 166, 126, 0.25);
    }
    .page-hero .title {
        font-weight: 800;
        letter-spacing: 0.3px;
    }
    .section-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 12px 30px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .section-card .card-header {
        background: #f7fdf9;
        border-bottom: 1px solid #e4f2ea;
        font-weight: 700;
        letter-spacing: 0.2px;
    }
    .table th {
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
    .empty-state {
        padding: 28px 0;
        color: #6c757d;
    }
</style>
@endpush

<div class="container py-4">
    <div class="page-hero d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
        <div>
            <div class="title fs-4 mb-1">Daftar Penyewa</div>
            <div class="small text-white-75">Kelola data penyewa dengan cepat dan rapi.</div>
        </div>
        <div class="d-flex align-items-center gap-2 mt-3 mt-md-0">
            <span class="badge bg-light text-success fw-semibold px-3 py-2">
                Total: {{ $penyewa->count() }} penyewa
            </span>
        </div>
    </div>

    {{-- Pesan sukses --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Pesan error --}}
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Validasi error --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        {{-- Form tambah penyewa --}}
        <div class="col-lg-4">
            <div class="card section-card h-100">
                <div class="card-header">Tambah Penyewa Baru</div>
                <div class="card-body">
                    <form action="{{ route('petugas.penyewa.store') }}" method="POST" class="d-grid gap-3">
                        @csrf
                        <div>
                            <label class="form-label fw-semibold">Nama</label>
                            <input type="text" name="name" class="form-control form-control-lg" required value="{{ old('name') }}" placeholder="Masukkan nama penyewa">
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control form-control-lg" required value="{{ old('email') }}" placeholder="Email aktif">
                        </div>
                        <button type="submit" class="btn btn-success btn-lg fw-semibold">
                            <i class="fa-solid fa-user-plus me-2"></i>Tambah Penyewa
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Tabel penyewa --}}
        <div class="col-lg-8">
            <div class="card section-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Penyewa Terdaftar</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($penyewa as $index => $p)
                                    <tr>
                                        <td class="fw-semibold">{{ $index + 1 }}</td>
                                        <td>{{ $p->name }}</td>
                                        <td class="text-muted">{{ $p->email }}</td>
                                        <td>
                                            <form action="{{ route('petugas.penyewa.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus penyewa ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="fa fa-trash me-1"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center empty-state">
                                            <i class="fa-regular fa-address-card fa-lg me-2"></i>Belum ada penyewa
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
