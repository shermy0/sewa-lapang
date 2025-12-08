@extends('layouts.sidebar')

@section('title', 'Petugas')

@push('styles')
<style>
    .petugas-page {
        max-width: 1120px;
        margin: 0 auto;
    }

    .petugas-hero {
        background: linear-gradient(135deg, #f3fbf6 0%, #eef3ff 100%);
        border: 1px solid #e4edf2;
        border-radius: 18px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        box-shadow: 0 12px 36px rgba(65, 166, 126, 0.12);
        position: relative;
        overflow: hidden;
    }

    .petugas-hero::after {
        content: '';
        position: absolute;
        width: 180px;
        height: 180px;
        background: radial-gradient(circle, rgba(65, 166, 126, 0.14) 0%, rgba(65, 166, 126, 0) 65%);
        top: -40px;
        right: -60px;
    }

    .hero-eyebrow {
        font-size: 12px;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #5b6b65;
        margin-bottom: 6px;
        font-weight: 700;
    }

    .hero-title {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #fff;
        color: #2e3a35;
        border: 1px solid #dbe7e3;
        border-radius: 999px;
        padding: 8px 14px;
        font-weight: 600;
        font-size: 13px;
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.04);
    }

    .stat-pill.soft {
        background: #f1f4f9;
        border-color: #e2e8f0;
        color: #536170;
        box-shadow: none;
    }

    .highlight-card {
        background: #fff;
        border: 1px dashed #d2e6db;
        border-radius: 14px;
        padding: 14px 16px;
        min-width: 260px;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.06);
        position: relative;
        z-index: 1;
    }

    .petugas-alert {
        border-radius: 12px;
        padding: 12px 14px;
        border: 1px solid;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    }

    .petugas-alert.success {
        background: #f0fbf6;
        border-color: #b8e7cb;
        color: #1f6b4b;
    }

    .petugas-alert.danger {
        background: #fff6f4;
        border-color: #f6c6bc;
        color: #9f2f1f;
    }

    .petugas-card {
        background: #fff;
        border: 1px solid #e6ecf3;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 12px 36px rgba(0, 0, 0, 0.07);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .petugas-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 46px rgba(0, 0, 0, 0.1);
    }

    .accent-icon {
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        background: rgba(65, 166, 126, 0.15);
        color: #2f8c66;
        border-radius: 12px;
        font-size: 18px;
        flex-shrink: 0;
    }

    .petugas-card label {
        font-weight: 600;
        color: #33443e;
    }

    .petugas-card .form-control {
        border: 1px solid #dbe2ea;
        border-radius: 10px;
        padding: 10px 12px;
        background: #fbfcff;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .petugas-card .form-control:focus {
        border-color: #41A67E;
        box-shadow: 0 0 0 3px rgba(65, 166, 126, 0.16);
    }

    .btn-accent {
        background: #41A67E;
        border: none;
        color: #fff;
        border-radius: 12px;
        padding: 10px 14px;
        font-weight: 700;
        box-shadow: 0 12px 26px rgba(65, 166, 126, 0.25);
    }

    .btn-accent:hover {
        background: #378c69;
        color: #fff;
    }

    .badge-soft {
        padding: 7px 10px;
        border-radius: 20px;
        background: #f3f6ff;
        color: #2d4b7d;
        font-weight: 700;
        font-size: 12px;
        border: 1px solid #d8e2f5;
    }

    .table-modern thead th {
        background: #f5fbf7;
        color: #2f4c41;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.02em;
        border: none;
    }

    .table-modern td,
    .table-modern th {
        padding: 12px 14px;
        vertical-align: middle;
    }

    .table-modern tbody tr {
        transition: background 0.2s ease;
    }

    .table-modern tbody tr:hover {
        background: #f9fdfb;
    }

    .table-modern td {
        border-color: #eef2f7;
    }

    .badge-date {
        background: #eef4ff;
        color: #2d4b7d;
        border-radius: 10px;
        padding: 6px 10px;
        font-weight: 600;
        font-size: 12px;
    }

    .empty-state {
        background: #f7fbff;
        color: #6c7c89;
        font-weight: 600;
    }

    @media (max-width: 991px) {
        .petugas-hero {
            flex-direction: column;
            align-items: flex-start;
        }

        .highlight-card {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="container petugas-page py-4">
    <div class="petugas-hero mb-4">
        <div class="position-relative" style="z-index: 1;">
            <div class="hero-eyebrow">Tim & akses</div>
            <h1 class="hero-title mb-1">Daftar Petugas</h1>
            <p class="text-muted mb-3">Kelola akun kasir dan pastikan email yang dipakai tetap aktif.</p>
            <div class="d-flex flex-wrap gap-2">
                <span class="stat-pill"><i class="fa-solid fa-users me-2"></i>{{ $petugas->count() }} petugas aktif</span>
                <span class="stat-pill soft"><i class="fa-solid fa-shield-halved me-2"></i>Email valid dibutuhkan saat login</span>
            </div>
        </div>
    </div>

    {{-- Pesan sukses --}}
    @if(session('success'))
        <div class="petugas-alert success mb-3">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Pesan error --}}
    @if(session('error'))
        <div class="petugas-alert danger mb-3">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Validasi error --}}
    @if($errors->any())
        <div class="petugas-alert danger mb-3">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>
                <div class="fw-semibold mb-1">Periksa kembali data yang dimasukkan</div>
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Form tambah petugas --}}
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="petugas-card h-100">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <span class="accent-icon"><i class="fa-solid fa-user-plus"></i></span>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Tambah</div>
                        <h5 class="mb-1">Petugas Baru</h5>
                        <p class="text-muted small mb-0">Nama dan email akan dipakai saat petugas login kasir.</p>
                    </div>
                </div>
                <form action="{{ route('pemilik.petugas.store') }}" method="POST" class="petugas-form">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" class="form-control" placeholder="Tuliskan nama lengkap" required value="{{ old('name') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="contoh: kasir@arena.com" required value="{{ old('email') }}">
                    </div>
                    <button type="submit" class="btn btn-accent w-100">
                        <i class="fa-solid fa-plus me-2"></i> Simpan Petugas
                    </button>
                </form>
            </div>
        </div>

        {{-- Tabel petugas --}}
        <div class="col-lg-7">
            <div class="petugas-card h-100">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Data aktif</div>
                        <h5 class="mb-1">Petugas Terdaftar</h5>
                        <p class="text-muted small mb-0">Pantau email dan waktu pembuatan untuk validasi akses.</p>
                    </div>
                    <span class="badge-soft"><i class="fa-solid fa-arrows-rotate me-1"></i>Terupdate otomatis</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;">No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Dibuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($petugas as $index => $p)
                                <tr>
                                    <td class="fw-semibold text-muted">{{ $index + 1 }}</td>
                                    <td class="fw-semibold">{{ $p->name }}</td>
                                    <td class="text-muted">{{ $p->email }}</td>
                                    <td>
                                        @if($p->created_at)
                                            <span class="badge-date"><i class="fa-regular fa-clock me-1"></i>{{ $p->created_at->format('d M Y, H:i') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr class="empty-state">
                                    <td colspan="4" class="text-center">Belum ada petugas terdaftar</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
