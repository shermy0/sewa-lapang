@extends('layouts.sidebar')

@section('content')
<div class="container">
    <h1>Halo, {{ auth()->user()->name }}!</h1>
    <p>Role kamu: <strong>{{ auth()->user()->role }}</strong></p>
    <p>Ini halaman tes sidebar. Coba klik-klik menunya!</p>
</div>
@endsection
