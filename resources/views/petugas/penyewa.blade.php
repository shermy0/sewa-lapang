@extends('layouts.master')

@section('title', 'Penyewa')

@section('content')
<div class="container mt-4">
    <h1 class="mb-3">Daftar Penyewa</h1>

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

    {{-- Form tambah penyewa --}}
    <div class="card mb-4 p-3">
        <h5>Tambah Penyewa Baru</h5>
        <form action="{{ route('petugas.penyewa.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Nama</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
            </div>
            <div class="mb-3">
                <label>Password <small class="text-muted">(opsional, default: password123)</small></label>
                <input type="text" name="password" class="form-control" value="{{ old('password') }}">
            </div>
            <button type="submit" class="btn btn-primary">Tambah Penyewa</button>
        </form>
    </div>

    {{-- Tabel penyewa --}}
    <div class="card p-3">
        <h5>Penyewa Terdaftar</h5>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Dibuat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($penyewa as $index => $p)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $p->name }}</td>
                        <td>{{ $p->email }}</td>
                        <td>{{ $p->created_at ? $p->created_at->format('d-m-Y H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Belum ada penyewa</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
