@extends('layouts.sidebar')

@section('title', 'Kelola Rekening')

@section('content')
<div class="container py-4">
    <h2 class="fw-bold mb-4 text-success">Kelola Rekening</h2>

    {{-- Pesan sukses --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Pesan error validasi --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form Rekening --}}
    <div class="card shadow-sm p-4 mb-4">
        <h5 class="fw-bold mb-3">Rekening Penerima</h5>
        <form action="{{ route('rekening.update') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Nama Bank</label>
                    <input type="text" name="nama_bank"
                        value="{{ old('nama_bank', $rekening->nama_bank ?? '') }}"
                        class="form-control" placeholder="Contoh: BCA, BNI, Mandiri">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Nomor Rekening</label>
                    <input type="text" name="nomor_rekening"
                        value="{{ old('nomor_rekening', $rekening->nomor_rekening ?? '') }}"
                        class="form-control" placeholder="Masukkan nomor rekening aktif">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Atas Nama</label>
                    <input type="text" name="atas_nama"
                        value="{{ old('atas_nama', $rekening->atas_nama ?? '') }}"
                        class="form-control" placeholder="Nama sesuai buku rekening">
                </div>
            </div>
            <div class="text-end">
                <button class="btn btn-success px-4">
                    <i class="bi bi-save me-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>

    {{-- Riwayat Pencairan --}}
    <div class="card shadow-sm p-4">
        <h5 class="fw-bold mb-3">Riwayat Pencairan Dana</h5>

        @if($riwayat->isEmpty())
            <p class="text-muted text-center mb-0">Belum ada riwayat pencairan dana.</p>
        @else
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Bank Tujuan</th>
                            <th>Nomor Rekening</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($riwayat as $item)
                            <tr>
                                <td>{{ $item->created_at->format('d M Y') }}</td>
                                <td>{{ $item->bank_tujuan }}</td>
                                <td>{{ $item->nomor_rekening }}</td>
                                <td>Rp{{ number_format($item->jumlah, 0, ',', '.') }}</td>
                                <td>
                                    @if($item->status == 'berhasil')
                                        <span class="badge bg-success">Berhasil</span>
                                    @elseif($item->status == 'gagal')
                                        <span class="badge bg-danger">Gagal</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Proses</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4">
    <form action="{{ route('rekening.cairkan') }}" method="POST" class="d-flex justify-content-end">
        @csrf
        <input type="number" name="jumlah" class="form-control w-auto me-2" placeholder="Jumlah (Rp)" required>
        <button class="btn btn-primary">
            <i class="bi bi-cash-stack me-1"></i> Cairkan Dana
        </button>
    </form>
</div>

            </div>
        @endif
    </div>
</div>
@endsection
