@extends('layouts.sidebar')

@section('title', 'Pemesanan Saya')

@section('content')
<link rel="stylesheet" href="{{ asset('css/penyewa.css') }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container py-4">
    <div class="penyewa-page-header">
        <div>
            <p class="eyebrow">Kelola Pemesanan</p>
            <h1>Pemesanan Saya</h1>
            <p class="subtitle">Pantau status, cek jadwal, dan bagikan ulasan setelah bermain.</p>
        </div>
    </div>

    @php
        $total = $pemesanan->count();
        $selesai = $pemesanan->where('status', 'selesai')->count();
        $menunggu = $pemesanan->where('status', 'menunggu')->count();
        $dibatalkan = $pemesanan->where('status', 'dibatalkan')->count();
    @endphp
    <div class="booking-stats">
        <div class="stat-card">
            <p>Total Pemesanan</p>
            <h4>{{ $total }}</h4>
        </div>
        <div class="stat-card stat-card--success">
            <p>Selesai</p>
            <h4>{{ $selesai }}</h4>
        </div>
        <div class="stat-card stat-card--warning">
            <p>Menunggu</p>
            <h4>{{ $menunggu }}</h4>
        </div>
        <div class="stat-card stat-card--danger">
            <p>Dibatalkan</p>
            <h4>{{ $dibatalkan }}</h4>
        </div>
    </div>

    @foreach (['success', 'error'] as $flash)
        @if (session($flash))
            <div class="alert alert-{{ $flash === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
                {{ session($flash) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    @endforeach

    @if ($pemesanan->isEmpty())
        <div class="text-center py-5">
            <i class="fa-solid fa-clipboard-list text-success fs-1 mb-3"></i>
            <h5 class="fw-semibold">Belum ada pemesanan.</h5>
            <p class="text-muted mb-0">Mulai jelajahi lapangan dan lakukan pemesanan pertamamu.</p>
        </div>
    @else
        <div class="table-responsive booking-table shadow-sm rounded-4 overflow-hidden">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Lapangan</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Jam</th>
                        <th scope="col">Durasi</th>
                        <th scope="col">Total</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-center" style="width: 200px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pemesanan as $item)
                        @php
                            $lapangan = $item->lapangan;
                            $formatMulai = strlen($item->jam_mulai ?? '') === 5 ? 'H:i' : 'H:i:s';
                            $formatSelesai = strlen($item->jam_selesai ?? '') === 5 ? 'H:i' : 'H:i:s';
                            $jamMulai = \Carbon\Carbon::createFromFormat($formatMulai, $item->jam_mulai);
                            $jamSelesai = \Carbon\Carbon::createFromFormat($formatSelesai, $item->jam_selesai);
                            $durasiJam = $jamMulai->floatDiffInHours($jamSelesai);
                            $status = ucfirst($item->status ?? 'menunggu');
                            $hasUlasan = $item->ulasan !== null;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $lapangan->nama_lapangan ?? 'Lapangan' }}</div>
                                <small class="text-muted">
                                    <i class="fa-solid fa-location-dot me-1"></i>
                                    {{ $lapangan->lokasi ?? '-' }}
                                </small>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</td>
                            <td>{{ $jamMulai->format('H:i') }} - {{ $jamSelesai->format('H:i') }}</td>
                            <td>{{ number_format($durasiJam, 1) }} jam</td>
                            <td>Rp{{ number_format($item->total_harga, 0, ',', '.') }}</td>
                            <td>
                                @php
                                    $statusClass = $item->status === 'selesai' ? 'success' : ($item->status === 'dibatalkan' ? 'danger' : 'warning');
                                @endphp
                                <span class="status-chip status-chip--{{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('penyewa.detail', $lapangan->id ?? 0) }}" class="btn btn-outline-success btn-sm btn-action">
                                        <i class="fa-solid fa-eye me-1"></i> Detail
                                    </a>
                                    @if ($item->status === 'selesai')
                                        <button class="btn btn-sm btn-action {{ $hasUlasan ? 'btn-warning text-dark' : 'btn-success' }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#ulasanPemesanan{{ $item->id }}">
                                            <i class="fa-solid fa-star me-1"></i>
                                            {{ $hasUlasan ? 'Ubah Ulasan' : 'Tulis Ulasan' }}
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@foreach ($pemesanan as $item)
    @if ($item->status === 'selesai')
        @php
            $lapangan = $item->lapangan;
            $ulasan = $item->ulasan;
        @endphp
        <div class="modal fade" id="ulasanPemesanan{{ $item->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ulasan {{ $lapangan->nama_lapangan ?? 'Lapangan' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="ulasanForm{{ $item->id }}" action="{{ route('penyewa.ulasan.store', $lapangan) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <select name="rating" class="form-select" required>
                                    <option value="" disabled {{ $ulasan ? '' : 'selected' }}>Pilih rating</option>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ (int) optional($ulasan)->rating === $i ? 'selected' : '' }}>
                                            {{ $i }} - {{ ['Sangat Buruk', 'Buruk', 'Cukup', 'Bagus', 'Sangat Bagus'][$i-1] }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Komentar</label>
                                <textarea name="komentar" class="form-control" rows="4" placeholder="Bagikan pengalamanmu">{{ old('komentar', optional($ulasan)->komentar) }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            @if ($ulasan)
                                <button type="submit"
                                        form="hapusUlasanForm{{ $item->id }}"
                                        class="btn btn-outline-danger"
                                        onclick="return confirm('Hapus ulasan ini?')">
                                    Hapus
                                </button>
                            @endif
                            <div class="ms-auto d-flex gap-2">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-success">{{ $ulasan ? 'Perbarui' : 'Kirim' }}</button>
                            </div>
                        </div>
                    </form>
                    @if ($ulasan)
                        <form id="hapusUlasanForm{{ $item->id }}" action="{{ route('penyewa.ulasan.destroy', $ulasan) }}" method="POST" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endforeach

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => alert.classList.add('d-none'));
    }, 4000);
</script>
@endsection