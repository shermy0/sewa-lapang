<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Antrian Sewa Lapang</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

    :root {
      --primary-bg: linear-gradient(135deg, #10b981 0%, #059669 100%);
      --header-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
      --card-bg: #ffffff;
      --text-color: #2d3748;
      --accent-color: #10b981;
      --accent-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
      --success-color: #10b981;
      --warning-color: #f6ad55;
      --danger-color: #fc8181;
    }

    body {
      background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 50%, #6ee7b7 100%);
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      overflow: hidden;
      height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .header {
      background: var(--header-gradient);
      color: white;
      padding: 18px 35px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 8px 32px rgba(16, 185, 129, 0.3);
      z-index: 10;
      backdrop-filter: blur(10px);
      border-bottom: 3px solid rgba(255, 255, 255, 0.2);
      position: relative;
    }

    .header .brand {
      font-size: 1.6rem;
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: 12px;
      animation: fadeInLeft 0.8s ease;
    }

    .header .brand i {
      background: rgba(255, 255, 255, 0.2);
      padding: 12px;
      border-radius: 12px;
      backdrop-filter: blur(10px);
    }

    .header .clock {
      text-align: right;
      animation: fadeInRight 0.8s ease;
    }

    .header .clock .time {
      font-size: 2.2rem;
      font-weight: 800;
      line-height: 1;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      letter-spacing: 1px;
    }

    .header .clock .date {
      font-size: 0.95rem;
      opacity: 0.95;
      font-weight: 500;
      margin-top: 4px;
    }


    .main-content {
      flex: 1;
      padding: 25px;
      display: flex;
      gap: 25px;
      height: calc(100vh - 120px);
      position: relative;
      z-index: 1;
    }

    /* Left Panel: Active Queue */
    .active-queue-panel {
      flex: 0 0 35%;
      display: flex;
      flex-direction: column;
      animation: fadeInUp 0.6s ease;
      height: 100%;
    }

    .active-card {
      background: var(--card-bg);
      border-radius: 24px;
      box-shadow: 0 10px 40px rgba(16, 185, 129, 0.2);
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      border: 2px solid rgba(255, 255, 255, 0.8);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
    }

    .active-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: var(--accent-gradient);
    }

    .active-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 60px rgba(16, 185, 129, 0.3);
    }

    .active-card .card-header {
      background: var(--accent-gradient);
      color: white;
      padding: 20px;
      text-align: center;
      font-size: 1.3rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 2px;
      box-shadow: 0 4px 15px rgba(16, 185, 129, 0.25);
    }

    .active-card .card-body {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      background: linear-gradient(180deg, #ffffff 0%, #f7fafc 100%);
      padding: 30px;
    }

    .active-queue-number {
      font-size: 5rem;
      font-weight: 900;
      background: var(--accent-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      line-height: 1;
      animation: pulse 2s ease-in-out infinite;
      text-shadow: 0 4px 20px rgba(16, 185, 129, 0.3);
    }

    .active-queue-label {
      font-size: 1.4rem;
      color: #4a5568;
      margin-top: 15px;
      font-weight: 600;
    }

    .active-queue-section {
      background: var(--accent-gradient);
      color: white;
      width: 100%;
      text-align: center;
      padding: 18px;
      font-size: 1.4rem;
      font-weight: 700;
      letter-spacing: 1px;
      box-shadow: 0 -4px 15px rgba(16, 185, 129, 0.25);
    }

    /* Right Panel: Video/Carousel */
    .media-panel,
    .empty-media,
    .carousel,
    .carousel-inner,
    .carousel-item,
    .carousel-item img {
      display: none;
    }

    /* Bottom Panel: Queue Grid */
    .queue-grid-container {
      height: auto;
      margin-top: auto;
      padding-bottom: 15px;
      animation: fadeInUp 1s ease;
    }

    .section-group {
      margin-bottom: 20px;
    }

    .section-group:last-child {
      margin-bottom: 0;
    }

    .queue-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 14px;
      width: 100%;
    }

    /* Scrollbar for right panel */
    div[style*="overflow-y: auto"]::-webkit-scrollbar {
      width: 8px;
    }

    div[style*="overflow-y: auto"]::-webkit-scrollbar-track {
      background: transparent;
    }

    div[style*="overflow-y: auto"]::-webkit-scrollbar-thumb {
      background: rgba(16, 185, 129, 0.3);
      border-radius: 10px;
    }

    div[style*="overflow-y: auto"]::-webkit-scrollbar-thumb:hover {
      background: rgba(16, 185, 129, 0.5);
    }

    .queue-item-card {
      min-height: 180px;
      background: white;
      border-radius: 20px;
      box-shadow: 0 4px 20px rgba(16, 185, 129, 0.1);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      border-top: 6px solid;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      animation: slideInUp 0.5s ease forwards;
      opacity: 0;
    }

    .queue-item-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 12px 35px rgba(16, 185, 129, 0.25);
    }

    .queue-item-card:nth-child(1) { animation-delay: 0.1s; }
    .queue-item-card:nth-child(2) { animation-delay: 0.2s; }
    .queue-item-card:nth-child(3) { animation-delay: 0.3s; }
    .queue-item-card:nth-child(4) { animation-delay: 0.4s; }
    .queue-item-card:nth-child(5) { animation-delay: 0.5s; }



    .queue-item-body {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      gap: 4px;
      background: linear-gradient(180deg, #ffffff 0%, #fafbfe 100%);
      padding: 20px 15px;
    }

    .queue-item-code {
      font-size: 1.5rem;
      font-weight: 900;
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: 0.5px;
      line-height: 1.1;
      word-break: break-word;
      text-align: center;
    }

    .queue-item-status {
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 800;
      padding: 4px 12px;
      border-radius: 999px;
      background: rgba(16, 185, 129, 0.15);
      color: #059669;
    }
    .queue-item-status.status-waiting { background: #fff7e6; color: #d97706; }
    .queue-item-status.status-paid { background: #ecfdf3; color: #047857; }
    .queue-item-status.status-playing { background: #dbeafe; color: #1d4ed8; }
    .queue-item-status.status-available { background: #f3f4f6; color: #6b7280; }

    .queue-meta {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 6px;
      width: 100%;
    }



    /* Footer Marquee */
    .footer-marquee {
      background: var(--header-gradient);
      color: white;
      padding: 12px 0;
      font-size: 1.05rem;
      font-weight: 600;
      white-space: nowrap;
      overflow: hidden;
      position: fixed;
      bottom: 0;
      width: 100%;
      box-shadow: 0 -4px 20px rgba(16, 185, 129, 0.25);
      border-top: 2px solid rgba(255, 255, 255, 0.2);
      z-index: 10;
    }

    .marquee-content {
      display: inline-block;
      padding-left: 100%;
      animation: marquee 25s linear infinite;
      letter-spacing: 0.5px;
    }

    @keyframes marquee {
      0% { transform: translate(0, 0); }
      100% { transform: translate(-100%, 0); }
    }

    /* Colors for cards */
    .color-0 { border-color: #10b981; }
    .color-1 { border-color: #34d399; }
    .color-2 { border-color: #059669; }
    .color-3 { border-color: #6ee7b7; }
    .color-4 { border-color: #14b8a6; }
    .color-5 { border-color: #0d9488; }

    /* Additional info styling */
    .info-badge {
      display: inline-block;
      padding: 4px 10px;
      background: rgba(16, 185, 129, 0.15);
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 600;
      color: #059669;
      margin: 2px;
    }

    .date-badge {
      background: rgba(16, 185, 129, 0.15);
      color: #059669;
    }

    .category-badge {
      background: rgba(237, 137, 54, 0.1);
      color: #ed8936;
    }

    /* Animations */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes fadeInLeft {
      from {
        opacity: 0;
        transform: translateX(-30px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    @keyframes fadeInRight {
      from {
        opacity: 0;
        transform: translateX(30px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    @keyframes slideInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes pulse {
      0%, 100% {
        transform: scale(1);
      }
      50% {
        transform: scale(1.05);
      }
    }

  </style>
</head>
<body>

  @php
    $activeQueue = null;
    $activeSectionName = 'Menunggu...';
    // 1) Prioritaskan yang sedang main / masuk arena
    foreach($sectionQueues as $section) {
        foreach($section['queue'] as $item) {
            if(in_array($item['status'], ['sedang_main', 'masuk_arena'], true)) {
                $activeQueue = $item;
                $activeSectionName = $section['label'];
                break 2;
            }
        }
    }
    // 2) Jika tidak ada, ambil antrean pertama yang ada
    if(!$activeQueue) {
        foreach($sectionQueues as $section) {
            if($section['queue']->isNotEmpty()) {
                $activeQueue = $section['queue']->first();
                $activeSectionName = $section['label'];
                break;
            }
        }
    }
  @endphp

  <!-- Header -->
  <header class="header d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
      <div class="brand">
        <i class="fa-solid fa-layer-group"></i>
        <div>
          <div>{{ $displayTitle ?? 'Layar Display' }}</div>
          <div style="font-size: 0.8rem; font-weight: 400;">Display Antrian</div>
        </div>
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
      <div class="active-card">
        <div class="card-header">NOMOR ANTRIAN</div>
        <div class="card-body">
            @if($activeQueue)
                <div class="active-queue-label mb-3">{{ $activeQueue['penyewa'] }}</div>

                <!-- Date and Category Info -->
                <div class="mt-1"></div>

                <!-- Countdown Timer -->
                @if($activeQueue['status'] === 'sedang_main')
                    @php
                        $now = \Carbon\Carbon::now();
                        $start = \Carbon\Carbon::parse($activeQueue['tanggal'] . ' ' . $activeQueue['jam_mulai']);
                        $isPlaying = $now->gte($start);

                        $topLabel = $isPlaying ? 'SEDANG BERMAIN' : 'JADWAL BOOKING';
                        $bottomLabel = $isPlaying ? 'SISA WAKTU MAIN' : 'DIMULAI DALAM';
                        $targetTime = $isPlaying ? $activeQueue['jam_selesai'] : $activeQueue['jam_mulai'];
                    @endphp
                <div class="mt-4 text-center">
                    <div class="small text-muted text-uppercase fw-bold mb-3 mt-3">{{ $topLabel }}</div>
                    <div class="h3 fw-bold text-dark mb-5">{{ $activeQueue['jam_mulai'] }} - {{ $activeQueue['jam_selesai'] }}</div>
                    <div class="small text-muted text-uppercase fw-bold mb-3">{{ $bottomLabel }}</div>
                    <div id="countdownTimer" class="display-1 fw-bold text-danger mt-3"
                         style="font-size: 6rem; line-height: 1.05; letter-spacing: 2px;"
                         data-end="{{ $targetTime }}"
                         data-date="{{ \Carbon\Carbon::parse($activeQueue['tanggal'])->format('Y-m-d') }}">
                        --:--
                    </div>
                </div>
                @else
                <div class="mt-3 text-center">
                    <div class="small text-muted text-uppercase fw-bold mb-1">JADWAL BOOKING</div>
                    <div class="h4 fw-bold" style="color: #10b981;">{{ $activeQueue['jam_mulai'] }} - {{ $activeQueue['jam_selesai'] }}</div>
                </div>
                @endif
            @else
                <div class="active-queue-label">Belum ada antrian</div>
            @endif
        </div>
      </div>
    </div>

    <!-- Right: Queue Grid (Grouped by Section) -->
    <div style="flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding-right: 10px; height: 100%;">
        @php
            $sectionGroups = $allSchedulesToday ?? collect();
        @endphp

        @forelse($sectionGroups as $sectionIndex => $sectionGroup)
            <div class="section-group" style="animation: fadeInUp 0.6s ease; animation-delay: {{ $sectionIndex * 0.1 }}s; opacity: 0; animation-fill-mode: forwards;">
                <!-- Section Header -->
                <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 12px 20px; border-radius: 16px 16px 0 0; font-weight: 700; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2); display: flex; align-items: center; gap: 10px;"></div>

                <!-- Schedules for this section -->
                <div class="queue-grid-container" style="margin-top: 0; padding: 15px; background: rgba(255, 255, 255, 0.5); border-radius: 0 0 16px 16px; backdrop-filter: blur(10px);">
                    <div class="queue-grid">
                        @php
                            $schedules = collect($sectionGroup['schedules']);

                            // Exclude the active queue if it exists
                            if(isset($activeQueue)) {
                                $schedules = $schedules->reject(function($item) use ($activeQueue) {
                                    return isset($item['kode_tiket']) && $item['kode_tiket'] === $activeQueue['kode_tiket'];
                                });
                            }
                        @endphp

                        @foreach($schedules as $index => $scheduleItem)
                            @php
                                $statusRaw = $scheduleItem['status'] ?? 'tersedia';
                                $statusLabel = match($statusRaw) {
                                    'sedang_main' => 'SEDANG MAIN',
                                    'masuk_arena' => 'MASUK ARENA',
                                    'dibayar' => 'DIBAYAR',
                                    'menunggu' => 'MENUNGGU',
                                    'tersedia' => 'TERSEDIA',
                                    default => strtoupper($statusRaw)
                                };
                                $statusClass = match($statusRaw) {
                                    'dibayar' => 'status-paid',
                                    'sedang_main' => 'status-playing',
                                    'masuk_arena' => 'status-playing',
                                    'menunggu' => 'status-waiting',
                                    'tersedia' => 'status-available',
                                    default => 'status-waiting'
                                };
                                $jamRange = $scheduleItem['jam_mulai'] . ' - ' . $scheduleItem['jam_selesai'];
                                $displayName = $scheduleItem['penyewa'] ?? '-';
                                $dateIso = \Carbon\Carbon::parse($scheduleItem['tanggal'])->format('Y-m-d');
                                $endIso = $dateIso . 'T' . $scheduleItem['jam_selesai'];
                            @endphp
                            <div class="queue-item-card color-{{ $index % 6 }}"
                                 data-end="{{ $endIso ?? '' }}">
                                <div class="queue-item-body">
                                    <div class="queue-item-status {{ $statusClass }} mb-2">{{ $statusLabel }}</div>
                                    <div class="h5 fw-bold text-dark mb-1 text-center text-truncate w-100 px-2">{{ $displayName }}</div>
                                    <div class="text-muted small fw-medium">{{ $jamRange }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="d-flex align-items-center justify-content-center w-100 text-muted" style="flex: 1;">
                <div class="text-center">
                    <i class="fa-solid fa-calendar-xmark fa-3x mb-3" style="opacity: 0.3;"></i>
                    <div>Tidak ada jadwal hari ini</div>
                </div>
            </div>
        @endforelse
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

    // Hilangkan kartu jadwal yang sudah lewat secara realtime (tanpa reload)
    function pruneExpiredSchedules() {
        const now = new Date();
        document.querySelectorAll('.queue-item-card[data-end]').forEach(card => {
            const endStr = card.dataset.end;
            if (!endStr) return;
            const end = new Date(endStr.replace(' ', 'T'));
            if (isNaN(end)) return;
            if (end <= now) {
                card.remove();
            }
        });
    }
    pruneExpiredSchedules();
    setInterval(pruneExpiredSchedules, 15000);
  </script>
</body>
</html>
