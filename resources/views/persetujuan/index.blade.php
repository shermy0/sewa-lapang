@extends('layouts.sidebar')

@section('title', 'Persetujuan')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h4 class="mb-3 text-success">
                <i class="fa-solid fa-check-circle me-2"></i> Daftar Persetujuan
            </h4>

            <!-- Filter / Search -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <form class="d-flex" style="max-width: 300px;">
                    <input type="text" class="form-control form-control-sm" placeholder="Cari penyewa...">
                </form>
                <button class="btn btn-sm btn-outline-success">
                    <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh
                </button>
            </div>

            <!-- Tabel Persetujuan -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-success">
                        <tr>
                            <th>#</th>
                            <th>Nama Penyewa</th>
                            <th>Lapangan Lama</th>
                            <th>Lapangan Baru</th>
                            <th>Tanggal / Jam</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dummy data sementara -->
                        <tr>
                            <td>1</td>
                            <td>Irham Maulana</td>
                            <td>Lapangan A</td>
                            <td>Lapangan B</td>
                            <td>2025-10-31 (08:00 - 09:00)</td>
                            <td><span class="badge bg-warning text-dark">Menunggu</span></td>
                            <td>
                                <button class="btn btn-sm btn-success">
                                    <i class="fa-solid fa-check me-1"></i> Setujui
                                </button>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fa-solid fa-xmark me-1"></i> Tolak
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Bayu Resnadi</td>
                            <td>Lapangan C</td>
                            <td>Lapangan D</td>
                            <td>2025-11-01 (10:00 - 11:30)</td>
                            <td><span class="badge bg-success">Disetujui</span></td>
                            <td>
                                <button class="btn btn-sm btn-secondary" disabled>
                                    <i class="fa-solid fa-check-double me-1"></i> Selesai
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination dummy -->
            <nav class="mt-3">
                <ul class="pagination pagination-sm justify-content-end">
                    <li class="page-item disabled"><a class="page-link" href="#">‹</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">›</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>
@endsection
 