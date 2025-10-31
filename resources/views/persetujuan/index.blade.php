@extends('layouts.sidebar')

@section('title', 'Persetujuan')

@section('content')
<div class="container py-4">
    <h3 class="fw-bold mb-4">Daftar Permintaan Perubahan Jadwal</h3>

    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Pemesanan ID</th>
                <th>Jadwal Lama</th>
                <th>Jadwal Baru</th>
                <th>Status</th>
                <th>Alasan</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($permintaan as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->pemesanan_id }}</td>
                    <td>
                        {{ $item->jadwalLama->tanggal ?? '-' }} 
                        ({{ $item->jadwalLama->jam_mulai ?? '' }} - {{ $item->jadwalLama->jam_selesai ?? '' }})
                    </td>
                    <td>
                        {{ $item->jadwalBaru->tanggal ?? '-' }} 
                        ({{ $item->jadwalBaru->jam_mulai ?? '' }} - {{ $item->jadwalBaru->jam_selesai ?? '' }})
                    </td>
                    <td>
                        <span class="badge 
                            @if($item->status == 'menunggu') bg-warning 
                            @elseif($item->status == 'disetujui') bg-success 
                            @else bg-danger @endif">
                            {{ ucfirst($item->status) }}
                        </span>
                    </td>
                    <td>{{ $item->alasan }}</td>
                    <td>{{ $item->created_at->format('d M Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
