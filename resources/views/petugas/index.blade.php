@extends('layouts.master')

@section('title', 'Petugas Kasir')

@section('content')
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>SEWALAP - Kasir</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    :root {
      --accent: #41A67E;
      --bg: #f5f7fb;
      --card-hover: rgba(0, 0, 0, 0.08);
    }

    body {
      background: var(--bg);
      font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
      margin: 0;
      padding: 0;
    }

    .topbar {
      background: var(--accent);
      color: #fff;
      padding: 12px 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .brand {
      font-weight: 700;
      font-size: 1.2rem;
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
      box-shadow: 0 8px 20px var(--card-hover);
    }

    /* GAMBAR */
    .lapangan-img,
    .card .carousel-inner,
    .card .carousel-item {
      width: 100%;
      height: 220px; /* tinggi konsisten */
    }

    .lapangan-img {
      object-fit: cover;
    }

    /* Carousel image */
    .card .carousel-inner img {
      width: 100%;
      height: 220px; /* sama dengan lapangan-img */
      object-fit: cover;
    }

    .cart {
      position: sticky;
      top: 20px;
      max-height: calc(100vh - 40px);
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
      padding: 15px;
      overflow-y: auto;
    }

    .cart h6 {
      font-weight: 600;
    }

    .btn-pay {
      background: var(--accent);
      color: #fff;
      font-weight: 600;
    }

    input#searchInput {
      border-radius: 20px;
      max-width: 200px;
    }

    select#filterKategori {
      max-width: 180px;
    }

    @media (max-width: 991px) {
      .cart {
        position: relative;
        height: auto;
        max-height: none;
        margin-top: 15px;
      }

      .lapangan-img,
      .card .carousel-inner,
      .card .carousel-item,
      .card .carousel-inner img {
        height: 180px; /* lebih kecil di mobile */
      }
    }

    .card .text-truncate {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
  </style>
</head>
<body>

<main class="container-fluid mt-3">
  <div class="row gx-4">
    <!-- GRID LAPANGAN -->
    <div class="col-lg-8">
      <div class="d-flex justify-content-between mb-3">
        <input id="searchInput" class="form-control form-control-sm d-none d-md-block"
           placeholder="Cari Lapangan" style="border-radius:20px;max-width:200px;">
        <select id="filterKategori" name="id_kategori" class="form-select form-select-sm" style="max-width:150px;" required>
            <option value="all" selected>Pilih Kategori</option>
            @foreach ($kategori as $kat)
                <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
            @endforeach
        </select>
      </div>
      <div id="grid" class="row g-3"></div>
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
      };fetch('{{ route("petugas.store") }}', {
          
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
@endsection