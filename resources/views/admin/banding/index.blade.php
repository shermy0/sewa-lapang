@extends('layouts.admin')

@section('title', 'Banding Pemilik')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-scale-balanced text-primary me-2"></i> Permohonan Banding Pemilik
            </h4>
            <p class="text-muted mb-0">Pantau dan tindak lanjuti banding dari akun pemilik yang diblokir.</p>
        </div>
        <div class="text-end">
            <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2">
                Total banding: {{ number_format($bandingList->total()) }}
            </span>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-uppercase text-muted mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>
                                {{ Str::headline($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 ms-auto">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> Terapkan
                        </button>
                        <a href="{{ route('admin.banding.index') }}" class="btn btn-light border">Atur ulang</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Pemilik</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Diajukan</th>
                            <th>Ditangani</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bandingList as $banding)
                            <tr>
                                <td class="fw-semibold">#{{ $banding->id }}</td>
                                <td>{{ $banding->pemilik->name ?? '-' }}</td>
                                <td>{{ $banding->pemilik->email ?? '-' }}</td>
                                <td>
                                    @php
                                        $badge = [
                                            'pending' => 'bg-warning text-dark',
                                            'diterima' => 'bg-success',
                                            'ditolak' => 'bg-danger',
                                        ][$banding->status] ?? 'bg-secondary';
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ Str::headline($banding->status) }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ optional($banding->created_at)->translatedFormat('d F Y, H:i') }}</small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ optional($banding->ditangani_pada)->translatedFormat('d F Y, H:i') ?? '-' }}
                                    </small>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.banding.show', $banding) }}" class="btn btn-sm btn-outline-primary">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-5 text-center text-muted">
                                    <i class="fa-solid fa-scale-balanced mb-2 fs-3 d-block"></i>
                                    Belum ada pengajuan banding.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($bandingList->hasPages())
            <div class="card-footer bg-white border-0">
                {{ $bandingList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
