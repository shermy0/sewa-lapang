@extends('layouts.sidebar')

@section('title', 'Pemesanan Lapangan')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-calendar-check me-2 text-success"></i> Pemesanan Lapangan
            </h4>
            <p class="text-muted mb-0">Kelola seluruh pemesanan yang masuk untuk lapangan Anda.</p>
        </div>
        <form class="d-flex gap-2" method="GET">
            <input type="search" name="search" class="form-control" placeholder="Cari penyewa atau lapangan..."
                value="{{ $search }}">
            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                @foreach (['menunggu' => 'Menunggu', 'dibayar' => 'Dibayar', 'selesai' => 'Selesai', 'batal' => 'Batal'] as $value => $label)
                    <option value="{{ $value }}" {{ $statusFilter === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-success">
                <i class="fa-solid fa-filter me-2"></i> Terapkan
            </button>
        </form>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        @php
            $summaryCards = [
                ['key' => 'total', 'label' => 'Total Pemesanan', 'icon' => 'fa-solid fa-layer-group', 'color' => 'primary'],
                ['key' => 'menunggu', 'label' => 'Menunggu', 'icon' => 'fa-solid fa-hourglass-half', 'color' => 'warning'],
                ['key' => 'dibayar', 'label' => 'Dibayar', 'icon' => 'fa-solid fa-sack-dollar', 'color' => 'info'],
                ['key' => 'selesai', 'label' => 'Selesai', 'icon' => 'fa-solid fa-circle-check', 'color' => 'success'],
                ['key' => 'batal', 'label' => 'Dibatalkan', 'icon' => 'fa-solid fa-circle-xmark', 'color' => 'danger'],
            ];
        @endphp
        @foreach ($summaryCards as $card)
            <div class="col-6 col-md-3 col-xl-2">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-{{ $card['color'] }} bg-opacity-10 text-{{ $card['color'] }} p-3 rounded-4">
                                <i class="{{ $card['icon'] }}"></i>
                            </div>
                            <div>
                                <p class="text-muted text-uppercase fw-semibold small mb-1">{{ $card['label'] }}</p>
                                <h5 class="fw-bold mb-0">{{ number_format($summary[$card['key']] ?? 0) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h6 class="fw-semibold mb-0">
                <i class="fa-solid fa-list-check me-2 text-success"></i> Daftar Pemesanan
            </h6>
            <small class="text-muted">Menampilkan {{ $pemesanan->firstItem() ?? 0 }}-{{ $pemesanan->lastItem() ?? 0 }} dari {{ $pemesanan->total() }} data</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Penyewa</th>
                        <th>Lapangan</th>
                        <th>Jadwal</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pemesanan as $item)
                        @php
                            $isDummy = !($item instanceof \App\Models\Pemesanan);
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration + ($pemesanan->firstItem() - 1) }}</td>
                            <td>
                                <div class="fw-semibold">{{ $item->penyewa->name ?? '-' }}</div>
                                <div class="text-muted small">{{ $item->penyewa->email ?? '' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $item->lapangan->nama_lapangan ?? '-' }}</div>
                                <div class="text-muted small">{{ $item->lapangan->lokasi ?? '' }}</div>
                            </td>
                            <td>
                                @if ($item->jadwal)
                                    <div class="fw-semibold">
                                        {{ $item->jadwal->tanggal?->translatedFormat('d F Y') ?? '-' }}
                                    </div>
                                    <div class="text-muted small">
                                        {{ $item->jadwal->jam_mulai?->format('H:i') ?? '--:--' }} -
                                        {{ $item->jadwal->jam_selesai?->format('H:i') ?? '--:--' }} WIB
                                    </div>
                                @else
                                    <span class="badge text-bg-light">Belum terjadwal</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusStyles = [
                                        'menunggu' => 'warning',
                                        'dibayar' => 'info',
                                        'selesai' => 'success',
                                        'batal' => 'danger',
                                    ];
                                    $statusLabels = [
                                        'menunggu' => 'Menunggu',
                                        'dibayar' => 'Dibayar',
                                        'selesai' => 'Selesai',
                                        'batal' => 'Dibatalkan',
                                    ];
                                @endphp
                                <span class="badge text-bg-{{ $statusStyles[$item->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$item->status] ?? ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <form action="{{ $isDummy ? '#' : route('pemilik.pemesanan.update', $item) }}" method="POST" class="d-inline-flex align-items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-select form-select-sm w-auto" {{ $isDummy ? 'disabled' : '' }}>
                                        @foreach ($statusLabels as $value => $label)
                                            <option value="{{ $value }}" {{ $item->status === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-success" {{ $isDummy ? 'disabled' : '' }}>
                                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan
                                    </button>
                                </form>
                                @if ($isDummy)
                                    <div class="text-muted small mt-1">Contoh data — tidak dapat diperbarui.</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-inbox fa-2x mb-3"></i>
                                <p class="mb-0">Belum ada pemesanan untuk ditampilkan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pemesanan->hasPages())
            <div class="card-footer bg-white border-0">
                {{ $pemesanan->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
