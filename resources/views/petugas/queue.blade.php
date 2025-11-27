@extends('layouts.master')

@section('title', 'Petugas Kasir')

@push('styles')
  <style>
    :root{
      --accent:#2f9f6f;
      --accent-dark:#27855d;
      --muted:#9aa5b1;
      --bg:#f5f7fb;
    }

    /* Override Master Layout Topbar */
    .topbar {
        background: var(--accent) !important;
        color: #fff !important;
        border-bottom: none !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    .topbar .brand {
        color: #fff !important;
    }
    .topbar small {
        color: rgba(255,255,255,0.9) !important;
    }

    /* Custom Scrollbar for Queue */
    .queue-wrapper::-webkit-scrollbar {
        height: 6px;
    }
    .queue-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .queue-wrapper::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 10px;
    }
    .queue-wrapper::-webkit-scrollbar-thumb:hover {
        background: #bbb;
    }

    /* Order List Item */
    .queue-item-card {
        background: #fff;
        border: 1px solid #e3e6f0;
        border-radius: 10px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .queue-item-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        border-color: var(--accent);
    }
    .status-bar {
        border-top: 1px solid rgba(0,0,0,0.05);
    }
    
    /* Queue Section Styling */
    .queue-wrapper {
        display: flex;
        gap: 15px;
        overflow-x: auto;
        padding-bottom: 10px;
        scroll-behavior: smooth;
    }
    .queue-card {
        background: #f8f9fc;
        border-radius: 12px;
        padding: 15px;
        border: 1px solid #e3e6f0;
        min-width: 320px;
        flex-shrink: 0;
        margin-bottom: 0; /* Remove bottom margin */
    }
    .queue-card .title {
        font-weight: 700;
        color: #2c3e50;
        font-size: 1rem;
    }
    .queue-card .subtitle {
        font-size: 0.8rem;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    .queue-chip {
        background: #fff;
        border: 1px solid #e3e6f0;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--accent);
        box-shadow: 0 2px 4px rgba(0,0,0,0.03);
    }

    .lapangan-card {
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
      overflow: hidden;
      background: #fff;
      display: flex;
      flex-direction: column;
    }

    .lapangan-card:hover {
      transform: translateY(-4px);
      box-shadow: 8px 20px var(--card-hover);
    }

    /* GAMBAR */
    .lapangan-img,
    .card .carousel-inner,
    .card .carousel-item {
      width: 100%;
      height: 160px; /* Sedikit diperkecil agar lebih compact */
    }

    .lapangan-img {
      object-fit: cover;
    }

    /* Carousel image */
    .card .carousel-inner img {
      width: 100%;
      height: 160px;
      object-fit: cover;
    }

    /* Filter Chips */
    .chip {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        background: #fff;
        border: 1px solid #e3e6f0;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--muted);
        cursor: pointer;
        transition: all 0.2s;
    }
    .chip:hover {
        background: #f8f9fc;
        color: var(--accent);
        border-color: var(--accent);
    }
    .chip.active {
        background: var(--accent);
        color: #fff;
        border-color: var(--accent);
    }

    /* Cart Styling */
    .cart {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        border: 1px solid #e3e6f0;
        position: sticky;
        top: 20px;
    }
    .cart h6 {
        font-weight: 700;
        color: #2c3e50;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.9rem;
    }
    .btn-pay {
        background: var(--accent);
        color: #fff;
        font-weight: 700;
        padding: 12px;
        border-radius: 8px;
        border: none;
        transition: all 0.2s;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .btn-pay:hover {
        background: var(--accent-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(47, 159, 111, 0.3);
    }

    .card .text-truncate {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
  </style>
@endpush

@section('content')
<div class="container-fluid py-3">
  @if(!empty($needsOwner))
    <div class="alert alert-warning mb-3">
      Akun petugas belum dikaitkan dengan pemilik. Minta pemilik membuatkan akun petugas dari menu <strong>Petugas</strong>.
    </div>
  @endif

  <div class="card shadow-sm border-0" style="border-radius:14px">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <div class="text-uppercase text-muted small fw-semibold">Antrean per Section</div>
          <h6 class="fw-bold mb-0">Urutan Penyewa</h6>
        </div>
        <span class="badge bg-primary bg-opacity-10 text-primary">Live</span>
      </div>

      @php
        $statusClasses = [
          'dibayar' => 'bg-success',
          'menunggu' => 'bg-warning text-dark',
          'kadaluarsa' => 'bg-secondary',
          'batal' => 'bg-danger',
        ];
      @endphp

      <div class="queue-wrapper">
        @forelse($sectionQueues as $section)
          <div class="queue-card">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="title">{{ $section['label'] }}</div>
                <div class="subtitle">{{ $section['lapangan'] }}</div>
              </div>
              <span class="queue-chip">{{ $section['queue']->count() }} antrean</span>
            </div>

            @if($section['queue']->isEmpty())
              <div class="queue-empty mt-3 text-muted small">Belum ada pemesanan pada section ini.</div>
            @else
              <div class="d-flex flex-column gap-2 mt-3">
                @foreach($section['queue'] as $order)
                  <div class="queue-item-card shadow-sm">
                    <div class="p-3">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 1rem;">{{ $order['penyewa'] }}</h6>
                            <small class="text-muted fw-semibold" style="font-size: 0.75rem;">{{ $order['kode_tiket'] }}</small>
                        </div>
                        <div class="d-flex align-items-center text-muted small">
                            <i class="fa-regular fa-calendar me-2"></i>
                            <span>{{ $order['tanggal'] }} • {{ $order['jam_mulai'] }} - {{ $order['jam_selesai'] }}</span>
                        </div>
                    </div>
                    <div class="status-bar {{ $statusClasses[$order['status']] ?? 'bg-secondary' }} text-white px-3 py-1 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                        <span class="fw-bold text-uppercase">{{ ucfirst($order['status']) }}</span>
                        <i class="fa-solid fa-check-circle opacity-50"></i>
                    </div>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        @empty
          <div class="text-center text-muted">Belum ada data antrean.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>


<main class="container-fluid mt-3">
  <div class="row gx-4">
    <!-- GRID LAPANGAN -->
    <div class="col-lg-8">

      <div class="toolbar-card mb-3">
        <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3 justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <span class="fw-semibold text-muted small text-uppercase">Pilih Lapangan</span>
            <span class="badge bg-success bg-opacity-10 text-success">Realtime</span>
          </div>
          <div class="d-flex gap-2 flex-wrap">
              <input id="searchInput" class="form-control form-control-sm" placeholder="Cari Lapangan" style="border-radius:20px;max-width:200px;">
              <select id="filterStatus" class="form-select form-select-sm" style="width: auto;">
                  <option value="all">Semua Status</option>
                  <option value="available">Tersedia</option>
                  <option value="booked">Dipesan</option>
              </select>

              <select id="filterKategori" name="id_kategori" class="form-select form-select-sm" style="width: auto;">
                  <option value="all">Pilih Kategori</option>
                  @foreach ($kategori as $kat)
                      <option value="{{ $kat->id }}">
                          {{ $kat->nama_kategori }}
                      </option>
                  @endforeach
              </select>
          </div>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-3">
          <div class="chip" onclick="document.getElementById('filterStatus').value='available';document.getElementById('filterStatus').dispatchEvent(new Event('change'));">Tersedia</div>
          <div class="chip" onclick="document.getElementById('filterStatus').value='booked';document.getElementById('filterStatus').dispatchEvent(new Event('change'));">Sedang dipesan</div>
          <div class="chip" onclick="document.getElementById('filterStatus').value='all';document.getElementById('filterStatus').dispatchEvent(new Event('change'));">Reset filter</div>
        </div>
      </div>
      <div id="grid" class="row g-3"></div>
      <div class="d-flex justify-content-between align-items-center mt-3">
         <div id="gridPaginationSummary"></div>
         <ul id="gridPagination" class="pagination pagination-sm mb-0"></ul>
      </div>
    </div>

    <!-- KERANJANG -->
    <div class="col-lg-4">
      <div class="cart">
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
</main>

<!-- MODAL JADWAL -->
<div class="modal fade" id="jadwalModal" tabindex="-1" aria-labelledby="jadwalModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="jadwalModalLabel">Jadwal Tersedia</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-2 mb-3">
          <div class="col">
            <label for="filterTanggal" class="form-label fw-semibold mb-1">Tanggal</label>
            <input type="date" id="filterTanggal" class="form-control">
          </div>
          <div class="col">
            <label for="filterJamMulai" class="form-label fw-semibold mb-1">Jam Mulai</label>
            <input type="time" id="filterJamMulai" class="form-control">
          </div>
          <div class="col-auto d-flex align-items-end">
            <button class="btn btn-success w-100" id="resetFilters">
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
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-success" id="pesanBtn">Pesan</button>
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
    const statusFilter = document.getElementById("filterStatus").value;

    let items = lapanganData.map(l => ({ ...l, foto: l.foto || [], hargaRataRata: l.hargaRataRata, kategori_nama: l.kategori_nama || 'Tidak ada'}));
    
    if(filterKategori!=="all") items = items.filter(l=>l.id_kategori==filterKategori);
    if(statusFilter!=="all") items = items.filter(l=>l.status===statusFilter);
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
            <small>Harga Rata-rata:</small>
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
    pg.innerHTML="";
    const createPageItem = (num, active=false, disabled=false) => {
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

    pg.appendChild(createPageItem("«", false, page===1).querySelector('a')?.parentElement||createPageItem("«", false, page===1));
    for(let i=1;i<=totalPages;i++){
      pg.appendChild(createPageItem(i,i===page));
    }
    pg.appendChild(createPageItem("»", false, page===totalPages).querySelector('a')?.parentElement||createPageItem("»", false, page===totalPages));
  }

  // ======== CART ========
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
      cart.forEach(it => {
        const li = document.createElement("li");
        li.className = "list-group-item py-2";

        li.innerHTML = `
          <div class="fw-bold">${it.nama}</div>
          <div class="small text-muted">${it.jam_mulai} • ${it.tanggal}</div>
          <div class="fw-bold">Rp ${Number(it.harga).toLocaleString('id-ID')}</div>
        `;
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

  // ======== MODAL JADWAL ========
  function openJadwalModal(lapangan){
    const modalEl = document.getElementById('jadwalModal');
    const modal = new bootstrap.Modal(modalEl);
    const content = document.getElementById('jadwalContent');
    const filterTanggal = document.getElementById('filterTanggal');
    const filterJamMulai = document.getElementById('filterJamMulai');
    const resetBtn = document.getElementById('resetFilters');
    const summaryEl = document.getElementById('paginationSummary');
    const paginationEl = document.getElementById('jadwalPagination');
    const pesanBtn = document.getElementById('pesanBtn');

    document.getElementById('jadwalModalLabel').innerText = lapangan.nama;
    content.innerHTML = '<p class="text-center text-muted">Memuat jadwal...</p>';
    filterTanggal.value = ''; filterJamMulai.value = '';

    modal.show();

    let jadwalData=[];
    let currentPage=1;
    const rowsPerPage=6;

    fetch(`/petugas/api/jadwal/${lapangan.id}`)
      .then(res=>res.json())
      .then(data=>{ jadwalData=data; renderPage(1); })
      .catch(err=>{ content.innerHTML='<p class="text-center text-danger">Gagal memuat jadwal</p>'; console.error(err); });

    function getFilteredData(){
      return jadwalData.filter(j=>{
        let ok=true;
        if(filterTanggal.value) ok=ok && j.tanggal===filterTanggal.value;
        if(filterJamMulai.value) ok=ok && j.jam_mulai>=filterJamMulai.value;
        return ok;
      });
    }

    function renderPage(page){
      const filtered = getFilteredData();
      const totalPages = Math.ceil(filtered.length/rowsPerPage) || 1;
      currentPage = Math.min(Math.max(1,page),totalPages);
      const start = (currentPage-1)*rowsPerPage;
      const pageData = filtered.slice(start,start+rowsPerPage);

      content.innerHTML = '';
      if(pageData.length === 0){
        content.innerHTML = '<p class="text-center text-muted">Tidak ada jadwal tersedia</p>';
      } else {
        const grid = document.createElement('div');
        grid.className = 'row g-3';

        pageData.forEach(j => {
          const col = document.createElement('div');
          col.className = 'col-md-4';

          let statusClass="", statusText="";
          if(j.booking_status==="dibayar"){
            statusClass="bg-success text-white";
            statusText="Sudah Dibayar";
          } else if(j.booking_status==="menunggu"){
            statusClass="bg-warning text-dark";
            statusText="Sedang Dibooking";
          } else {
            statusClass="bg-light text-dark";
            const tanggalObj = new Date(j.tanggal);
            statusText = tanggalObj.toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' });
          }

          const card = document.createElement('div');
          card.className = `card p-2 text-center ${statusClass}`;
          card.style.cursor = (j.booking_status==="tersedia" || j.booking_status===undefined) ? 'pointer' : 'default';
          card.style.borderRadius = '8px';

          if(j.booking_status!=="dibayar" && j.booking_status!=="menunggu"){
            card.innerHTML = `
              <div>${j.jam_mulai} - ${j.jam_selesai}</div>
              <div class="mt-1 fw-bold">Rp ${Number(j.harga_sewa).toLocaleString('id-ID')}</div>
              <div class="form-check mt-1">
                <input class="form-check-input slot-checkbox" type="checkbox" value="">
                <label class="form-check-label small">${statusText}</label>
              </div>
            `;
          } else {
            card.innerHTML = `
              <div>${j.jam_mulai} - ${j.jam_selesai}</div>
              <div class="mt-1 fw-bold">Rp ${Number(j.harga_sewa).toLocaleString('id-ID')}</div>
              <div class="small">${statusText}</div>
            `;
          }

          col.appendChild(card);
          grid.appendChild(col);
        });

        content.appendChild(grid);
      }

      summaryEl.textContent = filtered.length===0 ? 'Jadwal tidak tersedia' :
        `Menampilkan ${start+1} - ${Math.min(start+rowsPerPage, filtered.length)} dari ${filtered.length} jadwal | Halaman ${currentPage} / ${totalPages}`;

      renderJadwalPagination(totalPages);
    }

    function renderJadwalPagination(totalPages){
      paginationEl.innerHTML='';
      const createPageItem=(num,active=false,disabled=false)=>{
        const li=document.createElement('li');
        li.className='page-item '+(active?'active':'')+(disabled?' disabled':'');
        li.innerHTML=`<a href="#" class="page-link">${num}</a>`;
        if(!disabled && !active){
          li.querySelector('a').addEventListener('click',e=>{
            e.preventDefault(); currentPage=num; renderPage(currentPage);
          });
        }
        return li;
      }

      paginationEl.appendChild(createPageItem('«',false,currentPage===1));
      for(let i=1;i<=totalPages;i++) paginationEl.appendChild(createPageItem(i,i===currentPage));
      paginationEl.appendChild(createPageItem('»',false,currentPage===totalPages));
    }

    filterTanggal.addEventListener('change',()=>renderPage(1));
    filterJamMulai.addEventListener('change',()=>renderPage(1));
    resetBtn.addEventListener('click',()=>{
      filterTanggal.value=''; filterJamMulai.value='';
      renderPage(1);
    });

    pesanBtn.addEventListener('click', ()=>{
      const grid = content.querySelector('.row');
      if(!grid) return;

      const checkedSlots = grid.querySelectorAll('.slot-checkbox:checked');
      checkedSlots.forEach(chk=>{
        const card = chk.closest('.card');
        const jam = card.querySelector('div').textContent.split(' - ')[0];
        const hargaText = card.querySelector('.fw-bold').textContent.replace(/Rp\s|[.]/g,'');
        const tanggal = card.querySelector('label').textContent;

        addToCart({
          id: lapangan.id,
          nama: lapangan.nama,
          harga: parseInt(hargaText),
          jam_mulai: jam,
          tanggal: tanggal,
          durasi: 1
        });
      });

      modal.hide();
      renderCart();
    });
  }

  // ======== EVENT FILTER & SEARCH ========
  document.getElementById("filterKategori").addEventListener("change", e => {
    filterKategori = e.target.value;
    page = 1;
    renderGrid();
  });
  
  document.getElementById("filterStatus").addEventListener("change", e => {
    page = 1;
    renderGrid();
  });

  document.getElementById("searchInput").addEventListener("input", () => {
    page = 1;
    renderGrid();
  });

  // ======== PEMBAYARAN ========
  const payBtn = document.getElementById("payBtn");
  const paymentModalEl = document.getElementById('paymentModal');
  const paymentModal = new bootstrap.Modal(paymentModalEl);
  const confirmPaymentBtn = document.getElementById('confirmPaymentBtn');

  payBtn.addEventListener('click', ()=>{
      if(cart.length===0){
          alert("Keranjang kosong!");
          return;
      }
      paymentModal.show();
  });

  confirmPaymentBtn.addEventListener('click', ()=>{
      const method = document.querySelector('input[name="paymentMethod"]:checked').value;
      if(cart.length===0){
          alert("Keranjang kosong!");
          return;
      }
      paymentModal.hide();

      const transaksiData = {
          kasir: "{{ $petugasName }}",
          metode: method,
          total: cart.reduce((s,i)=>s+i.harga*i.durasi,0),
          items: cart
      };fetch('{{ route("petugas.payment.store") }}', {
          
          method:'POST',
          headers:{
              'Content-Type':'application/json',
              'X-CSRF-TOKEN':'{{ csrf_token() }}'
          },
          body: JSON.stringify(transaksiData)
      })
      .then(res => res.json())
      .then(data => {
          if(data.success){
              alert(`Pembayaran berhasil! Total: Rp ${transaksiData.total.toLocaleString('id-ID')}`);
              cart = [];
              renderCart();
              if(typeof refreshJadwal === 'function') refreshJadwal();
          } else {
              alert('Gagal menyimpan transaksi!');
          }
      })
      .catch(err => {
          console.error(err);
          alert('Terjadi kesalahan server!');
      });
  });

  // ======== INIT ========
  renderGrid();
  renderCart();

});
</script>

@endpush
