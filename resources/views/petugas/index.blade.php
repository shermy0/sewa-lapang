<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Petugas POS - Kasir Lapangan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root{--accent:#41A67E;--accent-dark:#41A67E}
    body{background:#f5f7fb;font-family:Inter,system-ui,-apple-system,'Segoe UI',Roboto,'Helvetica Neue',Arial}
    .topbar{background: #41A67E ;color:#fff;padding:14px 18px}
    .brand{font-weight:700;letter-spacing:.4px}
    .searchbar{max-width:560px}
    .lapangan-card{cursor:pointer;transition:.08s}
    .lapangan-card:hover{transform:translateY(-3px);box-shadow:0 6px 20px rgba(0,0,0,.08)}
    .lapangan-img{height:130px;object-fit:cover;border-radius:6px 6px 0 0}
    .badge-available{background:#28a745;color:#fff}
    .badge-booked{background:#ffc107;color:#212529}
    .cart{position:sticky;top:20px;height:calc(100vh - 40px);overflow:auto}
    .order-item{display:flex;align-items:center;gap:10px}
    .qty-btn{width:28px;height:28px;text-align:center;line-height:28px;border-radius:6px;background:#efefef;cursor:pointer}
    .btn-pay{background:var(--accent);color:#fff;border:none}
    @media(max-width:991px){.cart{position:relative;height:auto;margin-top:18px}}
  </style>
</head>

<body>

<header class="topbar d-flex align-items-center justify-content-between">
  <div class="d-flex align-items-center gap-3">
    <div class="brand">SEWA-LAPANG • Petugas Kasir</div>
    <div class="ms-3 searchbar d-none d-md-block">
      <input id="searchInput" class="form-control form-control-sm" placeholder="Cari Lapangan / Nama...">
    </div>
  </div>

  <div class="d-flex align-items-center gap-3">
    <a href="{{ route('petugas.scan') }}" class="btn btn-light btn-sm fw-semibold">
      Scan QR
    </a>
    <div class="text-end me-2 d-none d-md-block">
      <small>Petugas: <strong>{{ $petugasName }}</strong></small>
    </div>
    <div class="rounded-circle bg-white text-dark d-flex align-items-center justify-content-center"
         style="width:36px;height:36px">
      {{ substr($petugasName, 0, 1) }}
    </div>
  </div>
</header>


<main class="container-fluid mt-3">
  <div class="row gx-4">

    <!-- LEFT GRID -->
    <div class="col-lg-8">

      <div class="d-flex justify-content-between mb-3">
        <div>
          <h5 class="mb-0">Pilih Lapangan</h5>
        </div>
        <div class="d-flex gap-2">
            <select id="filterStatus" class="form-select form-select-sm">
                <option value="all">Semua Status</option>
                <option value="available">Tersedia</option>
                <option value="booked">Dipesan</option>
            </select>

            <select name="id_kategori" class="form-select form-select-lg" required>
                <option value="" disabled selected>Pilih Kategori</option>

                @foreach ($kategori as $kat)
                    <option value="{{ $kat->id }}">
                        {{ $kat->nama_kategori }}
                    </option>
                @endforeach
            </select>
        </div>
      </div>

      <div id="grid" class="row g-3"></div>

      <div class="mt-4 d-flex justify-content-center">
        <nav><ul id="pagination" class="pagination pagination-sm"></ul></nav>
      </div>

    </div>

    <!-- CART -->
    <div class="col-lg-4">
      <div class="card cart p-3">

        <div class="d-flex justify-content-between mb-2">
          <h6 class="mb-0">Daftar Pesanan</h6>
          <small id="cartCount">0 item</small>
        </div>

        <ul id="orderList" class="list-group list-group-flush mb-2"></ul>

        <div>
          <div class="d-flex justify-content-between">
            <div>Subtotal</div> <div id="subtotal">Rp 0</div>
          </div>
          <div class="d-flex justify-content-between">
            <div>Pajak (0%)</div> <div id="tax">Rp 0</div>
          </div>

          <hr>

          <div class="d-flex justify-content-between align-items-center">
            <div>
              <small class="text-muted">Total</small>
              <div class="fs-5 fw-bold" id="totalPrice">Rp 0</div>
            </div>

            <div style="min-width:160px">
              <button id="saveBtn" class="btn btn-outline-secondary w-100 mb-2">Simpan</button>
              <button id="payBtn" class="btn btn-pay w-100">Bayar</button>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</main>

<!-- DATA FROM LARAVEL -->
<script>
    const lapanganData = @json($lapangan);
    const petugasName = "{{ $petugasName }}";
    const currentOwnerId = {{ $pemilikId }};
</script>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

  let cart = [];
  let perPage = 9;
  let page = 1;
  let filterStatus = "all";
  let filterOwner = currentOwnerId;

  document.addEventListener("DOMContentLoaded", () => {
    renderGrid();
    renderCart();
  });

  document.getElementById("filterStatus").addEventListener("change", e => {
    filterStatus = e.target.value;
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

    let items = lapanganData.filter(l => l.pemilik_id == filterOwner);

    if(filterStatus !== "all")
      items = items.filter(l => l.status === filterStatus);

    if(q)
      items = items.filter(l => l.nama.toLowerCase().includes(q));

    const totalPages = Math.ceil(items.length / perPage) || 1;
    page = Math.min(page, totalPages);

    const start = (page - 1) * perPage;
    const pageItems = items.slice(start, start + perPage);

    pageItems.forEach(l => {
      const col = document.createElement("div");
      col.className = "col-md-4";
      col.innerHTML = `
        <div class="card lapangan-card" data-id="${l.id}">
          <img class="lapangan-img" 
               src="${l.foto ?? 'https://via.placeholder.com/600x400?text=Lapangan'}">
          <div class="card-body">
            <h6 class="card-title mb-1">${l.nama}</h6>
            <div class="d-flex justify-content-between">
              <div class="text-muted">Rp ${formatNum(l.harga)}</div>
              <span class="badge ${l.status === 'available' ? 'badge-available' : 'badge-booked'}">
                ${l.status === 'available' ? 'Tersedia' : 'Dipesan'}
              </span>
            </div>
          </div>
        </div>
      `;

      col.querySelector(".lapangan-card").addEventListener("click", () => {
        if(l.status !== "available")
          return alert("Lapangan sedang dipesan.");
        openOrderModal(l);
      });

      grid.appendChild(col);
    });

    renderPagination(totalPages);
  }

  function renderPagination(totalPages){
    const pg = document.getElementById("pagination");
    pg.innerHTML = "";

    for(let i=1; i<=totalPages; i++){
      const li = document.createElement("li");
      li.className = "page-item " + (i === page ? "active" : "");
      li.innerHTML = `<a href="#" class="page-link">${i}</a>`;
      li.addEventListener("click", e => {
        e.preventDefault();
        page = i;
        renderGrid();
      });
      pg.appendChild(li);
    }
  }

  function openOrderModal(l){
    const jam = prompt("Jam mulai (HH:MM)", "10:00");
    if(!jam) return;

    const dur = parseInt(prompt("Durasi (jam)", "1")) || 1;

    addToCart({
      id: l.id,
      nama: l.nama,
      harga: l.harga,
      jam_mulai: jam,
      durasi: dur
    });
  }

  function addToCart(item){
    const exist = cart.find(c => c.id===item.id && c.jam_mulai===item.jam_mulai);
    if(exist) exist.durasi += item.durasi;
    else cart.push({...item});
    renderCart();
  }

  function renderCart(){
    const list = document.getElementById("orderList");
    list.innerHTML = "";

    if(cart.length === 0){
      list.innerHTML = '<li class="list-group-item text-center text-muted">Belum ada pesanan</li>';
    } else {
      cart.forEach((it, idx) => {
        const li = document.createElement("li");
        li.className = "list-group-item";

        li.innerHTML = `
          <div class="order-item">
            <div style="flex:1">
              <div class="fw-bold">${it.nama}</div>
              <small class="text-muted">${it.jam_mulai} • ${it.durasi} jam</small>
            </div>

            <div class="text-end">
              <div>Rp ${formatNum(it.harga * it.durasi)}</div>
              <div class="d-flex justify-content-end mt-1">
                <div class="qty-btn" onclick="changeQty(${idx}, -1)">-</div>
                <div class="px-2">${it.durasi}</div>
                <div class="qty-btn" onclick="changeQty(${idx}, 1)">+</div>
                <div class="ms-2 text-danger" style="cursor:pointer" onclick="removeItem(${idx})">✕</div>
              </div>
            </div>
          </div>
        `;

        list.appendChild(li);
      });
    }

    document.getElementById("cartCount").innerText = cart.length + " item";
    updateTotals();
  }

  function changeQty(i,d){
    cart[i].durasi = Math.max(1, cart[i].durasi + d);
    renderCart();
  }

  function removeItem(i){
    cart.splice(i,1);
    renderCart();
  }

  function updateTotals(){
    const subtotal = cart.reduce((s,i)=>s+(i.harga*i.durasi),0);
    const total = subtotal;

    document.getElementById("subtotal").innerText = "Rp " + formatNum(subtotal);
    document.getElementById("totalPrice").innerText = "Rp " + formatNum(total);
  }

  function formatNum(n){ return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g,"."); }

</script>


</body>
</html>
