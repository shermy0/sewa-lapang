{{-- 
@extends('layouts.sidebar')

@section('content') --}}
@forelse($belumDibayar as $p)
<div class="col-md-4 mb-3">
    <div class="card p-3 border-warning position-relative">
        <p><strong>Lapangan:</strong> {{ $p->lapangan->nama_lapangan }}</p>
        <p><strong>Jadwal:</strong> 
            {{ \Carbon\Carbon::parse($p->jadwal->tanggal)->format('d M Y') }}
            ({{ $p->jadwal->jam_mulai }} - {{ $p->jadwal->jam_selesai }})
        </p>
        <p><strong>Status:</strong> 
            <span class="badge bg-warning text-dark">Belum Dibayar</span>
        </p>

        {{-- COUNTDOWN --}}
        <p class="text-danger fw-semibold mb-1" id="countdown-{{ $p->id }}"></p>

        <div class="d-flex justify-content-between mt-2">
            <button class="btn btn-success btn-pay-again" data-id="{{ $p->id }}">Bayar Sekarang</button>
            <form action="{{ route('pemesanan.batalkan', $p->id) }}" method="POST" onsubmit="return confirm('Yakin batalkan pemesanan ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Batalkan</button>
            </form>
        </div>
    </div>
</div>
@empty
<p class="text-muted">Tidak ada pesanan menunggu pembayaran.</p>
@endforelse
{{-- @endsection --}}
