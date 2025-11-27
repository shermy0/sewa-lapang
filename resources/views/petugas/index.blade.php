<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>SEWALAP - Kasir</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    :root{
      --accent:#41A67E;
      --bg:#f5f7fb;
      --card-hover:rgba(0,0,0,.08);
    }
    body{
      background: var(--bg);
      font-family: Inter,system-ui,-apple-system,'Segoe UI',Roboto,'Helvetica Neue',Arial;
    }
    .topbar{
      background: var(--accent);
      color:#fff;
      padding:12px 20px;
      box-shadow:0 2px 5px rgba(0,0,0,.05);
    }
    .brand{font-weight:700;font-size:1.2rem;}
    .lapangan-card{
      cursor:pointer;
      transition:.12s;
      border-radius:6px;
      box-shadow:0 2px 6px rgba(0,0,0,.05);
      overflow:hidden;
    }
    .lapangan-card:hover{
      transform:translateY(-3px);
      box-shadow:0 6px 15px var(--card-hover);
    }
    .lapangan-img{height:120px;object-fit:cover;width:100%}
    .cart{
      position:sticky;top:20px;height:calc(100vh - 40px);
      background:#fff;border-radius:10px;
      box-shadow:0 2px 5px rgba(0,0,0,.08);
      padding:15px;overflow:auto
    }
    .qty-btn{
      width:28px;height:28px;background:#eee;border-radius:6px;
      text-align:center;line-height:28px;cursor:pointer;
    }
    .btn-pay{background:var(--accent);color:#fff}
    @media(max-width:991px){
      .cart{position:relative;height:auto;margin-top:15px}
    }
  </style>
</head>
<body>

<header class="topbar d-flex align-items-center justify-content-between">
  <div class="d-flex align-items-center gap-3">
    <div class="brand">SEWALAP</div>
    <input id="searchInput" class="form-control form-control-sm d-none d-md-block"
           placeholder="Cari Lapangan" style="border-radius:20px;max-width:200px;">
  </div>

  <div class="d-flex align-items-center gap-3">
    <a href="{{ route('petugas.scan') }}" class="btn btn-light btn-sm fw-semibold">
      <i class="fa-solid fa-qrcode me-1"></i> Scan QR
    </a>
    <div class="text-end d-none d-md-block">
      <small>Petugas: <strong>{{ $petugasName }}</strong></small>
    </div>
    <div class="rounded-circle bg-white text-dark d-flex align-items-center justify-content-center"
         style="width:36px;height:36px;font-weight:600">
      {{ substr($petugasName, 0, 1) }}
    </div>
    <form action="{{ route('logout') }}" method="POST">
      @csrf
      <button type="submit" class="btn btn-light btn-sm">
        <i class="fa-solid fa-right-from-bracket"></i>
      </button>
    </form>
  </div>
</header>

<main class="container-fluid mt-3">
  <div class="row gx-4">

    <!-- GRID LAPANGAN -->
    <div class="col-lg-8">
      <div class="d-flex justify-content-between mb-3">
        <h5 class="mb-0">Pilih Lapangan</h5>
        <select id="filterKategori" name="id_kategori" class="form-select form-select-sm" style="max-width:150px;" required>
            <option value="all" selected>Pilih Kategori</option>
            @foreach ($kategori as $kat)
                <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
            @endforeach
        </select>
      </div>
      <div id="grid" class="row g-3"></div>
      <div class="d-flex justify-content-center mt-3">
        <ul id="pagination" class="pagination pagination-sm"></ul>
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
          <button id="saveBtn" class="btn btn-outline-secondary w-100 mt-3">Simpan</button>
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
      <div class="modal-body" id="jadwalContent">
        <p class="text-center text-muted">Memuat jadwal...</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
const lapanganData = @json($lapangan);
const petugasName  = "{{ $petugasName }}";

let cart = [];
let perPage = 9;
let page = 1;
let filterKategori = "all";

document.addEventListener("DOMContentLoaded", () => {
  renderGrid();
  renderCart();
});

document.getElementById("filterKategori").addEventListener("change", e => {
  filterKategori = e.target.value;
  page = 1;
  renderGrid();
});

document.getElementById("searchInput").addEventListener("input", () => {
  page = 1;
  renderGrid();
});

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

    let fotoHTML = "";
    if (l.foto.length > 1) {
      fotoHTML = `
        <div id="carousel${l.id}" class="carousel slide">
          <div class="carousel-inner">
            ${l.foto.map((f, idx) => `
              <div class="carousel-item ${idx===0?'active':''}">
                <img src="/storage/${f}" class="lapangan-img">
              </div>`).join('')}
          </div>
        </div>`;
    } else if(l.foto.length===1){
      fotoHTML = `<img src="/storage/${l.foto[0]}" class="lapangan-img">`;
    } else {
      fotoHTML = `<img src="/no-image.jpg" class="lapangan-img">`;
    }

    col.innerHTML = `
    <div class="card lapangan-card" data-id="${l.id}">
      ${fotoHTML}
      <div class="card-body py-2 px-2">
        <h6 class="card-title mb-1">${l.nama}</h6>
        ${l.deskripsi ? `<p class="text-truncate mb-1" style="font-size:0.85rem;">${l.deskripsi}</p>` : ''}
        <div class="mt-1 d-flex justify-content-between">
          <small>Harga Rata-rata:</small>
          <small style="color:#41A67E;font-weight:600;">
            ${l.hargaRataRata ? `Rp. ${l.hargaRataRata.toLocaleString('id-ID')} /jam` : '-'}
          </small>
        </div>
      </div>
    </div>`;

    document.querySelectorAll(".lapangan-card").forEach(card=>{
    card.addEventListener("click", async ()=>{
      const lapanganId = card.dataset.id;
      const modal = new bootstrap.Modal(document.getElementById('jadwalModal'));
      const content = document.getElementById('jadwalContent');
      document.getElementById('jadwalModalLabel').innerText = card.querySelector(".card-title").innerText;
      content.innerHTML = '<p class="text-center text-muted">Memuat jadwal...</p>';
      modal.show();

      try {
        const res = await fetch(`/petugas/api/jadwal/${lapanganId}`);
        const data = await res.json();
        if(!data.length){
          content.innerHTML = '<p class="text-center text-muted">Tidak ada jadwal tersedia</p>';
          return;
        }

        // GRID JADWAL
        const grid = document.createElement("div");
        grid.className = "row g-3";

        data.forEach(j => {
          const col = document.createElement("div");
          col.className = "col-md-4";

          // STATUS WARNA
          let statusClass="", statusText="";
          if(j.booking_status==="dibayar"){
            statusClass="bg-success text-white";
            statusText="Sudah Dibayar";
          } else if(j.booking_status==="menunggu"){
            statusClass="bg-warning text-dark";
            statusText="Sedang Dibooking";
          } else {
            statusClass="bg-light text-dark";
          }

          col.innerHTML = `
            <div class="card p-2 text-center ${statusClass}" style="cursor:pointer;border-radius:8px;">
              <div><strong>${j.tanggal}</strong></div>
              <div>${j.jam_mulai} - ${j.jam_selesai}</div>
              <div class="mt-1 fw-bold">Rp ${j.harga_sewa.toLocaleString('id-ID')}</div>
              <div class="small">${statusText}</div>
            </div>
          `;
          grid.appendChild(col);
        });

        content.innerHTML = "";
        content.appendChild(grid);

      } catch(e){
        content.innerHTML = '<p class="text-center text-danger">Gagal memuat jadwal</p>';
        console.error(e);
      }
    });
  });

    grid.appendChild(col);
  });

  renderPagination(totalPages);
}

function renderPagination(totalPages){
  const pg = document.getElementById("pagination");
  pg.innerHTML="";
  for(let i=1;i<=totalPages;i++){
    const li=document.createElement("li");
    li.className="page-item "+(i===page?"active":"");
    li.innerHTML=`<a href="#" class="page-link">${i}</a>`;
    li.addEventListener("click",e=>{
      e.preventDefault();
      page=i;
      renderGrid();
    });
    pg.appendChild(li);
  }
}

function addToCart(item){
  const exist = cart.find(c => c.id===item.id && c.jam_mulai===item.jam_mulai);
  if(exist) exist.durasi+=item.durasi;
  else cart.push({...item});
  renderCart();
}

function changeQty(i,d){ cart[i].durasi = Math.max(1, cart[i].durasi+d); renderCart(); }
function removeItem(i){ cart.splice(i,1); renderCart(); }

function renderCart(){
  const list=document.getElementById("orderList");
  list.innerHTML="";
  if(cart.length===0){
    list.innerHTML='<li class="list-group-item text-center text-muted">Belum ada pesanan</li>';
  } else {
    cart.forEach((it,idx)=>{
      const li=document.createElement("li");
      li.className="list-group-item py-2";
      li.innerHTML=`
        <div class="d-flex justify-content-between">
          <div>
            <div class="fw-bold">${it.nama}</div>
            <small class="text-muted">${it.jam_mulai} • ${it.durasi} jam</small>
          </div>
          <div class="text-end">
            <div>Rp ${formatNum(it.harga*it.durasi)}</div>
            <div class="d-flex justify-content-end mt-1">
              <div class="qty-btn" onclick="changeQty(${idx},-1)">-</div>
              <div class="px-2">${it.durasi}</div>
              <div class="qty-btn" onclick="changeQty(${idx},1)">+</div>
              <div class="ms-2 text-danger" style="cursor:pointer" onclick="removeItem(${idx})">✕</div>
            </div>
          </div>
        </div>`;
      list.appendChild(li);
    });
  }
  document.getElementById("cartCount").innerText = cart.length + " item";
  updateTotals();
}

function updateTotals(){
  const subtotal = cart.reduce((s,i)=>s+i.harga*i.durasi,0);
  document.getElementById("subtotal").innerText="Rp "+formatNum(subtotal);
  document.getElementById("totalPrice").innerText="Rp "+formatNum(subtotal);
}

function formatNum(n){ return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g,"."); }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
