@extends('layouts.sidebar')

@section('title', 'Riwayat Pemesanan')

@section('content')
<div class="container py-4">
    <h2 class="fw-bold mb-4 text-secondary">Riwayat Pemesanan</h2>

    <div class="row">
        @forelse($dibatalkan as $p)
        <div class="col-md-4 mb-3">
            <div class="card p-3 border-secondary">
                <p><strong>Lapangan:</strong> {{ $p->lapangan->nama_lapangan }}</p>
                <p><strong>Jadwal:</strong> 
                    {{ \Carbon\Carbon::parse($p->jadwal->tanggal)->format('d M Y') }}
                    ({{ $p->jadwal->jam_mulai }} - {{ $p->jadwal->jam_selesai }})
                </p>
                <p><strong>Status:</strong> 
                    @if($p->status == 'batal')
                        <span class="badge bg-danger">Dibatalkan</span>
                    @elseif($p->status == 'di-scan')
                        <span class="badge bg-primary">Sudah Discanned</span>
                    @endif
                </p>
            </div>
        </div>
        @empty
        <p class="text-muted">Belum ada riwayat pemesanan dibatalkan atau discan.</p>
        @endforelse
    </div>
</div>
@endsection
