@extends('layouts.sidebar')

@section('title', 'Kelola Banner')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="container mt-4">
    <!-- Header + Tombol Tambah Banner -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Kelola Banner</h3>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bannerModal">
            <i class="fa-solid fa-plus"></i> Tambah Banner
        </button>
    </div>

    <!-- Modal Tambah Banner -->
    <div class="modal fade" id="bannerModal" tabindex="-1" aria-labelledby="bannerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bannerModalLabel">Tambah Banner</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul</label>
                            <input type="text" name="judul" id="judul" class="form-control" placeholder="Judul banner (opsional)">
                        </div>
                        <div class="mb-3">
                            <label for="gambar" class="form-label">Gambar</label>
                            <input type="file" name="gambar" id="gambar" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Banner -->
    @if($banners->count())
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th style="width:5%;">No</th>
                    <th style="width:25%;">Judul</th>
                    <th style="width:25%;">Gambar</th>
                    <th style="width:15%;">Status</th>
                    <th style="width:30%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($banners as $banner)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $banner->judul ?? '-' }}</td>
                    <td>
                        <img src="{{ asset($banner->gambar) }}" alt="{{ $banner->judul ?? 'Banner' }}" class="img-fluid" style="max-height: 60px;">
                    </td>
                    <td>
                        <span class="badge {{ $banner->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($banner->status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center align-items-center gap-2">
                            <!-- Tombol Edit -->
                            <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal{{ $banner->id }}">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </button>

                            <!-- Tombol Hapus -->
                            <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-outline-danger btn-sm btn-delete">
                                    <i class="fa-solid fa-trash"></i> Hapus
                                </button>
                            </form>

                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const deleteButtons = document.querySelectorAll('.btn-delete');

                                    deleteButtons.forEach(button => {
                                        button.addEventListener('click', function () {
                                            const form = this.closest('form');

                                            Swal.fire({
                                                title: 'Apakah kamu yakin?',
                                                text: "Banner ini akan dihapus permanen!",
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonColor: '#d33', // merah
                                                cancelButtonColor: '#3085d6', // biru
                                                confirmButtonText: 'Ya, hapus!',
                                                cancelButtonText: 'Batal'
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    form.submit(); // submit form kalau user konfirmasi
                                                }
                                            });
                                        });
                                    });
                                });
                            </script>

                            <!-- Tombol Toggle -->
                            <form action="{{ route('admin.banners.toggle', $banner->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-success btn-sm">
                                <i class="fa-solid fa-power-off"></i>
                            </button>
                        </form>
                    </td>
                </tr>

                <!-- Modal Edit Banner -->
                <div class="modal fade" id="editModal{{ $banner->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $banner->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel{{ $banner->id }}">Edit Banner</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="judul{{ $banner->id }}" class="form-label">Judul</label>
                                        <input type="text" class="form-control" id="judul{{ $banner->id }}" name="judul" value="{{ $banner->judul }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="gambar{{ $banner->id }}" class="form-label">Gambar</label>
                                        <input type="file" class="form-control" id="gambar{{ $banner->id }}" name="gambar">
                                        <small class="text-muted">Biarkan kosong jika tidak ingin mengganti gambar</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <p class="text-center">Belum ada banner.</p>
    @endif
</div>
@endsection