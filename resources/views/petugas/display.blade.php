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
    $nowJakarta = \Carbon\Carbon::now('Asia/Jakarta');

    // Filter hanya jadwal yang sudah dibayar/terisi (TIDAK termasuk menunggu atau transaksi pending)
    $paidStatuses = ['dibayar', 'terisi', 'sedang_main', 'masuk_arena'];

$flatSchedules = collect($allSchedulesToday ?? [])->flatMap(function ($section) {
    return collect($section['schedules'] ?? [])
        ->map(function ($item) {
            $dateIso = \Carbon\Carbon::createFromFormat('d M Y', $item['tanggal'])->format('Y-m-d');
            return array_merge($item, [
                'date_iso' => $dateIso,
                'start_at' => $dateIso . 'T' . $item['jam_mulai'] . ':00',
                'end_at'   => $dateIso . 'T' . $item['jam_selesai'] . ':00',
            ]);
        });
})->sortBy('start_at')->values();


// PRIORITAS UTAMA — jadwal yang sedang berlangsung SEKARANG
$activeSchedule = $flatSchedules->first(function ($item) use ($nowJakarta) {
    $start = \Carbon\Carbon::parse($item['start_at'], 'Asia/Jakarta');
    $end   = \Carbon\Carbon::parse($item['end_at'], 'Asia/Jakarta');
    return $nowJakarta->between($start, $end);
});

// FALLBACK — jadwal setelah NOW
if (! $activeSchedule) {
    $activeSchedule = $flatSchedules->first(function ($item) use ($nowJakarta) {
        return \Carbon\Carbon::parse($item['start_at'], 'Asia/Jakarta')->gt($nowJakarta);
    });
}



    // activeSchedule bisa null jika tidak ada jadwal terbayar
    $hasActiveSchedule = !is_null($activeSchedule);

    $brandLapangan = $displayTitle ?? 'Layar Display';
    $brandSection = null;
    $activeCommunity = null;
    $isPlaying = false;
    $activeStatus = 'kosong';

    // Ambil section name dari jadwal pertama jika tidak ada active schedule
    if (!empty($allSchedulesToday)) {
        $firstSection = collect($allSchedulesToday)->first();
        $brandSection = $firstSection['section_name'] ?? null;
    }

    if ($hasActiveSchedule) {
        $brandLapangan = $activeSchedule['nama_lapangan'] ?? ($displayTitle ?? 'Layar Display');
        $brandSection = $activeSchedule['nama_section'] ?? $activeSchedule['section_name'] ?? $brandSection;
        $activeCommunity = $activeSchedule['nama_komunitas'] ?? $activeSchedule['penyewa'] ?? null;
        $activeStatus = $activeSchedule['status'] ?? 'kosong';
        $isPlaying = ($activeStatus === 'sedang_main');
    }

    // Gabungkan nama lapangan + section untuk header
    $brandTitle = trim($brandLapangan . ($brandSection ? ' - ' . $brandSection : ''));
    if ($brandTitle === '') {
        $brandTitle = 'Layar Display';
    }
  @endphp

  <!-- Header -->
  <header class="header d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
      <div class="brand">
        <i class="fa-solid fa-layer-group"></i>
        <div>
          <div>{{ $brandTitle }}</div>
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
        @php
if (! $hasActiveSchedule) {
    $activeCommunityLabel = 'KOSONG';
} else {
    $status = strtolower($activeStatus);

    switch ($status) {
        case 'sedang_main':
            $activeCommunityLabel = $activeCommunity ?: 'SEDANG BERMAIN';
            break;

        case 'dibayar':
        case 'terisi':
            $activeCommunityLabel = $activeCommunity ?: 'TERISI';
            break;

        case 'kosong':
            $activeCommunityLabel = 'KOSONG';
            break;

        default:
            $activeCommunityLabel = strtoupper($status);
    }
}

        @endphp
        <div class="card-header">{{ $activeCommunityLabel }}</div>
        <div class="card-body">

            @if($hasActiveSchedule && $isPlaying)
                {{-- Tampilan saat sedang bermain (sudah scan masuk lapangan) --}}
                @php
                    $activeStart = \Carbon\Carbon::parse($activeSchedule['start_at'], 'Asia/Jakarta');
                    $activeEnd = \Carbon\Carbon::parse($activeSchedule['end_at'], 'Asia/Jakarta');
                @endphp
                <div class="d-flex flex-column justify-content-center align-items-center text-center w-100" style="min-height: 260px;">
                    <div class="text-muted mb-2" style="font-size: 1rem; font-weight: 600; letter-spacing: 1px;">SISA WAKTU MAIN</div>
                    <div id="countdownTimer" class="display-1 fw-bold text-danger mb-3"
                         style="font-size: 6rem; line-height: 1.05; letter-spacing: 2px;"
                         data-start="{{ $activeStart->format('Y-m-d H:i:s') }}"
                         data-end="{{ $activeEnd->format('Y-m-d H:i:s') }}"
                         data-status="{{ $activeStatus }}">
                        00:00
                    </div>
                    <div class="h3 fw-bold text-dark">{{ $activeSchedule['jam_mulai'] }} - {{ $activeSchedule['jam_selesai'] }}</div>
                </div>
            @elseif($hasActiveSchedule && $activeStatus === 'masuk_arena')
                {{-- Tampilan saat sudah masuk arena (scan gor) tapi belum masuk lapangan --}}
                @php
                    $activeStart = \Carbon\Carbon::parse($activeSchedule['start_at'], 'Asia/Jakarta');
                    $activeEnd = \Carbon\Carbon::parse($activeSchedule['end_at'], 'Asia/Jakarta');
                @endphp
                <div class="d-flex flex-column justify-content-center align-items-center text-center w-100" style="min-height: 260px;">
                    <div class="badge bg-warning text-dark mb-3" style="font-size: 1.2rem; padding: 10px 25px; animation: pulse 2s ease-in-out infinite;">MASUK ARENA</div>
                    <div class="text-muted mb-2" style="font-size: 0.9rem; font-weight: 600; letter-spacing: 1px;">MENUNGGU MASUK LAPANGAN</div>
                    <div class="h1 fw-bold" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 3rem;">
                        {{ $activeSchedule['jam_mulai'] }} - {{ $activeSchedule['jam_selesai'] }}
                    </div>
                </div>
@elseif($hasActiveSchedule)
    @php
        $activeStart = \Carbon\Carbon::parse($activeSchedule['start_at'], 'Asia/Jakarta');
        $activeEnd = \Carbon\Carbon::parse($activeSchedule['end_at'], 'Asia/Jakarta');

        $status = strtolower($activeSchedule['status'] ?? 'tersedia');

        $badgeText = match ($status) {
            'sedang_main' => 'SEDANG BERMAIN',
            'dibayar', 'terisi' => 'TERJADWAL',
            'tersedia' => 'KOSONG',
            default => strtoupper($status),
        };

        $badgeColor = match ($status) {
            'sedang_main' => 'bg-primary',
            'dibayar', 'terisi' => 'bg-success',
            'tersedia' => 'bg-secondary',
            default => 'bg-dark',
        };
    @endphp

    <div class="d-flex flex-column justify-content-center align-items-center text-center w-100" style="min-height: 260px;">
        <div class="badge {{ $badgeColor }} mb-3" style="font-size: 1.2rem; padding: 10px 25px;">
            {{ $badgeText }}
        </div>

        <div class="text-muted mb-2" style="font-size: 0.9rem; font-weight: 600; letter-spacing: 1px;">
            JADWAL MAIN
        </div>

        <div class="h1 fw-bold" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 3rem;">
            {{ $activeSchedule['jam_mulai'] }} - {{ $activeSchedule['jam_selesai'] }}
        </div>
    </div>
@else

                {{-- Tampilan saat lapangan kosong (tidak ada jadwal terbayar) --}}
                <div class="d-flex flex-column justify-content-center align-items-center text-center w-100" style="min-height: 260px;">
                    <div style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); width: 120px; height: 120px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 25px; box-shadow: 0 10px 40px rgba(107, 114, 128, 0.3);">
                        <i class="fa-solid fa-futbol" style="font-size: 3.5rem; color: white;"></i>
                    </div>
                    <div class="h2 fw-bold" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; letter-spacing: 2px;">
                        LAPANGAN KOSONG
                    </div>
                    <div class="text-muted mt-2" style="font-size: 1.1rem;">Tidak ada jadwal terisi saat ini</div>
                </div>
            @endif
        </div>
      </div>
    </div>

    <!-- Right: Queue Grid (ALL Schedules Today) -->
    <div style="flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding-right: 10px; height: 100%;">
        @php
            // Ambil SEMUA jadwal hari ini (menunggu tetap tampil sebagai KOSONG)
            $allSchedulesFlat = collect($allSchedulesToday ?? [])->flatMap(function ($section) use ($nowJakarta) {
                return collect($section['schedules'] ?? [])
                    ->filter(function ($item) use ($nowJakarta) {
                        // Filter: hanya tampilkan jadwal yang belum berakhir
                        $dateIso = \Carbon\Carbon::createFromFormat('d M Y', $item['tanggal'])->format('Y-m-d');
                        $endTime = \Carbon\Carbon::parse($dateIso . ' ' . $item['jam_selesai'], 'Asia/Jakarta');
                        return $endTime->gt($nowJakarta);
                    })
                    ->map(function ($item) use ($section) {
                        return array_merge($item, [
                            'section_name' => $section['section_name'] ?? null,
                            'lapangan_name' => $section['lapangan_name'] ?? null,
                        ]);
                    });
            })->sortBy('jam_mulai')->values();

            // Exclude the active schedule from the grid (karena sudah tampil di panel utama)
            $activeScheduleId = $hasActiveSchedule ? ($activeSchedule['jadwal_id'] ?? null) : null;
            if ($activeScheduleId && $hasActiveSchedule) {
                $allSchedulesFlat = $allSchedulesFlat->reject(function($item) use ($activeScheduleId) {
                    return ($item['jadwal_id'] ?? null) === $activeScheduleId;
                })->values();
            }
        @endphp

        @if($allSchedulesFlat->isNotEmpty())
            <div class="section-group" style="animation: fadeInUp 0.6s ease; opacity: 0; animation-fill-mode: forwards;">
                <!-- Section Header -->
                <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 12px 20px; border-radius: 16px 16px 0 0; font-weight: 700; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2); display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>Jadwal Hari Ini</span>
                </div>

                <!-- All Schedules -->
                <div class="queue-grid-container" style="margin-top: 0; padding: 15px; background: rgba(255, 255, 255, 0.5); border-radius: 0 0 16px 16px; backdrop-filter: blur(10px);">
                    <div class="queue-grid">
                        @foreach($allSchedulesFlat as $index => $scheduleItem)
                            @php
                                $statusRaw = $scheduleItem['status'] ?? 'kosong';
                                // menunggu ditampilkan sebagai TERJADWAL (sudah ada yang booking)
                                $statusLabel = match($statusRaw) {
                                    'sedang_main' => 'SEDANG BERMAIN',
                                    'masuk_arena' => 'MASUK ARENA',
                                    'dibayar', 'terisi', 'menunggu' => 'TERJADWAL',
                                    default => 'KOSONG'
                                };
                                $statusClass = match($statusRaw) {
                                    'sedang_main' => 'status-playing',
                                    'masuk_arena' => 'status-playing',
                                    'dibayar', 'terisi', 'menunggu' => 'status-paid',
                                    default => 'status-available'
                                };
                                $jamRange = $scheduleItem['jam_mulai'] . ' - ' . $scheduleItem['jam_selesai'];
                                $displayName = $scheduleItem['nama_komunitas'] ?? $scheduleItem['penyewa'] ?? null;
                                $dateIso = \Carbon\Carbon::createFromFormat('d M Y', $scheduleItem['tanggal'])->format('Y-m-d');
                                $endIso = $dateIso . 'T' . $scheduleItem['jam_selesai'] . ':00';
                            @endphp
                            <div class="queue-item-card color-{{ $index % 6 }}"
                                 data-end="{{ $endIso ?? '' }}">
                                <div class="queue-item-body">
                                    <div class="queue-item-status {{ $statusClass }} mb-2">{{ $statusLabel }}</div>
                                    <div class="h5 fw-bold text-dark mb-1 text-center text-truncate w-100 px-2">{{ $displayName ?: '-' }}</div>
                                    <div class="text-muted small fw-medium">{{ $jamRange }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="d-flex align-items-center justify-content-center w-100 text-muted" style="flex: 1;">
                <div class="text-center">
                    <i class="fa-solid fa-calendar-xmark fa-3x mb-3" style="opacity: 0.3;"></i>
                    <div>Tidak ada jadwal hari ini</div>
                </div>
            </div>
        @endif
    </div>
  </div>

  <!-- Footer Marquee -->
  <div class="footer-marquee">
    <div class="marquee-content">
      Selamat Datang di SEWALAP. Silakan menunggu nomor antrian Anda dipanggil. Jagalah kebersihan dan ketertiban area lapangan. Terima kasih.
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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

    // Countdown Logic
    function updateCountdown() {
        const timerEl = document.getElementById('countdownTimer');
        if (!timerEl) return;
        const startTimeStr = timerEl.dataset.start;
        const endTimeStr = timerEl.dataset.end;

        if (!startTimeStr || !endTimeStr) return;

        const now = new Date();
        const startTime = new Date(startTimeStr.replace(' ', 'T'));
        const endTime = new Date(endTimeStr.replace(' ', 'T'));

        if (isNaN(startTime) || isNaN(endTime)) return;

        const status = timerEl.dataset.status;
        // Timer hanya jalan jika sudah masuk lapang (sedang_main), bukan masuk arena
        if (status !== 'sedang_main') {
            timerEl.textContent = "00:00";
            timerEl.classList.add('text-muted');
            timerEl.classList.remove('text-danger');
            return;
        }

        // Jika status active (masuk_arena/sedang_main), timer jalan terus (hitung mundur ke end time)
        // Hapus pengecekan now < startTime agar timer tetap jalan walau masuk lebih awal.

        const diff = endTime - now;

        if (diff <= 0) {
            timerEl.textContent = "00:00";
            timerEl.classList.remove('text-danger');
            timerEl.classList.add('text-muted');
            return;
        }

        const totalSeconds = Math.floor(diff / 1000);
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        const hDisplay = hours > 0 ? hours + ":" : "";
        const mDisplay = minutes.toString().padStart(2, '0');
        const sDisplay = seconds.toString().padStart(2, '0');

        timerEl.classList.add('text-danger');
        timerEl.classList.remove('text-muted');
        timerEl.textContent = `${hDisplay}${mDisplay}:${sDisplay}`;
    }

    setInterval(updateCountdown, 1000);
    updateCountdown();

    // AJAX polling untuk update data tanpa refresh halaman
    async function fetchDisplayData() {
        try {
            const url = new URL('{{ route("petugas.api.display-data") }}', window.location.origin);
            // Copy query params dari URL saat ini
            const currentParams = new URLSearchParams(window.location.search);
            currentParams.forEach((value, key) => url.searchParams.set(key, value));

            const response = await fetch(url);
            if (!response.ok) return;
            const data = await response.json();

            updateDisplayFromData(data);
        } catch (e) {
            console.error('Error fetching display data:', e);
        }
    }

    function updateDisplayFromData(data) {
        // Update header label
        const headerEl = document.querySelector('.active-card .card-header');
        const isPlaying = data.isPlaying;
        const activeSchedule = data.activeSchedule;
        const hasActiveSchedule = data.hasActiveSchedule ?? !!activeSchedule;
        const activeCommunity = data.activeCommunity;

        // Determine header label (hanya komunitas)
        let activeCommunityLabel = 'LAPANGAN KOSONG';
        if (hasActiveSchedule && activeSchedule) {
            const communityName = activeCommunity;
            if (communityName) {
                activeCommunityLabel = communityName;
            } else {
                activeCommunityLabel = isPlaying ? 'SEDANG BERMAIN' : 'TERISI';
            }
        }
        if (headerEl) {
            headerEl.textContent = activeCommunityLabel;
        }

        // Update main panel content
        const cardBody = document.querySelector('.active-card .card-body');
        if (!cardBody) return;

        if (hasActiveSchedule && isPlaying) {
            // Show timer view (no badge, just timer)
            cardBody.innerHTML = `
                <div class="d-flex flex-column justify-content-center align-items-center text-center w-100" style="min-height: 260px;">
                    <div class="text-muted mb-2" style="font-size: 1rem; font-weight: 600; letter-spacing: 1px;">SISA WAKTU MAIN</div>
                    <div id="countdownTimer" class="display-1 fw-bold text-danger mb-3"
                         style="font-size: 6rem; line-height: 1.05; letter-spacing: 2px;"
                         data-start="${activeSchedule.start_at}"
                         data-end="${activeSchedule.end_at}"
                         data-status="${activeSchedule.status}">
                        00:00
                    </div>
                    <div class="h3 fw-bold text-dark">${activeSchedule.jam_mulai} - ${activeSchedule.jam_selesai}</div>
                </div>
            `;
            updateCountdown(); // Re-run countdown for new element
        } else if (hasActiveSchedule && activeSchedule.status === 'masuk_arena') {
            // Show MASUK ARENA view (entered gor but not yet on field)
            cardBody.innerHTML = `
                <div class="d-flex flex-column justify-content-center align-items-center text-center w-100" style="min-height: 260px;">
                    <div class="badge bg-warning text-dark mb-3" style="font-size: 1.2rem; padding: 10px 25px; animation: pulse 2s ease-in-out infinite;">MASUK ARENA </div>
                    <div class="text-muted mb-2" style="font-size: 0.9rem; font-weight: 600; letter-spacing: 1px;">MENUNGGU MASUK LAPANGAN</div>
                    <div class="h1 fw-bold" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 3rem;">
                        ${activeSchedule.jam_mulai} - ${activeSchedule.jam_selesai}
                    </div>
                </div>
            `;
        } else if (hasActiveSchedule) {
            // Show TERJADWAL view (paid but not entered arena yet)
            cardBody.innerHTML = `
                <div class="d-flex flex-column justify-content-center align-items-center text-center w-100" style="min-height: 260px;">
                    <div class="badge bg-success mb-3" style="font-size: 1.2rem; padding: 10px 25px;">TERJADWAL</div>
                    <div class="text-muted mb-2" style="font-size: 0.9rem; font-weight: 600; letter-spacing: 1px;">JADWAL MAIN</div>
                    <div class="h1 fw-bold" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 3rem;">
                        ${activeSchedule.jam_mulai} - ${activeSchedule.jam_selesai}
                    </div>
                </div>
            `;
        } else {
            // Show "Lapangan Kosong" view (no paid schedules)
            cardBody.innerHTML = `
                <div class="d-flex flex-column justify-content-center align-items-center text-center w-100" style="min-height: 260px;">
                    <div style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); width: 120px; height: 120px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 25px; box-shadow: 0 10px 40px rgba(107, 114, 128, 0.3);">
                        <i class="fa-solid fa-futbol" style="font-size: 3.5rem; color: white;"></i>
                    </div>
                    <div class="h2 fw-bold" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; letter-spacing: 2px;">
                        LAPANGAN KOSONG
                    </div>
                    <div class="text-muted mt-2" style="font-size: 1.1rem;">Tidak ada jadwal terisi saat ini</div>
                </div>
            `;
        }

        // Update grid cards (only paid schedules)
        updateGridFromData(data.allSchedulesToday, isPlaying, activeSchedule);
    }

    function updateGridFromData(allSchedules, isPlaying, activeSchedule) {
        const gridContainer = document.querySelector('.queue-grid');
        if (!gridContainer) return;

        // Show/hide the empty state container
        const sectionGroup = document.querySelector('.section-group');
        const emptyState = document.querySelector('.queue-grid-container')?.parentElement?.nextElementSibling;

        const activeId = activeSchedule?.jadwal_id;
        const now = new Date();

        let html = '';
        let colorIndex = 0;

        if (!allSchedules) {
            gridContainer.innerHTML = '';
            return;
        }

        allSchedules.forEach(section => {
            (section.schedules || []).forEach(item => {
                // Filter: hanya tampilkan jadwal yang belum berakhir
                const endTime = new Date(item.end_at);
                if (endTime <= now) return;

                // Skip active schedule (sudah tampil di panel utama)
                if (hasActiveSchedule && item.jadwal_id === activeId) return;

                // menunggu ditampilkan sebagai TERJADWAL (sudah ada yang booking)
                const statusLabel = {
                    'sedang_main': 'SEDANG BERMAIN',
                    'masuk_arena': 'MASUK ARENA',
                    'dibayar': 'TERJADWAL',
                    'terisi': 'TERJADWAL',
                    'menunggu': 'TERJADWAL'
                }[item.status] || 'KOSONG';

                const statusClass = {
                    'sedang_main': 'status-playing',
                    'masuk_arena': 'status-playing',
                    'dibayar': 'status-paid',
                    'terisi': 'status-paid',
                    'menunggu': 'status-paid'
                }[item.status] || 'status-available';

                const displayName = item.nama_komunitas || item.penyewa || '-';
                const jamRange = `${item.jam_mulai} - ${item.jam_selesai}`;

                html += `
                    <div class="queue-item-card color-${colorIndex % 6}" data-end="${item.end_at || ''}">
                        <div class="queue-item-body">
                            <div class="queue-item-status ${statusClass} mb-2">${statusLabel}</div>
                            <div class="h5 fw-bold text-dark mb-1 text-center text-truncate w-100 px-2">${displayName}</div>
                            <div class="text-muted small fw-medium">${jamRange}</div>
                        </div>
                    </div>
                `;
                colorIndex++;
            });
        });

        gridContainer.innerHTML = html;
    }

    // Poll setiap 5 detik
    setInterval(fetchDisplayData, 5000);

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
