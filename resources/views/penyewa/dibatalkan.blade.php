{{-- 
@extends('layouts.sidebar')

@section('content') --}}
@forelse($dibatalkan as $p)
<div class="col-md-4 mb-3">
    <div class="card p-3 border-secondary">
        <p><strong>Lapangan:</strong> {{ $p->lapangan->nama_lapangan }}</p>
        <p><strong>Jadwal:</strong> 
            {{ \Carbon\Carbon::parse($p->jadwal->tanggal)->format('d M Y') }}
            ({{ $p->jadwal->jam_mulai }} - {{ $p->jadwal->jam_selesai }})
        </p>
        <p><strong>Status:</strong> 
            <span class="badge bg-secondary text-white">Dibatalkan / Sudah di-scan</span>
        </p>
        @if($p->kode_tiket)
        <p><strong>Kode Tiket:</strong> {{ $p->kode_tiket }}</p>
        <div class="mt-2 text-center">
            {!! DNS1D::getBarcodeHTML($p->kode_tiket, 'C128') !!}
        </div>
        @endif
    </div>
</div>
@empty
<p class="text-muted">Tidak ada tiket dibatalkan atau sudah di-scan.</p>
@endforelse
{{-- @endsection --}}