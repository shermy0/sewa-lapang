@extends('layouts.master')

@section('title', 'Petugas Kasir')

@section('content')
  <div class="row gx-4">
    <!-- GRID LAPANGAN -->
    <div class="col-lg-8">
      <div class="d-flex justify-content-between mb-3">
        <input id="searchInput" class="form-control form-control-sm d-none d-md-block"
           placeholder="Cari Lapangan" style="border-radius:20px;max-width:200px;">
        <select id="filterKategori" name="id_kategori" class="form-select form-select-sm" style="max-width:150px;" required>
            <option value="all" selected>Semua Kategori</option>
            @foreach ($kategori as $kat)
                <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
            @endforeach
        </select>
      </div>
      <div id="grid" class="row g-3"></div>
      <nav class="mt-3">
        <ul id="gridPagination" class="pagination pagination-sm justify-content-center"></ul>
      </nav>
    </div>

    <div class="col-lg-4">
      <div class="cart">
        <!-- INPUT NAMA PENYEWA -->
        <div class="mb-3 position-relative">
          <label class="fw-semibold mb-1">Nama Penyewa</label>
          <input 
            type="text" 
            id="searchPenyewa" 
            class="form-control" 
            placeholder="Cari nama penyewa..."
            autocomplete="off"
            required>
          <ul id="penyewaResults" class="list-group position-absolute w-100" 
              style="z-index: 1050; display: none; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></ul>
        </div>

        <div class="d-flex justify-content-between mb-2">
          <h6 class="mb-0">Daftar Pesanan</h6>
          <small id="cartCount">0 item</small>
        </div>

        <ul id="orderList" class="list-group list-group-flush mb-2"></ul>

        <div>
          <div class="d-flex justify-content-between">
            <span>Subtotal</span>
            <span id="subtotal">Rp 0</span>
          </div>
          <hr>
          <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">Total</small>
            <div class="fs-5 fw-bold" id="totalPrice">Rp 0</div>
          </div>
          <button id="payBtn" class="btn btn-pay w-100 mt-2">Bayar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL JADWAL -->
  <div class="modal fade" id="jadwalModal" tabindex="-1" aria-labelledby="jadwalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="jadwalModalLabel">Jadwal Tersedia</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-2 mb-3 align-items-end">
            <div class="col-4">
              <label for="filterTanggal" class="form-label fw-semibold mb-1">Tanggal</label>
              <input type="date" id="filterTanggal" class="form-control form-control-sm">
            </div>
            <div class="col-4">
              <label for="filterJamMulai" class="form-label fw-semibold mb-1">Jam Mulai</label>
              <input type="time" id="filterJamMulai" class="form-control form-control-sm">
            </div>
            <div class="col-4 d-flex">
              <button class="btn btn-success w-100 form-control-sm" id="resetFilters">
                <i class="fa fa-rotate-left me-1"></i> Reset Filter
              </button>
            </div>
          </div>

          <div id="jadwalContent">
            <p class="text-center text-muted">Memuat jadwal...</p>
          </div>

          <div class="d-flex justify-content-between align-items-center mt-2">
            <div id="paginationSummary"></div>
            <ul id="jadwalPagination" class="pagination pagination-sm mb-0"></ul>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
          <button type="button" class="btn btn-success btn-sm" id="pesanBtn">Pesan</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL PEMBAYARAN -->
  <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="paymentModalLabel">Pilih Metode Pembayaran</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <ul class="list-group">
            <li class="list-group-item">
              <input type="radio" name="paymentMethod" value="cash" id="payCash" checked>
              <label for="payCash">Cash</label>
            </li>
            <li class="list-group-item">
              <input type="radio" name="paymentMethod" value="midtrans" id="payMidtrans">
              <label for="payMidtrans">Midtrans</label>
            </li>
          </ul>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-success" id="confirmPaymentBtn">Bayar</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
<script>
const lapanganData = @json($lapangan);
let cart = [];
let perPage = 9;
let page = 1;
let filterKategori = "all";

document.addEventListener("DOMContentLoaded", () => {

  // ======== FUNGSI UTILITAS ========
  function getCurrentTime() {
    const now = new Date();
    return now.toTimeString().slice(0, 5); // Format HH:MM
  }

  function getCurrentDate() {
    const now = new Date();
    return now.toISOString().split('T')[0]; // Format YYYY-MM-DD
  }

  function isToday(dateString) {
    return dateString === getCurrentDate();
  }

  function isFutureDate(dateString) {
    return dateString > getCurrentDate();
  }

  function shouldFilterByTime(dateString, jamMulai) {
    // Hanya filter berdasarkan waktu jika tanggalnya hari ini
    if (isToday(dateString)) {
      const currentTime = getCurrentTime();
      return jamMulai >= currentTime;
    }
    // Untuk tanggal besok atau seterusnya, tampilkan semua jam
    return true;
  }

  // ======== RENDER GRID LAPANGAN ========
  function renderGrid(){
    const grid = document.getElementById("grid");
    grid.innerHTML = "";
    const q = document.getElementById("searchInput").value.toLowerCase();

    let items = lapanganData.map(l => ({ 
      ...l, 
      foto: l.foto || [], 
      hargaRataRata: l.hargaRataRata, 
      kategori_nama: l.kategori_nama || 'Tidak ada'
    }));
    
    if(filterKategori !== "all") items = items.filter(l => l.id_kategori == filterKategori);
    if(q) items = items.filter(l => l.nama.toLowerCase().includes(q));

    const totalPages = Math.ceil(items.length / perPage) || 1;
    page = Math.min(page, totalPages);
    const start = (page - 1) * perPage;
    const pageItems = items.slice(start, start + perPage);

    pageItems.forEach(l => {
      const col = document.createElement("div");
      col.className = "col-md-4";
      
      let fotoHTML = l.foto.length > 1 ? `
        <div id="carousel${l.id}" class="carousel slide">
          <div class="carousel-inner">
            ${l.foto.map((f,idx) => `
              <div class="carousel-item ${idx === 0 ? 'active' : ''}">
                <img src="/storage/${f}" class="lapangan-img">
              </div>
            `).join('')}
          </div>
        </div>` : l.foto.length === 1 ? 
        `<img src="/storage/${l.foto[0]}" class="lapangan-img">` : 
        `<img src="/no-image.jpg" class="lapangan-img">`;

      col.innerHTML = `
        <div class="card lapangan-card" data-id="${l.id}">
          ${fotoHTML}
          <div class="card-body py-2 px-2">
            <h6 class="card-title mb-1">${l.nama}</h6>
            ${l.deskripsi ? `<p class="text-truncate mb-1" style="font-size:0.85rem;">${l.deskripsi}</p>` : ''}
            <div class="mt-1 d-flex justify-content-between">
              <small>Harga: </small>
              <small style="color:#41A67E;font-weight:600;">
                ${l.hargaRataRata ? `Rp. ${l.hargaRataRata.toLocaleString('id-ID')} /jam` : '-'}
              </small>
            </div>
          </div>
        </div>
      `;

      col.querySelector(".lapangan-card").addEventListener("click", () => openJadwalModal(l));
      grid.appendChild(col);
    });

    renderGridPagination(totalPages);
  }

  function renderGridPagination(totalPages){
    const pg = document.getElementById("gridPagination");
    if(!pg) return; 
    
    pg.innerHTML = "";
    
    const createPageItem = (num, active = false, disabled = false) => {
      const li = document.createElement("li");
      li.className = "page-item " + (active ? "active" : "") + (disabled ? " disabled" : "");
      li.innerHTML = `<a href="#" class="page-link">${num}</a>`;
      if(!disabled && !active){
        li.querySelector('a').addEventListener("click", e => {
          e.preventDefault(); 
          page = num; 
          renderGrid();
        });
      }
      return li;
    }

    pg.appendChild(createPageItem("«", false, page === 1));
    for(let i = 1; i <= totalPages; i++){
      pg.appendChild(createPageItem(i, i === page));
    }
    pg.appendChild(createPageItem("»", false, page === totalPages));
  }

  // ======== CART ========
  function addToCart(item){
    const exist = cart.find(c => 
      c.id === item.id && 
      c.jam_mulai === item.jam_mulai && 
      c.tanggal === item.tanggal
    );
    
    if(exist) {
      exist.durasi += item.durasi;
    } else {
      cart.push({...item});
    }
    renderCart();
  }

  function renderCart(){
    const list = document.getElementById("orderList");
    list.innerHTML = "";

    if(cart.length === 0){
      list.innerHTML = '<li class="list-group-item text-center text-muted">Belum ada pesanan</li>';
    } else {
      cart.forEach((it, index) => {
        const li = document.createElement("li");
        li.className = "list-group-item py-2 d-flex justify-content-between align-items-center";
        li.innerHTML = `
          <div>
            <div class="fw-bold">${it.nama}</div>
            <div class="small text-muted">${it.jam_mulai} • ${it.tanggal}</div>
            <div class="fw-bold">Rp ${Number(it.harga).toLocaleString('id-ID')}</div>
          </div>
          <button type="button" class="btn btn-sm btn-danger btn-remove">&times;</button>
        `;

        li.querySelector('.btn-remove').addEventListener('click', () => {
          cart.splice(index, 1);
          renderCart();
        });

        list.appendChild(li);
      });
    }

    document.getElementById("cartCount").innerText = cart.length + " item";
    updateTotals();
  }

  function updateTotals(){
    const subtotal = cart.reduce((s, i) => s + i.harga * i.durasi, 0);
    document.getElementById("subtotal").innerText = "Rp " + subtotal.toLocaleString('id-ID');
    document.getElementById("totalPrice").innerText = "Rp " + subtotal.toLocaleString('id-ID');
  }

  // ======== PEMBAYARAN ========
  document.getElementById('payBtn').addEventListener('click', function () {
    const penyewaInput = document.getElementById('searchPenyewa');
    const penyewaId = penyewaInput.dataset.id;
    
    if(!penyewaId) { 
      alert("Silakan pilih penyewa terlebih dahulu!"); 
      penyewaInput.focus();
      return; 
    }
    if(cart.length === 0){ 
      alert("Keranjang kosong!"); 
      return; 
    }

    const paymentModalEl = document.getElementById('paymentModal');
    const paymentModal = new bootstrap.Modal(paymentModalEl);
    paymentModal.show();
  });

  document.getElementById('confirmPaymentBtn').addEventListener('click', async function() {
    const method = document.querySelector('input[name="paymentMethod"]:checked').value;
    const penyewaInput = document.getElementById('searchPenyewa');
    const penyewaId = penyewaInput.dataset.id;
    
    if(!penyewaId) { 
      alert("Silakan pilih penyewa terlebih dahulu!"); 
      return; 
    }
    if(cart.length === 0){ 
      alert("Keranjang kosong!"); 
      return; 
    }

    // Prepare data
    const itemsForServer = cart.map(i => ({
      id: i.lapangan_id || i.id, 
      jadwal_id: i.jadwal_id,
      harga: i.harga,
      durasi: i.durasi
    }));
    
    const total = cart.reduce((s, i) => s + i.harga * i.durasi, 0);

    const payload = {
      penyewa_id: penyewaId,
      items: itemsForServer,
      total: total,
      kasir: '{{ Auth::user()->name }}'
    };

    try {
      let url = '';
      if(method === 'cash') {
        url = "{{ route('petugas.store.cash') }}";
      } else {
        url = "{{ route('petugas.store.midtrans') }}";
      }

      const res = await fetch(url, {
        method: "POST",
        headers: { 
          "Content-Type": "application/json", 
          "Accept": "application/json",
          "X-CSRF-TOKEN": "{{ csrf_token() }}" 
        },
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      
      if(!data.success) throw new Error(data.message || 'Gagal memproses pembayaran');

      if(method === 'cash'){
        alert("Pemesanan Cash Berhasil!");
        cart = [];
        renderCart();
        const paymentModalEl = document.getElementById('paymentModal');
        const modal = bootstrap.Modal.getInstance(paymentModalEl);
        if(modal) modal.hide();
        
        if(typeof refreshJadwal === "function") refreshJadwal();
      } else {
        const paymentModalEl = document.getElementById('paymentModal');
        const modal = bootstrap.Modal.getInstance(paymentModalEl);
        if(modal) modal.hide();

        if(data.snap_token){
          snap.pay(data.snap_token, {
            onSuccess: function(result){
              fetch("{{ route('petugas.payment.check') }}", {
                method: "POST",
                headers: {
                  "Content-Type": "application/json",
                  "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ order_ids: data.orders })
              }).then(() => {
                alert("Pembayaran Berhasil!");
                cart = [];
                renderCart();
                if(typeof refreshJadwal === "function") refreshJadwal();
              });
            },
            onPending: function(result){
              alert("Menunggu Pembayaran...");
              cart = [];
              renderCart();
            },
            onError: function(result){
              alert("Pembayaran Gagal!");
            },
            onClose: function(){
              alert('Anda menutup popup tanpa menyelesaikan pembayaran');
            }
          });
        } else {
          alert("Token pembayaran tidak ditemukan");
        }
      }
    } catch(err) {
      console.error(err);
      alert("Terjadi kesalahan: " + err.message);
    }
  });

  // ======== MODAL JADWAL & CART HANDLING ========
  let jadwalData = [];
  let currentPage = 1;
  const rowsPerPage = 6;
  let currentLapanganId = null;

  function openJadwalModal(lapangan){
    const modalEl = document.getElementById('jadwalModal');
    currentLapanganId = lapangan.id;
    modalEl.dataset.lapanganId = lapangan.id;
    const modal = new bootstrap.Modal(modalEl);
    
    document.getElementById('jadwalModalLabel').innerText = lapangan.nama;
    
    // Set tanggal default ke hari ini
    const today = getCurrentDate();
    document.getElementById('filterTanggal').value = today;
    document.getElementById('filterJamMulai').value = '';
    
    modal.show();
    loadJadwalData(lapangan.id, today, '');

    // Event listeners untuk modal
    setupModalEventListeners(lapangan, modal);
  }

  function setupModalEventListeners(lapangan, modal) {
    const filterTanggal = document.getElementById('filterTanggal');
    const filterJamMulai = document.getElementById('filterJamMulai');
    const resetBtn = document.getElementById('resetFilters');
    const pesanBtn = document.getElementById('pesanBtn');

    // Hapus event listener lama jika ada
    const newFilterTanggal = filterTanggal.cloneNode(true);
    const newFilterJamMulai = filterJamMulai.cloneNode(true);
    const newResetBtn = resetBtn.cloneNode(true);
    
    filterTanggal.parentNode.replaceChild(newFilterTanggal, filterTanggal);
    filterJamMulai.parentNode.replaceChild(newFilterJamMulai, filterJamMulai);
    resetBtn.parentNode.replaceChild(newResetBtn, resetBtn);

    // Event listener untuk filter tanggal
    newFilterTanggal.addEventListener('change', function() {
      currentPage = 1;
      const jamMulaiValue = isToday(this.value) ? getCurrentTime() : '';
      document.getElementById('filterJamMulai').value = jamMulaiValue;
      loadJadwalData(currentLapanganId, this.value, jamMulaiValue);
    });

    // Event listener untuk filter jam mulai
    newFilterJamMulai.addEventListener('change', function() {
      currentPage = 1;
      loadJadwalData(currentLapanganId, document.getElementById('filterTanggal').value, this.value);
    });

    // Event listener untuk reset filter
    newResetBtn.addEventListener('click', function() {
      const today = getCurrentDate();
      document.getElementById('filterTanggal').value = today;
      document.getElementById('filterJamMulai').value = '';
      currentPage = 1;
      loadJadwalData(currentLapanganId, today, '');
    });

    // Event listener untuk tombol pesan
    pesanBtn.onclick = function() {
      const checkedSlots = document.querySelectorAll('.slot-checkbox:checked');
      if (checkedSlots.length === 0) {
        alert('Pilih setidaknya satu jadwal untuk dipesan!');
        return;
      }

      checkedSlots.forEach(chk => {
        const card = chk.closest('.card');
        const jamText = card.querySelector('.jam-text').textContent;
        const jamParts = jamText.split(' - ');
        const hargaText = card.querySelector('.harga-text').textContent.replace(/Rp\s|[.]/g,'');
        const tanggalText = card.querySelector('.tanggal-text').textContent;

        addToCart({
          lapangan_id: lapangan.id,
          nama: lapangan.nama,
          harga: parseInt(hargaText),
          jam_mulai: jamParts[0],
          tanggal: tanggalText,
          durasi: 1,
          jadwal_id: parseInt(chk.dataset.jadwalId)
        });
      });

      modal.hide();
      renderCart();
    };
  }

  function loadJadwalData(lapanganId, tanggal, jamMulai) {
    const content = document.getElementById('jadwalContent');
    content.innerHTML = '<p class="text-center text-muted">Memuat jadwal...</p>';
    
    // Build query parameters
    let url = `/petugas/api/jadwal/${lapanganId}`;
    const params = new URLSearchParams();
    if (tanggal) params.append('tanggal', tanggal);
    if (jamMulai) params.append('jam_mulai', jamMulai);
    
    if (params.toString()) {
      url += '?' + params.toString();
    }

    fetch(url)
      .then(res => res.json())
      .then(data => { 
        jadwalData = data || [];
        renderJadwalPage(currentPage);
      })
      .catch(err => { 
        content.innerHTML = '<p class="text-center text-danger">Gagal memuat jadwal</p>'; 
        console.error(err); 
        jadwalData = [];
      });
  }

  function renderJadwalPage(page) {
    const content = document.getElementById('jadwalContent');
    const summaryEl = document.getElementById('paginationSummary');
    
    const totalPages = Math.ceil(jadwalData.length / rowsPerPage) || 1;
    currentPage = Math.min(Math.max(1, page), totalPages);
    const start = (currentPage - 1) * rowsPerPage;
    const pageData = jadwalData.slice(start, start + rowsPerPage);

    content.innerHTML = '';
    
    if (pageData.length === 0) {
      content.innerHTML = `
        <div class="text-center text-muted py-4">
          <i class="fas fa-calendar-times fa-3x mb-3"></i>
          <p>Tidak ada jadwal tersedia untuk kriteria yang dipilih</p>
        </div>
      `;
    } else {
      const grid = document.createElement('div');
      grid.className = 'row g-3';

      pageData.forEach(j => {
        const col = document.createElement('div');
        col.className = 'col-md-4';

        let statusClass = "", statusText = "", isAvailable = false;
        
        if (j.booking_status === "dibayar") { 
          statusClass = "bg-secondary text-white"; 
          statusText = "Sudah Dibayar"; 
        } else if (j.booking_status === "menunggu") { 
          statusClass = "bg-warning text-dark"; 
          statusText = "Sedang Dibooking"; 
        } else {
          statusClass = "bg-light text-dark border";
          isAvailable = true;
          // Format tanggal untuk ditampilkan
          const tanggalObj = new Date(j.tanggal);
          statusText = tanggalObj.toLocaleDateString('id-ID', { 
            day: '2-digit', 
            month: 'short', 
            year: 'numeric' 
          });
        }

        const card = document.createElement('div');
        card.className = `card p-2 text-center ${statusClass}`;
        card.style.cursor = isAvailable ? 'pointer' : 'default';
        card.style.borderRadius = '8px';
        card.style.minHeight = '120px';
        card.style.display = 'flex';
        card.style.flexDirection = 'column';
        card.style.justifyContent = 'center';

        if (isAvailable) {
          card.innerHTML = `
            <div class="jam-text fw-bold">${j.jam_mulai} - ${j.jam_selesai}</div>
            <div class="mt-1 fw-bold harga-text text-success">Rp ${Number(j.harga_sewa).toLocaleString('id-ID')}</div>
            <div class="form-check mt-1">
              <input class="form-check-input slot-checkbox" type="checkbox" data-jadwal-id="${j.id}">
              <label class="form-check-label small tanggal-text">${statusText}</label>
            </div>
          `;
          
          // Toggle selection on card click
          card.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
              const checkbox = this.querySelector('.slot-checkbox');
              checkbox.checked = !checkbox.checked;
              
              // Visual feedback
              if (checkbox.checked) {
                this.style.backgroundColor = '#e8f5e8';
                this.style.borderColor = '#28a745';
              } else {
                this.style.backgroundColor = '';
                this.style.borderColor = '';
              }
            }
          });
        } else {
          card.innerHTML = `
            <div class="jam-text">${j.jam_mulai} - ${j.jam_selesai}</div>
            <div class="mt-1 fw-bold harga-text">Rp ${Number(j.harga_sewa).toLocaleString('id-ID')}</div>
            <div class="small">${statusText}</div>
          `;
        }

        col.appendChild(card);
        grid.appendChild(col);
      });

      content.appendChild(grid);
    }

    // Update pagination summary
    const startItem = jadwalData.length > 0 ? start + 1 : 0;
    const endItem = Math.min(start + rowsPerPage, jadwalData.length);
    summaryEl.textContent = jadwalData.length === 0 
      ? 'Tidak ada jadwal tersedia' 
      : `Menampilkan ${startItem} - ${endItem} dari ${jadwalData.length} jadwal | Halaman ${currentPage} / ${totalPages}`;

    // Render pagination
    renderJadwalPagination(totalPages);
  }

  function renderJadwalPagination(totalPages) {
    const paginationEl = document.getElementById('jadwalPagination');
    paginationEl.innerHTML = '';

    if (totalPages <= 1) return;

    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `<a class="page-link" href="#" aria-label="Previous">&laquo;</a>`;
    if (currentPage > 1) {
      prevLi.querySelector('a').addEventListener('click', (e) => {
        e.preventDefault();
        renderJadwalPage(currentPage - 1);
      });
    }
    paginationEl.appendChild(prevLi);

    // Page numbers
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    if (endPage - startPage + 1 < maxVisiblePages) {
      startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
      const pageLi = document.createElement('li');
      pageLi.className = `page-item ${i === currentPage ? 'active' : ''}`;
      pageLi.innerHTML = `<a class="page-link" href="#">${i}</a>`;
      
      if (i !== currentPage) {
        pageLi.querySelector('a').addEventListener('click', (e) => {
          e.preventDefault();
          renderJadwalPage(i);
        });
      }
      
      paginationEl.appendChild(pageLi);
    }

    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = `<a class="page-link" href="#" aria-label="Next">&raquo;</a>`;
    if (currentPage < totalPages) {
      nextLi.querySelector('a').addEventListener('click', (e) => {
        e.preventDefault();
        renderJadwalPage(currentPage + 1);
      });
    }
    paginationEl.appendChild(nextLi);
  }

  // ======== EVENT FILTER & SEARCH ========
  document.getElementById("filterKategori").addEventListener("change", e => {
    filterKategori = e.target.value;
    page = 1;
    renderGrid();
  });
  
  document.getElementById("searchInput").addEventListener("input", () => { 
    page = 1; 
    renderGrid(); 
  });

  // ======== SEARCH PENYEWA ========
  const penyewaInput = document.getElementById("searchPenyewa");
  const list = document.getElementById("penyewaResults");

  penyewaInput.addEventListener("input", function () {
    let q = this.value;
    // Clear ID when user types to force selection
    this.removeAttribute('data-id'); 
    
    if(q.length < 1){ 
      list.style.display = "none"; 
      return; 
    }

    fetch(`/petugas/penyewa/search?q=` + encodeURIComponent(q))
      .then(res => res.json())
      .then(data => {
        list.innerHTML = "";
        
        if(data.length === 0){ 
          let item = document.createElement("li");
          item.className = "list-group-item text-muted small";
          item.textContent = "Penyewa tidak ditemukan";
          list.appendChild(item);
        } else {
          data.forEach(p => {
            let item = document.createElement("li");
            item.className = "list-group-item list-group-item-action";
            item.style.cursor = "pointer";
            item.textContent = p.name;
            item.onclick = () => { 
              penyewaInput.value = p.name; 
              penyewaInput.dataset.id = p.id; 
              list.style.display = "none"; 
            };
            list.appendChild(item);
          });
        }
        list.style.display = "block";
      })
      .catch(err => console.error(err));
  });

  // Hide dropdown when clicking outside
  document.addEventListener('click', function(e) {
    if (!penyewaInput.contains(e.target) && !list.contains(e.target)) {
      list.style.display = 'none';
    }
  });

  // ======== INIT ========
  renderGrid();
  renderCart();
});
</script>
@endpush
