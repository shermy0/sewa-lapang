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
      position: relative;
    }
    
    /* Animated background effect */
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: 
        radial-gradient(circle at 20% 30%, rgba(16, 185, 129, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(5, 150, 105, 0.15) 0%, transparent 50%);
      pointer-events: none;
      z-index: 0;
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
      padding: 25px 25px 75px;
      display: flex;
      gap: 25px;
      height: calc(100vh - 120px);
      position: relative;
      z-index: 1;
    }

    /* Left Panel: Active Queue */
    .active-queue-panel {
      flex: 0 0 38%;
      display: flex;
      flex-direction: column;
      gap: 25px;
      animation: fadeInUp 0.6s ease;
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
    .media-panel {
      flex: 1;
      background: #000;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
      position: relative;
      border: 2px solid rgba(255, 255, 255, 0.1);
      animation: fadeInUp 0.8s ease;
    }
    
    .carousel, .carousel-inner, .carousel-item {
      height: 100%;
    }
    
    .carousel-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }
    
    .carousel-item img:hover {
      transform: scale(1.05);
    }

    /* Bottom Panel: Queue Grid */
    .queue-grid-container {
      height: auto;
      max-height: 260px;
      margin-top: auto;
      padding-bottom: 15px;
      animation: fadeInUp 1s ease;
    }
    
    .queue-grid {
      display: flex;
      gap: 14px;
      height: 100%;
      overflow-x: auto;
      padding-bottom: 10px;
      scrollbar-width: thin;
      scrollbar-color: rgba(16, 185, 129, 0.4) transparent;
    }
    
    .queue-grid::-webkit-scrollbar {
      height: 8px;
    }
    
    .queue-grid::-webkit-scrollbar-track {
      background: transparent;
    }
    
    .queue-grid::-webkit-scrollbar-thumb {
      background: rgba(16, 185, 129, 0.4);
      border-radius: 10px;
    }
    
    .queue-item-card {
      flex: 0 0 220px;
      min-height: 230px;
      background: white;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(16, 185, 129, 0.15);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      border-top: 5px solid;
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
    
    .queue-item-header {
      padding: 10px 12px 8px;
      text-align: center;
      font-weight: 700;
      font-size: 0.9rem;
      background: linear-gradient(180deg, #f8f9fc 0%, #ffffff 100%);
      border-bottom: 2px solid rgba(16, 185, 129, 0.15);
      line-height: 1.2;
      min-height: 50px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 2px;
    }
    
    .queue-location {
      font-size: 0.75rem;
      color: #6b7280;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .queue-item-body {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      align-items: center;
      gap: 8px;
      background: linear-gradient(180deg, #ffffff 0%, #fafbfe 100%);
      padding: 10px 12px;
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

    .queue-upcoming-title {
      font-size: 0.75rem;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      color: #6b7280;
      font-weight: 700;
      margin-top: 6px;
    }

    .queue-upcoming-list {
      list-style: none;
      padding: 0;
      margin: 6px 0 0;
      display: flex;
      flex-direction: column;
      gap: 4px;
      width: 100%;
    }

    .queue-upcoming-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: rgba(255, 255, 255, 0.6);
      border-radius: 10px;
      padding: 6px 10px;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
    }

    .queue-upcoming-item .time-badge {
      background: rgba(16, 185, 129, 0.1);
      color: #0f9d58;
      padding: 2px 8px;
      border-radius: 8px;
      font-size: 0.8rem;
      font-weight: 700;
      margin-right: 8px;
      white-space: nowrap;
    }

    .queue-upcoming-item .guest {
      flex: 1;
      font-weight: 600;
      font-size: 0.9rem;
      color: #1f2937;
      margin-right: 8px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .queue-upcoming-item .code {
      font-family: 'Inter', monospace;
      font-size: 0.8rem;
      color: #6b7280;
      font-weight: 700;
      white-space: nowrap;
    }
    
    .queue-meta {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 6px;
      width: 100%;
    }
    
    .queue-item-footer {
      font-size: 0.8rem;
      text-align: center;
      padding: 8px 10px 10px;
      color: #111827;
      font-weight: 600;
      background: linear-gradient(180deg, #fafbfe 0%, #f8f9fc 100%);
      border-top: 1px solid rgba(16, 185, 129, 0.08);
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
    .color-0 .queue-item-header { background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%); color: #10b981; }
    
    .color-1 { border-color: #34d399; }
    .color-1 .queue-item-header { background: linear-gradient(135deg, rgba(52, 211, 153, 0.1) 0%, rgba(52, 211, 153, 0.05) 100%); color: #34d399; }
    
    .color-2 { border-color: #059669; }
    .color-2 .queue-item-header { background: linear-gradient(135deg, rgba(5, 150, 105, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%); color: #059669; }
    
    .color-3 { border-color: #6ee7b7; }
    .color-3 .queue-item-header { background: linear-gradient(135deg, rgba(110, 231, 183, 0.1) 0%, rgba(110, 231, 183, 0.05) 100%); color: #047857; }
    
    .color-4 { border-color: #14b8a6; }
    .color-4 .queue-item-header { background: linear-gradient(135deg, rgba(20, 184, 166, 0.1) 0%, rgba(20, 184, 166, 0.05) 100%); color: #14b8a6; }
    
    .color-5 { border-color: #0d9488; }
    .color-5 .queue-item-header { background: linear-gradient(135deg, rgba(13, 148, 136, 0.1) 0%, rgba(13, 148, 136, 0.05) 100%); color: #0d9488; }
    
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
        $activeQueue = null;
        $activeSectionName = 'Menunggu...';
        // 1) Prioritaskan yang sedang main
        foreach($sectionQueues as $section) {
            foreach($section['queue'] as $item) {
                if(($item['status'] ?? '') === 'sedang_main') {
                    $activeQueue = $item;
                    $activeSectionName = $section['label'];
                    break 2;
                }
            }
        }
        // 2) Jika tidak ada, ambil jadwal terdekat
        if(!$activeQueue) {
            $nearestTime = null;
            foreach($sectionQueues as $section) {
                if($section['queue']->isNotEmpty()) {
                    $candidate = $section['queue']->first();
                    $candidateTime = \Carbon\Carbon::parse($candidate['tanggal'].' '.$candidate['jam_mulai']);
                    if(is_null($nearestTime) || $candidateTime->lt($nearestTime)) {
                        $nearestTime = $candidateTime;
                        $activeQueue = $candidate;
                        $activeSectionName = $section['label'];
                    }
                }
            }
        }
      @endphp

      <div class="active-card">
        <div class="card-header">NOMOR ANTRIAN</div>
        <div class="card-body">
            @if($activeQueue)
                <div class="active-queue-number">{{ $activeQueue['kode_tiket'] }}</div>
                <div class="active-queue-label">{{ $activeQueue['penyewa'] }}</div>
                
                <!-- Date and Category Info -->
                <div class="mt-3 d-flex flex-wrap justify-content-center gap-2">
                    <span class="info-badge date-badge">
                        <i class="fa-regular fa-calendar me-1"></i>
                        {{ $activeQueue['tanggal'] }}
                    </span>
                    <span class="info-badge category-badge">
                        <i class="fa-solid fa-tag me-1"></i>
                        {{ $activeQueue['kategori'] ?? 'Lapangan' }}
                    </span>
                </div>

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
                    <div class="small text-muted text-uppercase fw-bold mb-1">{{ $topLabel }}</div>
                    <div class="h3 fw-bold text-dark mb-2">{{ $activeQueue['jam_mulai'] }} - {{ $activeQueue['jam_selesai'] }}</div>
                    <div class="small text-muted text-uppercase fw-bold mb-1">{{ $bottomLabel }}</div>
                    <div id="countdownTimer" class="display-4 fw-bold text-danger"
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
                        $statusRaw = $currentQueue['status'] ?? 'menunggu';
                        $statusLabel = $currentQueue ? strtoupper($statusRaw) : 'MENUNGGU';
                        $statusClass = match($statusRaw) {
                            'dibayar' => 'status-paid',
                            'sedang_main' => 'status-playing',
                            default => 'status-waiting'
                        };
                        $jamRange = $currentQueue ? ($currentQueue['jam_mulai'] . ' - ' . $currentQueue['jam_selesai']) : '';
                    @endphp
                    <div class="queue-item-card color-{{ $index % 6 }}">
                        <div class="queue-item-header">
                            <div class="fw-bold text-truncate">{{ $section['label'] }}</div>
                            <div class="queue-location">
                                <i class="fa-solid fa-location-dot me-1"></i>
                                {{ $currentQueue['nama_lapangan'] ?? 'Lapangan' }}
                            </div>
                        </div>
                        <div class="queue-item-body">
                            <div class="queue-item-code">{{ $currentQueue ? $currentQueue['kode_tiket'] : '-' }}</div>
                            <div class="queue-item-status {{ $statusClass }}">{{ $currentQueue ? $statusLabel : 'MENUNGGU' }}</div>
                            
                            @if($currentQueue)
                                <div class="queue-meta mt-1">
                                    <span class="info-badge date-badge" style="font-size: 0.7rem;">
                                        <i class="fa-regular fa-calendar me-1"></i>
                                        {{ $currentQueue['tanggal'] }}
                                    </span>
                                    @if($currentQueue['kategori'])
                                    <span class="info-badge category-badge" style="font-size: 0.7rem;">
                                        <i class="fa-solid fa-tag me-1"></i>
                                        {{ $currentQueue['kategori'] }}
                                    </span>
                                    @endif
                                </div>
                                @if($section['queue']->count() > 1)
                                    <div class="queue-upcoming-title">Selanjutnya</div>
                                    <ul class="queue-upcoming-list">
                                        @foreach($section['queue']->skip(1) as $upcoming)
                                            <li class="queue-upcoming-item">
                                                <span class="time-badge">{{ $upcoming['jam_mulai'] }}-{{ $upcoming['jam_selesai'] }}</span>
                                                <span class="guest">{{ $upcoming['penyewa'] }}</span>
                                                <span class="code">{{ $upcoming['kode_tiket'] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            @endif
                        </div>
                        <div class="queue-item-footer">
                             {{ $currentQueue ? $currentQueue['penyewa'] : 'Kosong' }}
                             @if($jamRange)
                                <div class="text-muted mt-1" style="font-size: 0.78rem; font-weight: 500;">
                                    <i class="fa-regular fa-clock me-1"></i>
                                    {{ $jamRange }}
                                </div>
                             @endif
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
