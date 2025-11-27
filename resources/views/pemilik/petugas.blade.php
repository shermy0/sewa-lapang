@extends('layouts.sidebar')

@section('title', 'Petugas')

@section('content')
<div class="container mt-4">
    <h1>Daftar Petugas</h1>

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

    {{-- Form tambah petugas --}}
    <div class="card mb-4 p-3">
        <h5>Tambah Petugas Baru</h5>
        <form action="{{ route('pemilik.petugas.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Nama</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
            </div>
            <button type="submit" class="btn btn-primary">Tambah Petugas</button>
        </form>
    </div>

    {{-- Tabel petugas --}}
    <div class="card p-3">
        <h5>Petugas Terdaftar</h5>
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
                @forelse($petugas as $index => $p)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $p->name }}</td>
                        <td>{{ $p->email }}</td>
                        <td>{{ $p->created_at ? $p->created_at->format('d-m-Y H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Belum ada petugas</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
