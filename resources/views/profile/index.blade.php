@extends('layouts.sidebar')

@section('content')
  <link rel="stylesheet" href="{{ asset('css/profile.css') }}">

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
                <button 
                    class="nav-link {{ (session('activeTab') === 'profile' || !session('activeTab')) ? 'active' : '' }}" 
                    id="tab-profile" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                    <i class="bi bi-person-circle me-1"></i> Profile
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button 
                    class="nav-link {{ session('activeTab') === 'password' ? 'active' : '' }}" 
                    id="tab-password" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                    <i class="bi bi-lock me-1"></i> Ganti Password
                </button>
            </li>
            @if($user->role === 'pemilik')
            <li class="nav-item" role="presentation">
                <button 
                    class="nav-link {{ session('activeTab') === 'lapangan' ? 'active' : '' }}" 
                    id="tab-lapangan" data-bs-toggle="tab" data-bs-target="#lapangan" type="button" role="tab">
                    <i class="bi bi-building me-1"></i> Lapangan Dimiliki
                </button>
            </li>
            @endif
        </ul>

        <div class="tab-content" id="profileTabsContent">
            <!-- ====================== TAB PROFILE ====================== -->
            <div class="tab-pane fade {{ (session('activeTab') === 'profile' || !session('activeTab')) ? 'show active' : '' }}" id="profile" role="tabpanel">
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary-custom">Nama Lengkap</label>
                                <input type="text" name="nama" value="{{ old('nama', $user->name) }}" class="form-control shadow-sm" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary-custom">Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                                    class="form-control shadow-sm" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-secondary-custom">No HP</label>
                                <input type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" class="form-control shadow-sm">
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <button class="btn btn-save px-4 py-2">
                            <i class="bi bi-check-circle me-1"></i> Simpan Profil
                        </button>
                    </div>
                </form>
            </div>

            <!-- ====================== TAB PASSWORD ====================== -->
            <div class="tab-pane fade {{ session('activeTab') === 'password' ? 'show active' : '' }}" id="password" role="tabpanel">
                <form action="{{ route('profile.password.update') }}" method="POST">
                    @csrf
                    <div class="col-md-12 mt-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary-custom">Password Lama</label>
                            <input type="password" name="current_password" class="form-control shadow-sm" required>
                            @error('current_password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary-custom">Password Baru</label>
                            <input type="password" name="password" class="form-control shadow-sm" required>
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary-custom">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control shadow-sm" required>
                            @error('password_confirmation')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="text-end mt-3">
                            <button class="btn btn-save px-4 py-2">
                                <i class="bi bi-lock-fill me-1"></i> Simpan Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ====================== TAB LAPANGAN ====================== -->
            @if($user->role === 'pemilik')
            <div class="tab-pane fade {{ session('activeTab') === 'lapangan' ? 'show active' : '' }}" id="lapangan" role="tabpanel">
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
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }
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
                    }).then(() => location.reload());
                }
            });
        }
    });
}
</script>
@endsection
