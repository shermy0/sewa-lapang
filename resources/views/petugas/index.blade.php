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
    .brand{font-weight:700;letter-spacing:.4px;font-size:1.2rem;}
    .searchbar input{border-radius:20px}
    .lapangan-card{
      cursor:pointer;
      transition:.12s;
      border-radius:6px;
      overflow:hidden;
      box-shadow:0 2px 6px rgba(0,0,0,.04);
      display:flex;
      flex-direction:column;
    }
    .lapangan-card:hover{
      transform:translateY(-3px);
      box-shadow:0 6px 18px var(--card-hover);
    }
    .lapangan-img{
      height:100px;
      object-fit:cover;
      border-radius:6px 6px 0 0;
      width:100%;
    }
    .badge-available{background:#28a745;color:#fff;font-size:.75rem;padding:.2rem .4rem;}
    .badge-booked{background:#ffc107;color:#212529;font-size:.75rem;padding:.2rem .4rem;}
    .cart{
      position:sticky;
      top:20px;
      height:calc(100vh - 40px);
      overflow:auto;
      border-radius:10px;
      background:#fff;
      box-shadow:0 2px 6px rgba(0,0,0,.08);
    }
    .order-item{
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding:.4rem 0;
      border-bottom:1px solid #eee;
    }
    .qty-btn{
      width:28px;height:28px;text-align:center;line-height:28px;border-radius:6px;background:#efefef;cursor:pointer;user-select:none;
      transition:.1s;
    }
    .qty-btn:hover{background:#ddd;}
    .btn-pay{background:var(--accent);color:#fff;border:none;border-radius:6px}
    .page-item.active .page-link{
      background:var(--accent);
      border-color:var(--accent);
      color:#fff;
    }
    @media(max-width:991px){
      .cart{position:relative;height:auto;margin-top:18px}
    }
  </style>
</head>

<body>

<header class="topbar d-flex align-items-center justify-content-between">
  <div class="d-flex align-items-center gap-3">
    <div class="brand">SEWALAP</div>
    <div class="ms-3 searchbar d-none d-md-block">
      <input id="searchInput" class="form-control form-control-sm" placeholder="Cari Lapangan">
    </div>
  </div>

  <div class="d-flex align-items-center gap-3">
    <a href="{{ route('petugas.scan') }}" class="btn btn-light btn-sm fw-semibold">
      <i class="fa-solid fa-qrcode me-1"></i> Scan QR
    </a>
    <div class="text-end me-2 d-none d-md-block">
      <small>Petugas: <strong>{{ $petugasName }}</strong></small>
    </div>
    <div class="rounded-circle bg-white text-dark d-flex align-items-center justify-content-center"
         style="width:36px;height:36px;font-weight:600">
      {{ substr($petugasName, 0, 1) }}
    </div>
    <form action="{{ route('logout') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-light btn-sm">
            <i class="fa-solid fa-right-from-bracket"></i>
        </button>
    </form>
  </div>
</header>

<main class="container-fluid mt-3">
  <div class="row gx-4">
    <div class="col-lg-8">
      <div class="d-flex justify-content-between mb-3 flex-wrap gap-2 align-items-center">
        <h1 class="mb-0 fs-5">Pilih Lapangan</h1>
        <select id="filterKategori" class="form-select form-select-sm" style="max-width:180px;">
          <option value="all">Semua Kategori</option>
          @foreach ($kategori as $kat)
              <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
          @endforeach
        </select>
      </div>

      <div id="grid" class="row g-3"></div>

      <div class="mt-3 d-flex justify-content-center">
        <nav><ul id="pagination" class="pagination pagination-sm"></ul></nav>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card cart p-3">
        <div class="d-flex justify-content-between mb-2">
          <h6 class="mb-0">Daftar Pesanan</h6>
          <small id="cartCount">0 item</small>
        </div>

        <ul id="orderList" class="list-group list-group-flush mb-2"></ul>

        <div>
          <div class="d-flex justify-content-between"><div>Subtotal</div> <div id="subtotal">Rp 0</div></div>
          <hr>
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <small class="text-muted">Total</small>
              <div class="fs-5 fw-bold" id="totalPrice">Rp 0</div>
            </div>
            <div style="min-width:140px">
              <button id="saveBtn" class="btn btn-outline-secondary w-100 mb-2">Simpan</button>
              <button id="payBtn" class="btn btn-pay w-100">Bayar</button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</main>

<script>
const lapanganData = @json($lapangan);
const petugasName = "{{ $petugasName }}";

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
    hargaRataRata: l.hargaRataRata || 0,
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
    col.innerHTML = `
      <div class="card lapangan-card" data-id="${l.id}">
        <img class="lapangan-img" src="${l.foto ?? 'https://via.placeholder.com/600x400?text=Lapangan'}">
        <div class="card-body py-2 px-2">
          <h6 class="card-title mb-1">${l.nama}</h6>
          <small class="text-muted">${l.kategori_nama}</small>
          <div class="mt-1 d-flex justify-content-between align-items-center">
            <small class="text-muted"><i class="fa-solid fa-money-bill-wave text-success me-1"></i> Harga Rata-rata</small>
            <span class="fw-bold text-success">Rp ${formatNum(l.hargaRataRata)} / jam</span>
          </div>    
        </div>
      </div>
    `;
    col.querySelector(".lapangan-card").addEventListener("click", () => {
      addToCart({
        id: l.id,
        nama: l.nama,
        harga: l.hargaRataRata || 0,
        jam_mulai: new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}),
        durasi: 1
      });
    });
    grid.appendChild(col);
  });

  renderPagination(totalPages);
}

function renderPagination(totalPages){
  const pg = document.getElementById("pagination");
  pg.innerHTML = "";
  for(let i=1;i<=totalPages;i++){
    const li=document.createElement("li");
    li.className="page-item "+(i===page?"active":"");
    li.innerHTML=`<a href="#" class="page-link">${i}</a>`;
    li.addEventListener("click", e=>{e.preventDefault(); page=i; renderGrid();});
    pg.appendChild(li);
  }
}

function addToCart(item){
  const exist = cart.find(c=>c.id===item.id&&c.jam_mulai===item.jam_mulai);
  if(exist) exist.durasi+=item.durasi;
  else cart.push({...item});
  renderCart();
}

function renderCart(){
  const list=document.getElementById("orderList");
  list.innerHTML="";
  if(cart.length===0) list.innerHTML='<li class="list-group-item text-center text-muted">Belum ada pesanan</li>';
  else cart.forEach((it,idx)=>{
    const li=document.createElement("li");
    li.className="list-group-item py-2";
    li.innerHTML=`
      <div class="order-item">
        <div style="flex:1">
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
      </div>
    `;
    list.appendChild(li);
  });
  document.getElementById("cartCount").innerText=cart.length+" item";
  updateTotals();
}

function changeQty(i,d){cart[i].durasi=Math.max(1,cart[i].durasi+d); renderCart();}
function removeItem(i){cart.splice(i,1); renderCart();}
function updateTotals(){
  const subtotal=cart.reduce((s,i)=>s+i.harga*i.durasi,0);
  document.getElementById("subtotal").innerText="Rp "+formatNum(subtotal);
  document.getElementById("totalPrice").innerText="Rp "+formatNum(subtotal);
}
function formatNum(n){return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g,".");}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
