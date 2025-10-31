@extends('layouts.sidebar')

@section('content')
<style>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css');

:root {
  --green: #41A67E;
  --gold: #F59E0B;
  --white: #ffffff;
  --gray-bg: #F3F4F6;
  --text: #2E3A35;
  --border: #ddd;
  --shadow: rgba(0, 0, 0, 0.1);
  --danger: #E74C3C;
}

body {
  background: var(--gray-bg);
  color: var(--text);
  font-family: 'Poppins', sans-serif;
}

.container {
  max-width: 900px;
  margin: 50px auto;
  background: var(--white);
  border-radius: 12px;
  box-shadow: 0 4px 10px var(--shadow);
  padding: 25px 30px;
  transition: all 0.3s ease;
}

h2 {
  color: var(--green);
  font-weight: 600;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.add-btn {
  background: var(--green);
  color: var(--white);
  border: none;
  border-radius: 8px;
  padding: 10px 16px;
  cursor: pointer;
  transition: 0.3s;
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 500;
}
.add-btn:hover {
  background: var(--gold);
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}
th, td {
  border-bottom: 1px solid var(--border);
  padding: 12px 10px;
  text-align: left;
}
th {
  background: var(--gray-bg);
  font-weight: 600;
  color: var(--text);
}

.action-btn {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
}

.icon-btn {
  border: none;
  background: none;
  cursor: pointer;
  padding: 6px 8px;
  border-radius: 6px;
  font-size: 16px;
  transition: 0.3s ease;
}
.icon-btn.edit {
  color: var(--green);
}
.icon-btn.edit:hover {
  background: rgba(65,166,126,0.1);
}
.icon-btn.delete {
  color: var(--danger);
}
.icon-btn.delete:hover {
  background: rgba(231,76,60,0.1);
}

/* Modal Styling */
.modal {
  position: fixed;
  z-index: 1000;
  inset: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  background: rgba(0,0,0,0.5);
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.4s ease;
  backdrop-filter: blur(2px);
}

.modal.show {
  opacity: 1;
  pointer-events: auto;
}

.modal-content {
  background: var(--white);
  padding: 25px;
  border-radius: 12px;
  width: 420px;
  box-shadow: 0 4px 20px var(--shadow);
  transform: translateY(-50px);
  opacity: 0;
  transition: all 0.4s ease;
}

.modal.show .modal-content {
  transform: translateY(0);
  opacity: 1;
}

.modal-content h3 {
  color: var(--green);
  margin-bottom: 15px;
}

.modal-content label {
  font-weight: 500;
  display: block;
  margin-bottom: 6px;
  color: var(--text);
}

.modal-content input,
.modal-content textarea {
  width: 100%;
  border: 1px solid var(--border);
  padding: 9px 10px;
  border-radius: 6px;
  margin-bottom: 12px;
  transition: border 0.3s;
}
.modal-content input:focus,
.modal-content textarea:focus {
  border-color: var(--green);
  outline: none;
}

.modal-content button {
  background: var(--green);
  color: var(--white);
  border: none;
  border-radius: 8px;
  padding: 9px 14px;
  cursor: pointer;
  transition: 0.3s;
  width: 100%;
  font-weight: 500;
}
.modal-content button:hover {
  background: var(--gold);
}

.close-btn {
  float: right;
  cursor: pointer;
  color: var(--gold);
  font-size: 20px;
  transition: 0.2s;
}
.close-btn:hover {
  color: var(--green);
}
</style>

<div class="container">
  <h2><i class="fa-solid fa-tags"></i> Kelola Kategori</h2>

  <button class="add-btn" onclick="openModal()">
    <i class="fa-solid fa-plus"></i> Tambah Kategori
  </button>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Nama Kategori</th>
        <th>Deskripsi</th>
        <th style="width: 120px; text-align:center;">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($kategori as $k)
      <tr>
        <td>{{ $k->id }}</td>
        <td>{{ $k->nama_kategori }}</td>
        <td>{{ $k->deskripsi }}</td>
        <td class="action-btn">
          <button class="icon-btn edit" onclick="editKategori({{ $k->id }}, '{{ $k->nama_kategori }}', '{{ $k->deskripsi }}')">
            <i class="fa-solid fa-pen-to-square"></i>
          </button>
          <button type="button" class="icon-btn delete" onclick="hapusKategori({{ $k->id }})">
            <i class="fa-solid fa-trash"></i>
          </button>
          <form id="delete-form-{{ $k->id }}" action="{{ route('kategori.destroy', $k->id) }}" method="POST" style="display:none;">
            @csrf
            @method('DELETE')
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

<!-- Modal -->
<div class="modal" id="kategoriModal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal()">✖</span>
    <h3 id="modalTitle">Tambah Kategori</h3>
    <form id="kategoriForm" method="POST" action="{{ route('kategori.store') }}">
      @csrf
      <input type="hidden" name="_method" id="formMethod" value="POST">

      <label>Nama Kategori</label>
      <input type="text" name="nama_kategori" id="nama_kategori" required>

      <label>Deskripsi</label>
      <textarea name="deskripsi" id="deskripsi" rows="3"></textarea>

      <button type="submit"><i class="fa-solid fa-save"></i> Simpan</button>
    </form>
  </div>
</div>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const modal = document.getElementById('kategoriModal');

function openModal() {
  document.getElementById('modalTitle').innerText = 'Tambah Kategori';
  document.getElementById('kategoriForm').action = '{{ route("kategori.store") }}';
  document.getElementById('formMethod').value = 'POST';
  document.getElementById('nama_kategori').value = '';
  document.getElementById('deskripsi').value = '';

  modal.classList.add('show');
}

function editKategori(id, nama, deskripsi) {
  document.getElementById('modalTitle').innerText = 'Edit Kategori';
  document.getElementById('kategoriForm').action = '/kategori/' + id;
  document.getElementById('formMethod').value = 'PUT';
  document.getElementById('nama_kategori').value = nama;
  document.getElementById('deskripsi').value = deskripsi;

  modal.classList.add('show');
}

function closeModal() {
  modal.classList.remove('show');
}

// Tutup modal jika klik di luar konten
window.addEventListener('click', function(e) {
  if (e.target === modal) closeModal();
});

// SweetAlert untuk hapus
function hapusKategori(id) {
  Swal.fire({
    title: 'Yakin hapus kategori ini?',
    text: "Data yang dihapus tidak bisa dikembalikan!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#41A67E',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      document.getElementById('delete-form-' + id).submit();
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const flashSuccess = @json(session('success'));
  const flashError = @json(session('error'));
  const validationErrors = @json($errors->all());

  if (flashSuccess) {
    Swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: flashSuccess,
      confirmButtonColor: '#41A67E'
    });
  }

  if (flashError) {
    Swal.fire({
      icon: 'error',
      title: 'Terjadi Kesalahan',
      text: flashError,
      confirmButtonColor: '#41A67E'
    });
  }

  if (validationErrors.length) {
    Swal.fire({
      icon: 'error',
      title: 'Validasi Gagal',
      html: `<ul style="text-align:left;margin:0;padding-left:18px;">${validationErrors.map((msg) => `<li>${msg}</li>`).join('')}</ul>`,
      confirmButtonColor: '#41A67E'
    });
  }
});
</script>
@endsection
