@extends('layouts.sidebar')

@section('title', 'Data Lapangan')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold">Data Lapangan</h4>
    <a href="#" class="btn btn-success">
      <i class="fa-solid fa-plus me-1"></i> Tambah Lapangan
    </a>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      @if ($lapangan->count())
        <table class="table table-striped align-middle">
          <thead class="table-success">
            <tr>
              <th>No</th>
              <th>Nama Lapangan</th>
              <th>Deskripsi</th>
              <th>Lokasi</th>
              <th>Harga per Jam</th>
              <th>Foto</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($lapangan as $l)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $l->nama_lapangan }}</td>
              <td>{{ $l->deskripsi ?? '-' }}</td>
              <td>{{ $l->lokasi }}</td>
              <td>Rp {{ number_format($l->harga_per_jam, 0, ',', '.') }}</td>
              <td>
                @if ($l->foto)
                  <img src="{{ asset('storage/'.$l->foto) }}" alt="Foto Lapangan" width="70" class="rounded">
                @else
                  <span class="text-muted">Tidak ada</span>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>

        {{ $lapangan->links() }}
      @else
        <p class="text-muted text-center">Belum ada data lapangan.</p>
      @endif
    </div>
  </div>
</div>
@endsection
