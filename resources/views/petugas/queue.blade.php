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
          <div class="border rounded p-2 mb-3 bg-light">
            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-semibold">Total yang harus dibayar</span>
              <span class="fs-5 fw-bold text-success" id="paymentTotal">Rp 0</span>
            </div>
          </div>

          <ul class="list-group mb-3">
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <input type="radio" name="paymentMethod" value="cash" id="payCash" checked>
                <label for="payCash" class="fw-semibold mb-0">Cash</label>
                <div class="small text-muted">Bayar tunai di kasir</div>
              </div>
              <i class="fa-solid fa-money-bill-wave text-success"></i>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <input type="radio" name="paymentMethod" value="midtrans" id="payMidtrans">
                <label for="payMidtrans" class="fw-semibold mb-0">Midtrans</label>
                <div class="small text-muted">Bayar via payment gateway</div>
              </div>
              <i class="fa-solid fa-credit-card text-primary"></i>
            </li>
          </ul>

          <div id="cashSection" class="border rounded p-3 bg-white">
            <div class="mb-2">
              <label class="form-label fw-semibold mb-1">Uang diterima</label>
              <input type="number" min="0" class="form-control" id="cashReceived" placeholder="Masukkan nominal">
            </div>
            <div class="d-flex justify-content-between">
              <span class="text-muted">Kembalian</span>
              <span class="fw-bold" id="cashChange">Rp 0</span>
            </div>
            <div class="small text-muted mt-1" id="cashHint">Pastikan uang diterima ≥ total.</div>
          </div>
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

  // ======== RENDER GRID LAPANGAN ========
  function renderGrid(){
    const grid = document.getElementById("grid");
    grid.innerHTML = "";
    const q = document.getElementById("searchInput").value.toLowerCase();

    let items = lapanganData.map(l => ({ ...l, foto: l.foto || [], hargaRataRata: l.hargaRataRata, kategori_nama: l.kategori_nama || 'Tidak ada'}));
    if(filterKategori!=="all") items = items.filter(l=>l.id_kategori==filterKategori);
    if(q) items = items.filter(l=>l.nama.toLowerCase().includes(q));

    const totalPages = Math.ceil(items.length/perPage) || 1;
    page = Math.min(page,totalPages);
    const start = (page-1)*perPage;
    const pageItems = items.slice(start,start+perPage);

    pageItems.forEach(l=>{
      const col = document.createElement("div");
      col.className = "col-md-4";
      let fotoHTML = l.foto.length>1?`
        <div id="carousel${l.id}" class="carousel slide">
          <div class="carousel-inner">
            ${l.foto.map((f,idx)=>`<div class="carousel-item ${idx===0?'active':''}"><img src="/storage/${f}" class="lapangan-img"></div>`).join('')}
          </div>
        </div>` : l.foto.length===1?`<img src="/storage/${l.foto[0]}" class="lapangan-img">`:`<img src="/no-image.jpg" class="lapangan-img">`;

      col.innerHTML = `<div class="card lapangan-card" data-id="${l.id}">${fotoHTML}
        <div class="card-body py-2 px-2">
          <h6 class="card-title mb-1">${l.nama}</h6>
          ${l.deskripsi?`<p class="text-truncate mb-1" style="font-size:0.85rem;">${l.deskripsi}</p>`:''}
          <div class="mt-1 d-flex justify-content-between">
            <small>Harga: </small>
            <small style="color:#41A67E;font-weight:600;">${l.hargaRataRata?`Rp. ${l.hargaRataRata.toLocaleString('id-ID')} /jam`:'-'}</small>
          </div>
        </div>
      </div>`;

      col.querySelector(".lapangan-card").addEventListener("click",()=>openJadwalModal(l));
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
      li.className="page-item "+(active?"active":"")+(disabled?" disabled":"");
      li.innerHTML=`<a href="#" class="page-link">${num}</a>`;
      if(!disabled && !active){
        li.querySelector('a').addEventListener("click",e=>{
          e.preventDefault(); page=num; renderGrid();
        });
      }
      return li;
    }

    pg.appendChild(createPageItem("«", false, page===1));
    for(let i=1;i<=totalPages;i++){
      pg.appendChild(createPageItem(i,i===page));
    }
    pg.appendChild(createPageItem("»", false, page===totalPages));
  }

  // ======== CART ========
  function getCartTotal(){
    return cart.reduce((s, i) => s + i.harga * i.durasi, 0);
  }

  function addToCart(item){
    const exist = cart.find(c=>c.id===item.id && c.jam_mulai===item.jam_mulai && c.tanggal===item.tanggal);
    if(exist) exist.durasi+=item.durasi;
    else cart.push({...item});
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

        li.querySelector('.btn-remove').addEventListener('click', ()=>{
            // Jika item berasal dari DB, hapus via API agar tidak hilang saat refresh
            if (it.persisted && it.id) {
                fetch(`/petugas/cart-temp/${it.id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(() => renderCartFromDB())
                .catch(() => {
                    cart.splice(index, 1);
                    renderCart();
                });
            } else {
                cart.splice(index, 1);
                renderCart();
            }
        });

        list.appendChild(li);
      });
    }

    document.getElementById("cartCount").innerText = cart.length + " item";
    updateTotals();
  }

  function updateTotals(){
    const subtotal = getCartTotal();
    document.getElementById("subtotal").innerText = "Rp " + subtotal.toLocaleString('id-ID');
    document.getElementById("totalPrice").innerText = "Rp " + subtotal.toLocaleString('id-ID');
  }

  // ======== PEMBAYARAN ========
  const paymentTotalEl = document.getElementById('paymentTotal');
  const cashSection = document.getElementById('cashSection');
  const cashReceivedEl = document.getElementById('cashReceived');
  const cashChangeEl = document.getElementById('cashChange');
  const cashHintEl = document.getElementById('cashHint');

  const swalWarn = (msg) => Swal.fire('Perhatian', msg, 'warning');
  const swalError = (msg) => Swal.fire('Error', msg, 'error');
  const swalInfo = (msg) => Swal.fire('Info', msg, 'info');
  const swalSuccess = (msg) => Swal.fire('Berhasil', msg, 'success');

  function renderCashSection(){
    const total = getCartTotal();
    if(paymentTotalEl) paymentTotalEl.textContent = "Rp " + total.toLocaleString('id-ID');
    if(!cashReceivedEl) return;
    const cash = Number(cashReceivedEl.value || 0);
    const change = cash - total;
    cashChangeEl.textContent = "Rp " + Math.max(change,0).toLocaleString('id-ID');
    if(change < 0){
      cashHintEl.textContent = "Uang kurang Rp " + Math.abs(change).toLocaleString('id-ID');
      cashHintEl.classList.remove('text-muted');
      cashHintEl.classList.add('text-danger');
    } else {
      cashHintEl.textContent = "Pastikan uang diterima ≥ total.";
      cashHintEl.classList.remove('text-danger');
      cashHintEl.classList.add('text-muted');
    }
  }

  document.querySelectorAll('input[name=\"paymentMethod\"]').forEach(r => {
    r.addEventListener('change', () => {
      if(cashSection){
        cashSection.style.display = r.value === 'cash' && r.checked ? 'block' : 'none';
      }
    });
  });

  if(cashReceivedEl){
    cashReceivedEl.addEventListener('input', renderCashSection);
    cashReceivedEl.addEventListener('change', renderCashSection);
  }

  document.getElementById('payBtn').addEventListener('click', function () {
    const penyewaInput = document.getElementById('searchPenyewa');
    const radioCash = document.getElementById('payCash');
    const radioMidtrans = document.getElementById('payMidtrans');
    const paymentModalEl = document.getElementById('paymentModal');

    // Tampilkan modal dulu
    const paymentModal = new bootstrap.Modal(paymentModalEl);
    paymentModal.show();

    // Reset default radio & input cash
    if(cashReceivedEl){
      if(radioCash){ radioCash.checked = true; }
      if(radioMidtrans){ radioMidtrans.checked = false; }
      cashSection.style.display = 'block';
      cashReceivedEl.value = getCartTotal();
      renderCashSection();
    }

    // Delay validasi supaya modal muncul dulu
    setTimeout(() => {
      const penyewaId = penyewaInput.dataset.id;
      if(cart.length === 0){
        swalWarn("Keranjang kosong!");
        return;
      }
    }, 200);
  });

  // tombol konfirmasi di dalam modal
  document.getElementById('confirmPaymentBtn').addEventListener('click', async function() {
    const method = document.querySelector('input[name="paymentMethod"]:checked').value;
    const penyewaInput = document.getElementById('searchPenyewa');
    const penyewaId = penyewaInput.dataset.id;
    const total = getCartTotal();

    if(!penyewaId) {
      swalWarn("Silakan pilih penyewa terlebih dahulu!");
      return;
    }
    if(cart.length === 0){
      swalWarn("Keranjang kosong!");
      return;
    }
    if(method === 'cash'){
      const cash = Number(cashReceivedEl?.value || 0);
      if(cash < total){
        swalWarn("Uang diterima kurang dari total.");
        return;
      }
    }

    // Prepare data
    const itemsForServer = cart.map(i => ({
      id: i.lapangan_id || i.id,
      jadwal_id: i.jadwal_id,
      harga: i.harga,
      durasi: i.durasi
    }));
    const cartSnapshot = cart.map(i => ({...i}));

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
        swalSuccess("Pemesanan cash berhasil!");
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
                swalSuccess("Pembayaran berhasil!");
                cart = [];
                renderCart();
                if(typeof refreshJadwal === "function") refreshJadwal();
              });
            },
            onPending: function(result){
              swalInfo("Menunggu Pembayaran...");
              cart = [];
              renderCart();
            },
            onError: function(result){
              swalError("Pembayaran Gagal!");
            },
            onClose: function(){
              swalWarn('Anda menutup popup tanpa menyelesaikan pembayaran');
            }
          });
        } else {
          swalError("Token pembayaran tidak ditemukan");
        }
      }
    } catch(err) {
      console.error(err);
      swalError("Terjadi kesalahan: " + err.message);
    }
  });


  // ======== MODAL JADWAL & CART HANDLING ========
  let jadwalData = [];
let currentPage = 1;
const rowsPerPage = 6;
let currentLapanganId = null;

function openJadwalModal(lapangan) {
    currentLapanganId = lapangan.id;
    const modalEl = document.getElementById('jadwalModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    const content = document.getElementById('jadwalContent');
    const filterTanggal = document.getElementById('filterTanggal');
    const filterJamMulai = document.getElementById('filterJamMulai');
    const resetBtn = document.getElementById('resetFilters');
    const pesanBtn = document.getElementById('pesanBtn');
    const summaryEl = document.getElementById('paginationSummary');

    // Set default tanggal hari ini
    const today = new Date().toISOString().split('T')[0];
    filterTanggal.value = today;
    filterJamMulai.value = '';

    function fetchJadwal() {
        let url = `/petugas/api/jadwal/${currentLapanganId}?tanggal=${filterTanggal.value}`;
        if (filterJamMulai.value) url += `&jam_mulai=${filterJamMulai.value}`;

        content.innerHTML = '<p class="text-center text-muted">Memuat jadwal...</p>';

        fetch(url)
        .then(res => res.json())
        .then(data => {
            jadwalData = data || [];
            currentPage = 1;
            renderJadwalPage(currentPage);
        })
        .catch(err => {
            content.innerHTML = '<p class="text-center text-danger">Gagal memuat jadwal</p>';
            console.error(err);
        });
    }

    function renderJadwalPage(page) {
        const totalPages = Math.ceil(jadwalData.length / rowsPerPage) || 1;
        currentPage = Math.min(Math.max(1, page), totalPages);
        const start = (currentPage - 1) * rowsPerPage;
        const pageData = jadwalData.slice(start, start + rowsPerPage);

        content.innerHTML = '';
        if (pageData.length === 0) {
            content.innerHTML = '<p class="text-center text-muted">Tidak ada jadwal tersedia</p>';
        } else {
            const grid = document.createElement('div');
            grid.className = 'row g-3';

            pageData.forEach(j => {
                const col = document.createElement('div');
                col.className = 'col-md-4';

                let statusClass = '', statusText = '';
                if (j.booking_status === "dibayar") {
                    statusClass = "bg-success text-white"; statusText = "Sudah Dibayar";
                } else if (j.booking_status === "menunggu") {
                    statusClass = "bg-warning text-dark"; statusText = "Sedang Dibooking";
                } else if (j.booking_status === "tidak_tersedia") {
                    statusClass = "bg-secondary text-white"; statusText = "Tidak Tersedia";
                } else {
                    statusClass = "bg-light text-dark";
                    const tanggalObj = new Date(j.tanggal);
                    statusText = tanggalObj.toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' });
                }

                const card = document.createElement('div');
                card.className = `card p-2 text-center ${statusClass}`;
                card.style.cursor = (j.booking_status === "tersedia" || !j.booking_status) ? 'pointer' : 'default';
                card.style.borderRadius = '8px';
                card.style.minHeight = '120px';
                card.style.display = 'flex';
                card.style.flexDirection = 'column';
                card.style.justifyContent = 'center';

                if(j.booking_status !== "dibayar" && j.booking_status !== "menunggu" && j.booking_status !== "tidak_tersedia") {
                    card.innerHTML = `
                        <div class="jam-text fw-bold">${j.jam_mulai} - ${j.jam_selesai}</div>
                        <div class="mt-1 fw-bold harga-text text-success">Rp ${Number(j.harga_sewa).toLocaleString('id-ID')}</div>
                        <div class="form-check mt-1">
                            <input class="form-check-input slot-checkbox" 
                                  type="checkbox" 
                                  data-jadwal-id="${j.id}" 
                                  data-tanggal="${j.tanggal}" 
                                  data-jam-mulai="${j.jam_mulai}" 
                                  data-harga="${j.harga_sewa}">
                            <label class="form-check-label small">${statusText}</label>
                        </div>
                    `;

                    card.addEventListener('click', function(e) {
                        if(e.target.type !== 'checkbox') {
                            const checkbox = this.querySelector('.slot-checkbox');
                            checkbox.checked = !checkbox.checked;
                            if(checkbox.checked){
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

        summaryEl.textContent = jadwalData.length === 0 ? 'Jadwal tidak tersedia' :
            `Menampilkan ${start+1} - ${Math.min(start+rowsPerPage, jadwalData.length)} dari ${jadwalData.length} jadwal | Halaman ${currentPage} / ${totalPages}`;
    }

    filterTanggal.addEventListener('change', fetchJadwal);
    filterJamMulai.addEventListener('change', fetchJadwal);
    resetBtn.addEventListener('click', () => {
        filterTanggal.value = today;
        filterJamMulai.value = '';
        fetchJadwal();
    });

    pesanBtn.onclick = async () => {
    const selected = content.querySelectorAll('.slot-checkbox:checked');
    if(selected.length === 0){
        Swal.fire('Perhatian', 'Pilih setidaknya satu jadwal untuk dipesan!', 'warning');
        return;
    }

    const penyewaInput = document.getElementById('searchPenyewa');
    const penyewaName = penyewaInput.value || null;
    const penyewaId = penyewaInput.dataset.id || null;

    // Cari data lapangan
    const lapangan = lapanganData.find(l => l.id === currentLapanganId);
    if(!lapangan){
        Swal.fire('Error', 'Data lapangan tidak ditemukan', 'error');
        return;
    }

    for(const chk of selected){
        const payload = {
            lapangan_id: currentLapanganId,
            lapangan_name: lapangan.nama,
            harga: Number(chk.dataset.harga),
            jam_mulai: chk.dataset.jamMulai,
            tanggal: chk.dataset.tanggal,
            nama_penyewa: penyewaName,
            jadwal_id: chk.dataset.jadwalId
        };

        try{
            const res = await fetch("{{ route('petugas.cart-temp.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if(!data.success) throw new Error('Gagal menambahkan ke cart');

            addToCart({
                lapangan_id: payload.lapangan_id,
                nama: payload.lapangan_name,
                harga: payload.harga,
                jam_mulai: payload.jam_mulai,
                tanggal: payload.tanggal,
                durasi: 1,
                jadwal_id: payload.jadwal_id
            });
        } catch(err){
            console.error(err);
            Swal.fire('Error', 'Gagal menambahkan jadwal ke cart', 'error');
        }
    }

    // Tutup modal & render cart
    const modal = bootstrap.Modal.getInstance(document.getElementById('jadwalModal'));
    if(modal) modal.hide();

    renderCart();
};

    fetchJadwal();
}

  // ======== EVENT FILTER & SEARCH ========
  document.getElementById("filterKategori").addEventListener("change", e => {
    filterKategori = e.target.value;
    page = 1;
    renderGrid();
  });
  document.getElementById("searchInput").addEventListener("input", () => { page = 1; renderGrid(); });

  // ======== SEARCH PENYEWA ========
  const penyewaInput = document.getElementById("searchPenyewa");
  const list = document.getElementById("penyewaResults");
  let penyewaTimeout = null;
  const MIN_PENYEWA_LEN = 2;

  function renderPenyewaDropdown(data, options = {}) {
    if (!list) return;
    const headline = options.headline || "";
    const emptyText = options.emptyText || "Penyewa tidak ditemukan";

    list.innerHTML = "";

    if (headline) {
      const headItem = document.createElement("li");
      headItem.className = "list-group-item text-muted small bg-light";
      headItem.textContent = headline;
      list.appendChild(headItem);
    }

    if (data.length === 0) {
      const item = document.createElement("li");
      item.className = "list-group-item text-muted small";
      item.textContent = emptyText;
      list.appendChild(item);
      list.style.display = "block";
      return;
    }

    data.forEach(p => {
      const emailText = p.email ? p.email : "-";
      const phoneText = p.no_hp ? ` • ${p.no_hp}` : "";
      const item = document.createElement("li");
      item.className = "list-group-item list-group-item-action";
      item.dataset.penyewaId = p.id;
      item.style.cursor = "pointer";
      item.innerHTML = `
        <div class="d-flex flex-column">
          <span class="fw-semibold">${p.name}</span>
          <small class="text-muted">${emailText}${phoneText}</small>
        </div>
      `;
      item.addEventListener("click", () => selectPenyewa(p));
      list.appendChild(item);
    });

    list.style.display = "block";
  }

  function selectPenyewa(penyewa) {
    if (!penyewaInput) return;
    penyewaInput.value = penyewa.name;
    penyewaInput.dataset.id = penyewa.id;
    list.style.display = "none";
  }

  function fetchPenyewa(keyword, options = {}) {
    fetch(`/petugas/penyewa/search?q=` + encodeURIComponent(keyword))
      .then(res => res.json())
      .then(data => renderPenyewaDropdown(data, options))
      .catch(err => console.error(err));
  }

  penyewaInput.addEventListener("focus", function () {
    // Saat input kosong, tampilkan 10 penyewa terbaru supaya petugas bisa pilih cepat
    if (!this.value.trim()) {
      this.removeAttribute('data-id');
      fetchPenyewa("", { headline: "Penyewa terbaru" });
    }
  });

  penyewaInput.addEventListener("input", function () {
    const q = this.value.trim();
    this.removeAttribute('data-id');

    if (penyewaTimeout) clearTimeout(penyewaTimeout);

    if (!q) {
      fetchPenyewa("", { headline: "Penyewa terbaru" });
      return;
    }

    if (q.length < MIN_PENYEWA_LEN) {
      renderPenyewaDropdown([], { emptyText: "Ketik minimal 2 huruf" });
      return;
    }

    penyewaTimeout = setTimeout(() => {
      fetchPenyewa(q);
    }, 250);
  });

  // Enter otomatis memilih hasil pertama supaya petugas lebih cepat
  penyewaInput.addEventListener("keydown", function(e) {
    if (e.key === "Enter") {
      const firstItem = list.querySelector("li.list-group-item-action");
      if (firstItem && firstItem.dataset.penyewaId) {
        e.preventDefault();
        firstItem.click();
      }
    }
  });

  // Hide dropdown when clicking outside
  document.addEventListener('click', function(e) {
      if (!penyewaInput.contains(e.target) && !list.contains(e.target)) {
          list.style.display = 'none';
      }
  });

  function renderCartFromDB() {
    fetch('/petugas/cart-temp')
    .then(res => res.json())
    .then(carts => {
        // Sinkronkan cart lokal supaya tetap ada setelah refresh
        cart = carts.map(c => ({
            id: c.id,
            nama: `${c.lapangan_name} - ${c.nama_penyewa || '-'}`,
            jam_mulai: c.jam_mulai,
            tanggal: c.tanggal,
            harga: Number(c.harga) || 0,
            durasi: c.durasi || 1,
            persisted: true
        }));

        renderCart();
    })
    .catch(err => console.error(err));
}

// ======== INIT CART ========
function initCart() {
    renderCartFromDB(); // render cart dari server saat page load
}

// ======== ADD TO CART DARI MODAL ========
async function addToCartFromModal(payload) {
    try {
        const res = await fetch("{{ route('petugas.cart-temp.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if(!data.success) throw new Error('Gagal menambahkan ke cart');
        renderCartFromDB(); // render ulang dari DB
    } catch(err) {
        console.error(err);
        Swal.fire('Error', 'Gagal menambahkan jadwal ke cart', 'error');
    }
}

// ======== PESAN BTN DI MODAL ========
pesanBtn.onclick = async () => {
    const selected = content.querySelectorAll('.slot-checkbox:checked');
    if(selected.length === 0){
        Swal.fire('Perhatian', 'Pilih setidaknya satu jadwal untuk dipesan!', 'warning');
        return;
    }

    const penyewaInput = document.getElementById('searchPenyewa');
    const penyewaName = penyewaInput.value || null;

    const lapangan = lapanganData.find(l => l.id === currentLapanganId);
    if(!lapangan){
        Swal.fire('Error', 'Data lapangan tidak ditemukan', 'error');
        return;
    }

    for(const chk of selected){
        const payload = {
            lapangan_id: currentLapanganId,
            lapangan_name: lapangan.nama,
            harga: Number(chk.dataset.harga),
            jam_mulai: chk.dataset.jamMulai,
            tanggal: chk.dataset.tanggal,
            nama_penyewa: penyewaName,
            jadwal_id: chk.dataset.jadwalId
        };
        await addToCartFromModal(payload); // pakai ini
    }

    const modal = bootstrap.Modal.getInstance(document.getElementById('jadwalModal'));
    if(modal) modal.hide();
};

// ======== INIT ========
initCart();
renderGrid();
});
</script>
@endpush
