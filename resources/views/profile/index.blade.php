@extends('layouts.sidebar')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4 fw-bold text-primary-custom">Pengaturan Akun</h3>

    @if(session('success'))
        <div class="alert alert-success shadow-sm text-center">{{ session('success') }}</div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card shadow-lg border-0 p-4">
            <!-- FOTO PROFIL -->
            <div class="text-center mb-3">
                <img id="previewFoto" 
                    src="{{ $user->foto_profil ? asset('storage/'.$user->foto_profil) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=41A67E&color=fff' }}" 
                    class="rounded-circle border border-3 border-primary-custom shadow-sm object-fit-cover"
                    width="130" height="130" alt="Foto Profil">
            </div>

            <div class="text-center mb-4">
                <label for="foto_profil" class="btn btn-edit-foto px-3 py-2">
                    <i class="bi bi-camera-fill me-1"></i> Ganti Foto
                </label>
                <input type="file" id="foto_profil" name="foto_profil" accept="image/*" class="d-none" onchange="previewImage(event)">

                @if($user->foto_profil)
                    <button type="button" class="btn btn-outline-danger ms-2 px-3 py-2" onclick="hapusFoto()">
                        <i class="bi bi-trash3 me-1"></i> Hapus Foto
                    </button>
                @endif
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs justify-content-center mb-3 custom-tabs" id="profileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-profile" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                        <i class="bi bi-person-circle me-1"></i> Profile
                    </button>
                </li>
                @if($user->role === 'pemilik')
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-lapangan" data-bs-toggle="tab" data-bs-target="#lapangan" type="button" role="tab">
                        <i class="bi bi-building me-1"></i> Lapangan Dimiliki
                    </button>
                </li>
                @endif
            </ul>

            <div class="tab-content" id="profileTabsContent">
                <!-- ====================== TAB PROFILE ====================== -->
                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary-custom">Nama Lengkap</label>
                                <input type="text" name="nama" value="{{ old('nama', $user->name) }}" class="form-control shadow-sm" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary-custom">Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control shadow-sm" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary-custom">No HP</label>
                                <input type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" class="form-control shadow-sm">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary-custom">Password Baru</label>
                                <input type="password" name="password" class="form-control shadow-sm" placeholder="Kosongkan jika tidak ingin mengganti">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary-custom">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="form-control shadow-sm">
                            </div>

                            <div class="text-end mt-4">
                                <button class="btn btn-save px-4 py-2">
                                    <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ====================== TAB LAPANGAN ====================== -->
                @if($user->role === 'pemilik')
                <div class="tab-pane fade" id="lapangan" role="tabpanel">
                    <div class="mt-3">
                        @if($lapangan->isEmpty())
                            <p class="text-muted text-center">Belum ada lapangan terdaftar.</p>
                        @else
                            <table class="table align-middle shadow-sm border rounded-3">
                                <thead class="table-header-custom text-center">
                                    <tr>
                                        <th>Nama Lapangan</th>
                                        <th>Kategori</th>
                                        <th>Harga/Jam</th>
                                        <th>Lokasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lapangan as $item)
                                    <tr>
                                        <td>{{ $item->nama_lapangan }}</td>
                                        <td>{{ $item->kategori ?? '-' }}</td>
                                        <td>Rp{{ number_format($item->harga_per_jam, 0, ',', '.') }}</td>
                                        <td>{{ $item->lokasi }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<!-- Preview Foto Script -->
<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewFoto').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}
function hapusFoto() {
    Swal.fire({
        title: 'Hapus Foto Profil?',
        text: "Foto profil kamu akan diganti dengan avatar inisial otomatis.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#41A67E',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("{{ route('profile.hapusFoto') }}", {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Foto profil telah dihapus.',
                        icon: 'success',
                        confirmButtonColor: '#41A67E',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            });
        }
    });
}
</script>

<!-- 🌿 Custom Styling -->
<style>

    
    :root {
        --main-green: #41A67E;
        --accent-yellow: #F59E0B;
        --accent-blue: #3B82F6;
        --accent-purple: #8B5CF6;
    }

    .text-primary-custom { color: var(--main-green); }
    .text-secondary-custom { color: #4b5563; }

    .card {
        border-radius: 18px;
        background-color: #ffffff;
    }

    .object-fit-cover {
        object-fit: cover;
    }

    .custom-tabs {
        border-bottom: none;
    }

    .custom-tabs .nav-link {
        border: none;
        color: #6b7280;
        font-weight: 500;
        margin: 0 8px;
        transition: all 0.3s ease;
        border-radius: 10px;
        padding: 8px 20px;
    }

    .custom-tabs .nav-link.active {
        color: #fff;
        background: linear-gradient(90deg, var(--main-green), var(--accent-blue));
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    .btn-save {
        background: linear-gradient(90deg, var(--main-green), var(--accent-purple));
        border: none;
        color: #fff;
        border-radius: 8px;
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-save:hover {
        background: linear-gradient(90deg, var(--accent-purple), var(--main-green));
        transform: translateY(-2px);
    }

    .btn-edit-foto {
        background: linear-gradient(90deg, var(--accent-yellow), var(--main-green));
        border: none;
        border-radius: 8px;
        color: #fff;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn-edit-foto:hover {
        background: linear-gradient(90deg, var(--main-green), var(--accent-yellow));
        transform: translateY(-1px);
    }

    .table-header-custom {
        background: linear-gradient(90deg, var(--main-green), var(--accent-blue));
        color: #fff;
    }

    .form-control:focus {
        border-color: var(--main-green);
        box-shadow: 0 0 0 0.2rem rgba(65, 166, 126, 0.25);
    }

    .alert-success {
        border-left: 5px solid var(--main-green);
        background-color: rgba(65, 166, 126, 0.1);
        color: #155d48;
    }
</style>
@endsection
