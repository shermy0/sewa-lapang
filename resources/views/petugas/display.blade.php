<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Antrian Sewa Lapang</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root {
      --primary-bg: #eef2f7;
      --header-bg: #4e73df;
      --card-bg: #ffffff;
      --text-color: #333;
      --accent-color: #2f9f6f;
    }
    body {
      background-color: var(--primary-bg);
      font-family: 'Inter', sans-serif;
      overflow: hidden; /* Prevent scroll for full screen feel */
      height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .header {
      background: linear-gradient(90deg, #4e73df 0%, #224abe 100%);
      color: white;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      z-index: 10;
    }
    .header .brand {
      font-size: 1.5rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .header .clock {
      text-align: right;
    }
    .header .clock .time {
      font-size: 2rem;
      font-weight: 700;
      line-height: 1;
    }
    .header .clock .date {
      font-size: 0.9rem;
      opacity: 0.9;
    }

    .main-content {
      flex: 1;
      padding: 20px;
      display: flex;
      gap: 20px;
      height: calc(100vh - 120px); /* Adjust based on header/footer */
    }

    /* Left Panel: Active Queue */
    .active-queue-panel {
      flex: 0 0 35%;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    .active-card {
      background: var(--card-bg);
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      border: 1px solid #e3e6f0;
    }
    .active-card .card-header {
      background: #4e73df;
      color: white;
      padding: 15px;
      text-align: center;
      font-size: 1.2rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .active-card .card-body {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      background: #f8f9fc;
    }
    .active-queue-number {
      font-size: 6rem;
      font-weight: 800;
      color: #2c3e50;
      line-height: 1;
    }
    .active-queue-label {
      font-size: 1.5rem;
      color: #858796;
      margin-top: 10px;
    }
    .active-queue-section {
        background: #2f9f6f;
        color: white;
        width: 100%;
        text-align: center;
        padding: 15px;
        font-size: 1.5rem;
        font-weight: bold;
    }

    /* Right Panel: Video/Carousel */
    .media-panel {
      flex: 1;
      background: #000;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      position: relative;
    }
    .carousel, .carousel-inner, .carousel-item {
      height: 100%;
    }
    .carousel-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    /* Bottom Panel: Queue Grid */
    .queue-grid-container {
      height: 220px; /* Fixed height for bottom row */
      margin-top: auto;
    }
    .queue-grid {
      display: flex;
      gap: 15px;
      height: 100%;
      overflow-x: auto;
      padding-bottom: 10px;
    }
    .queue-item-card {
      flex: 0 0 200px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      border-top: 5px solid; /* Color set dynamically */
    }
    .queue-item-header {
      padding: 10px;
      text-align: center;
      font-weight: 600;
      font-size: 0.9rem;
      background: #f8f9fc;
      border-bottom: 1px solid #eee;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .queue-item-body {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 2.5rem;
      font-weight: 700;
      color: #333;
    }
    .queue-item-footer {
        font-size: 0.8rem;
        text-align: center;
        padding: 5px;
        color: #666;
    }

    /* Footer Marquee */
    .footer-marquee {
      background: #224abe;
      color: white;
      padding: 10px 0;
      font-size: 1rem;
      font-weight: 500;
      white-space: nowrap;
      overflow: hidden;
      position: fixed;
      bottom: 0;
      width: 100%;
    }
    .marquee-content {
      display: inline-block;
      padding-left: 100%;
      animation: marquee 20s linear infinite;
    }
    @keyframes marquee {
      0% { transform: translate(0, 0); }
      100% { transform: translate(-100%, 0); }
    }

    /* Colors for cards */
    .color-0 { border-color: #4e73df; }
    .color-0 .queue-item-header { background: rgba(78, 115, 223, 0.1); color: #4e73df; }
    .color-1 { border-color: #1cc88a; }
    .color-1 .queue-item-header { background: rgba(28, 200, 138, 0.1); color: #1cc88a; }
    .color-2 { border-color: #36b9cc; }
    .color-2 .queue-item-header { background: rgba(54, 185, 204, 0.1); color: #36b9cc; }
    .color-3 { border-color: #f6c23e; }
    .color-3 .queue-item-header { background: rgba(246, 194, 62, 0.1); color: #f6c23e; }
    .color-4 { border-color: #e74a3b; }
    .color-4 .queue-item-header { background: rgba(231, 74, 59, 0.1); color: #e74a3b; }
    .color-5 { border-color: #858796; }

  </style>
</head>
<body>

  <!-- Header -->
  <header class="header">
    <div class="brand">
      <i class="fa-solid fa-layer-group"></i>
      <div>
        <div>SEWALAP VIRTUAL OFFICE</div>
        <div style="font-size: 0.8rem; font-weight: 400;">Sistem Antrian Sewa Lapangan</div>
      </div>
    </div>
    <div class="clock">
      <div class="time" id="clockTime">00:00:00</div>
      <div class="date" id="clockDate">Senin, 1 Januari 2024</div>
    </div>
  </header>

  <!-- Main Layout -->
  <div class="main-content">

    <!-- Left: Active Queue (Biggest/Latest) -->
    <div class="active-queue-panel">
      <!-- We can show the very first queue item here as the "Active" one being called -->
      @php
        // Find the first section with a queue
        $activeQueue = null;
        $activeSectionName = 'Menunggu...';
        foreach($sectionQueues as $section) {
            if($section['queue']->isNotEmpty()) {
                $activeQueue = $section['queue']->first();
                $activeSectionName = $section['label'];
                break;
            }
        }
      @endphp

      <div class="active-card">
        <div class="card-header">NOMOR ANTRIAN</div>
        <div class="card-body">
            @if($activeQueue)
                <div class="active-queue-number">{{ $activeQueue['kode_tiket'] }}</div>
                <div class="active-queue-label">{{ $activeQueue['penyewa'] }}</div>

                <!-- Countdown Timer -->
                <div class="mt-4 text-center">
                    <div class="small text-muted text-uppercase fw-bold mb-1">Sisa Waktu Main</div>
                    <div id="countdownTimer" class="display-4 fw-bold text-danger"
                         data-end="{{ $activeQueue['jam_selesai'] }}"
                         data-date="{{ \Carbon\Carbon::parse($activeQueue['tanggal'])->format('Y-m-d') }}">
                        --:--
                    </div>
                </div>
            @else
                <div class="active-queue-number">-</div>
                <div class="active-queue-label">Belum ada antrian</div>
            @endif
        </div>
        <div class="active-queue-section">{{ $activeSectionName }}</div>
      </div>
    </div>

    <!-- Right: Carousel & Grid -->
    <div style="flex: 1; display: flex; flex-direction: column; gap: 20px;">

        <!-- Top Right: Carousel -->
        <div class="media-panel">
            <div id="carouselExampleSlidesOnly" class="carousel slide carousel-fade" data-bs-ride="carousel">
                <div class="carousel-inner">
                    @forelse($carouselImages as $index => $img)
                        <div class="carousel-item {{ $index == 0 ? 'active' : '' }}" data-bs-interval="5000">
                            <img src="{{ asset('storage/' . $img) }}" class="d-block w-100" alt="...">
                        </div>
                    @empty
                        <div class="carousel-item active">
                            <div class="d-flex justify-content-center align-items-center h-100 bg-secondary text-white">
                                <h3>Selamat Datang di Sewalap</h3>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Bottom Right: Queue Grid -->
        <div class="queue-grid-container">
            <div class="queue-grid">
                @foreach($sectionQueues as $index => $section)
                    @php
                        $currentQueue = $section['queue']->first();
                    @endphp
                    <div class="queue-item-card color-{{ $index % 5 }}">
                        <div class="queue-item-header">{{ $section['label'] }}</div>
                        <div class="queue-item-body">
                            {{ $currentQueue ? $currentQueue['kode_tiket'] : '-' }}
                        </div>
                        <div class="queue-item-footer">
                             {{ $currentQueue ? $currentQueue['penyewa'] : 'Kosong' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
  </div>

  <!-- Footer Marquee -->
  <div class="footer-marquee">
    <div class="marquee-content">
      Selamat Datang di SEWALAP. Silakan menunggu nomor antrian Anda dipanggil. Jagalah kebersihan dan ketertiban area lapangan. Terima kasih.
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script>
    function updateClock() {
      const now = new Date();
      const timeString = now.toLocaleTimeString('id-ID', { hour12: false });
      const dateString = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

      document.getElementById('clockTime').textContent = timeString;
      document.getElementById('clockDate').textContent = dateString;
    }

    setInterval(updateClock, 1000);
    updateClock();

    // Generate QR Code if active queue exists
    @if($activeQueue)
        const qrContainer = document.getElementById("qrcode");
        if(qrContainer) {
            new QRCode(qrContainer, {
                text: "{{ $activeQueue['kode_tiket'] }}",
                width: 120,
                height: 120,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        }
    @endif

    // Countdown Logic
    function updateCountdown() {
        const timerEl = document.getElementById('countdownTimer');
        if (!timerEl) return;

        const endTimeStr = timerEl.dataset.end; // "HH:MM"
        const dateStr = timerEl.dataset.date;   // "YYYY-MM-DD"

        if (!endTimeStr || !dateStr) return;

        const now = new Date();
        const endTime = new Date(`${dateStr}T${endTimeStr}:00`);

        // If end time is tomorrow (e.g. playing until 01:00), handle date crossing?
        // For simplicity assuming same day or handled by backend date.

        const diff = endTime - now;

        if (diff <= 0) {
            timerEl.textContent = "Selesai";
            timerEl.classList.remove('text-danger');
            timerEl.classList.add('text-muted');
            return;
        }

        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        const hDisplay = hours > 0 ? hours + ":" : "";
        const mDisplay = minutes.toString().padStart(2, '0');
        const sDisplay = seconds.toString().padStart(2, '0');

        timerEl.textContent = `${hDisplay}${mDisplay}:${sDisplay}`;
    }

    setInterval(updateCountdown, 1000);
    updateCountdown();

    // Auto reload page every 30 seconds to fetch new data
    setTimeout(function(){
       window.location.reload();
    }, 30000);
  </script>
</body>
</html>
