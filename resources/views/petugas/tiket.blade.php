@extends('layouts.master')

@section('title', 'Tiket Petugas')

@section('content')
<div class="container mt-4">
    <h1>Daftar Tiket</h1>
    @if(!empty($tiket))
        <ul>
            @foreach($tiket as $t)
                <li>{{ $t->nama ?? 'Tiket kosong' }}</li>
            @endforeach
        </ul>
    @else
        <p class="text-muted">Belum ada tiket.</p>
    @endif
</div>
@endsection
