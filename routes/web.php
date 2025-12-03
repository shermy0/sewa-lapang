<?php

use App\Http\Controllers\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Admin\LapanganController as AdminLapanganController;
use App\Http\Controllers\Admin\LaporanPenyalahgunaanController as AdminLaporanPenyalahgunaanController;
use App\Http\Controllers\Admin\PembayaranController as AdminPembayaranController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UlasanController;
use App\Http\Controllers\Penyewa\FavoritController as PenyewaFavoritController;
use App\Http\Controllers\Penyewa\LaporanPenyalahgunaanController as PenyewaLaporanPenyalahgunaanController;
use App\Http\Controllers\KelolaRekeningController;
use App\Http\Controllers\PemilikDashboardController;
use App\Http\Controllers\PemilikPemesananController;
use App\Http\Controllers\ScanTiketController;
use App\Http\Controllers\FavoritController;
use App\Http\Controllers\LapanganController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PersetujuanController;
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BandingPemilikController as AdminBandingPemilikController;
use App\Http\Controllers\BandingPemilikController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\PemilikPetugasController;
use App\Http\Controllers\CartTempController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    $user = auth()->user();

    if ($user) {
        if ($user->role === 'penyewa') {
            return redirect()->route('penyewa.beranda');
        }

        if ($user->role === 'pemilik') {
            return redirect()->route('dashboard.pemilik');
        }

        if ($user->role === 'admin') {
            return redirect()->route('dashboard.admin');
        }

        if ($user->role === 'petugas') {
            return redirect()->route('petugas.index');
        }
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    // Forgot password
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
        ->name('password.request');

    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->name('password.email');

    // Reset form + update password 100% satu controller
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])
        ->name('password.reset');

    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])
        ->name('password.update');
});


Route::middleware('auth')->group(function () {
    Route::get('/verify-email', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.success');
        }
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', __('Email verifikasi baru telah dikirim.'));
    })->middleware(['throttle:6,1'])
      ->name('verification.send');

    Route::get('/verifikasi-berhasil', function (Request $request) {
        $user = $request->user();
        if ($user->role === 'penyewa') return redirect()->route('penyewa.beranda');
        if ($user->role === 'pemilik') return redirect()->route('dashboard.pemilik');
        if ($user->role === 'admin') return redirect()->route('dashboard.admin');
        return redirect('/');
    })->name('verification.success');
});

Route::get('/ajukan-banding', [BandingPemilikController::class, 'create'])->name('banding.create');
Route::post('/ajukan-banding', [BandingPemilikController::class, 'store'])->name('banding.store');



Route::middleware(['auth', 'verified', 'role:penyewa'])->group(function () {
Route::get('/sections/{lapangan_id}', [PemesananController::class, 'getSectionsByLapangan']);
// Route untuk ambil detail permintaan perubahan
Route::get('/permintaan-perubahan/{id}', [App\Http\Controllers\PemesananController::class, 'getDetailPermintaan']);
Route::post('/permintaan-perubahan/{id}/setujui', [App\Http\Controllers\PemesananController::class, 'setujuiPermintaan'])
    ->name('permintaan-perubahan.setujui');
Route::post('/pemesanan/{id}/ajukan-perubahan', [PemesananController::class, 'ajukanPerubahan'])->name('pemesanan.ajukanPerubahan');
Route::get('/sections/{lapanganId}', [PemesananController::class, 'getSectionsByLapangan']);
Route::get('/jadwal/{id}', [PemesananController::class, 'getJadwalBySection']);

    Route::post('/permintaan-perubahan/{pemesananId}', [PemesananController::class, 'ajukanPerubahan'])
        ->name('permintaan-perubahan.store');

Route::delete('/permintaan-perubahan/{id}', [PemesananController::class, 'batalkanPermintaan'])
    ->name('permintaan-perubahan.delete');
// Ajukan perubahan
Route::post('/permintaan-perubahan/{pemesanan}', [PemesananController::class, 'ajukanPerubahan'])->name('permintaan.ajukan');

// Endpoint baru: pindah langsung (untuk slot available)
Route::patch('/pemesanan/{pemesanan}/pindah', [PemesananController::class, 'pindahLangsung'])->name('pemesanan.pindah');



    Route::get('/pemesanan/create/{lapangan}', [PemesananController::class, 'create'])->name('pemesanan.create');
    Route::post('/pemesanan/store', [PemesananController::class, 'store'])->name('pemesanan.store');
    Route::post('/pemesanan/update-status', [PemesananController::class, 'updateStatus'])->name('pemesanan.updateStatus');
    Route::post('/pemesanan/success/{id}', [PemesananController::class, 'updateSuccess']);
Route::get('/jadwal/section/{section_id}', [PemesananController::class, 'getJadwalBySection']);
    Route::post('/midtrans/callback', [PemesananController::class, 'midtransCallback']);
    Route::post('/midtrans/token', [PemesananController::class, 'getSnapToken'])->name('midtrans.token');
    Route::get('/midtrans/token-again/{pemesanan}', [PemesananController::class, 'getSnapTokenAgain']);
    Route::post('/pemesanan/{pemesanan}/expire', [PemesananController::class, 'expireNow'])->name('pemesanan.expire');

    Route::delete('/pemesanan/batalkan/{id}', [PemesananController::class, 'batalkan'])->name('pemesanan.batalkan');
    Route::get('/tiket/download/{id}', [PemesananController::class, 'downloadTiket'])->name('tiket.download');

    Route::get('penyewa/tiket', [PemesananController::class, 'riwayatTiket'])->name('penyewa.tiket');
    Route::get('penyewa/pembayaran', [PemesananController::class, 'riwayatBelum'])->name('penyewa.pembayaran');
    Route::get('penyewa/riwayat', [PemesananController::class, 'riwayatBatal'])->name('penyewa.riwayat');

    // BERANDA PENYEWA
    Route::get('/beranda-penyewa', [BerandaController::class, 'index'])->name('penyewa.beranda');
    Route::get('/penyewa/detail/{id}', [BerandaController::class, 'detail'])->name('penyewa.detail');

    // ULASAN PENYEWA
    Route::prefix('ulasan')->group(function () {
        Route::post('/{lapangan}', [UlasanController::class, 'simpan'])->name('ulasan.simpan');
        Route::get('/{id}/edit', [UlasanController::class, 'edit'])->name('ulasan.edit');
        Route::put('/{id}/update', [UlasanController::class, 'update'])->name('ulasan.update');
        Route::delete('/{id}', [UlasanController::class, 'destroy'])->name('ulasan.hapus');
    });

    // FAVORIT PENYEWA
    Route::get('favorit', [PenyewaFavoritController::class, 'index'])->name('favorit.index');
    Route::post('lapangan/{lapangan}/favorit', [PenyewaFavoritController::class, 'store'])->name('favorit.store');
    Route::delete('lapangan/{lapangan}/favorit', [PenyewaFavoritController::class, 'destroy'])->name('favorit.destroy');

    Route::middleware('role:penyewa')->prefix('penyewa')->name('penyewa.')->group(function () {
        Route::get('/laporan', [PenyewaLaporanPenyalahgunaanController::class, 'index'])->name('laporan.index');
        Route::post('/laporan', [PenyewaLaporanPenyalahgunaanController::class, 'store'])->name('laporan.store');
    });
});

Route::middleware('auth')->get('/test-sidebar', function () {
    return view('dashboard');
})->name('test.sidebar');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/hapus-foto', [ProfileController::class, 'hapusFoto'])->name('profile.hapusFoto');
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});


Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

    // PETUGAS KASIR
    Route::middleware(['auth', 'verified', 'role:petugas'])
        ->prefix('petugas')->name('petugas.')
        ->group(function () {

        // Dashboard petugas
        Route::get('/', [PetugasController::class, 'index'])->name('index');
        Route::post('/store', [PetugasController::class, 'store'])->name('petugas.store');

        // Penyewa
        Route::get('/penyewa', [PetugasController::class, 'penyewa'])->name('penyewa');
        Route::post('/penyewa/store', [PetugasController::class, 'storePenyewa'])->name('penyewa.store');
        Route::delete('/penyewa/{id}', [PetugasController::class, 'destroyPenyewa'])->name('penyewa.destroy');

        // Tiket
        Route::get('/tiket', [PetugasController::class, 'tiket'])->name('tiket');
                
        // Search penyewa (AJAX)
        Route::get('/penyewa/search', [PetugasController::class, 'searchPenyewa'])->name('penyewa.search');

        // API jadwal lapangan
        Route::get('/api/jadwal/{lapangan}', [PetugasController::class, 'getJadwalLapangan']);
        // Untuk dropdown display: lapangan + section
        Route::get('/api/lapangan-sections', [PetugasController::class, 'getLapanganWithSections'])->name('api.lapangan-sections');

        // Section 
        Route::get('/sections/{lapanganId}', [PetugasController::class, 'getSections'])->name('petugas.getSections');

        // Payment
        Route::post('/payment/cash', [PetugasController::class, 'storeCash'])->name('store.cash');
        Route::post('/payment/midtrans', [PetugasController::class, 'storeMidtrans'])->name('store.midtrans');
        Route::post('/payment/check', [PetugasController::class, 'checkPaymentStatus'])->name('payment.check');        
        Route::post('/pemesanan/{pemesanan}/midtrans/token', [PetugasController::class, 'midtransPayAgain'])->name('pemesanan.midtrans.token');
        Route::post('/pemesanan/{pemesanan}/midtrans/success', [PetugasController::class, 'midtransSuccess'])->name('pemesanan.midtrans.success');

        // CART TEMP — FULL DB AUTO SAVE
        Route::post('/cart-temp', [CartTempController::class, 'store'])->name('cart-temp.store');
        Route::get('/cart-temp', [CartTempController::class, 'index'])->name('cart-temp.index');
        Route::delete('/cart-temp/{id}', [CartTempController::class, 'destroy']);
        Route::delete('/cart-temp', [CartTempController::class, 'clear']);
        Route::post('/cart-temp/nama', [CartTempController::class, 'updateNama']);

        // Scan tiket
        Route::get('/scan', [ScanTiketController::class, 'index'])->name('scan');
        Route::get('/verify-tiket/{kode}', [ScanTiketController::class, 'verifyTiket'])->name('verify-tiket');
        Route::get('/display', [PetugasController::class, 'display'])->name('display');
        Route::get('/api/lapangan-list', [PetugasController::class, 'getLapanganList'])->name('api.lapangan-list');
    });

Route::middleware(['auth', 'verified', 'role:pemilik'])->group(function () {

    Route::get('/kelolapetugas', [PemilikPetugasController::class, 'index'])->name('pemilik.petugas');
    Route::post('/kelolapetugas', [PemilikPetugasController::class, 'store'])->name('pemilik.petugas.store');

    // PERSETUJUAN PEMILIK
    Route::get('/persetujuan', [PersetujuanController::class, 'index'])->name('persetujuan.index');
    Route::put('/persetujuan/{id}', [PersetujuanController::class, 'update']);
    Route::get('/dashboard/pemilik', [PemilikDashboardController::class, 'index'])->name('dashboard.pemilik');
    Route::get('/favorit/pemilik', [FavoritController::class, 'index'])->name('pemilik.favorit');
    Route::get('/pemilik/pemesanan', [PemilikPemesananController::class, 'index'])->name('pemilik.pemesanan.index');

    // CRUD Kategori
    Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
    Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
    Route::get('/kategori/{id}', [KategoriController::class, 'show'])->name('kategori.show');
    Route::put('/kategori/{id}', [KategoriController::class, 'update'])->name('kategori.update');
    Route::delete('/kategori/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

    // CRUD Lapangan
    Route::get('/lapangan', [LapanganController::class, 'index'])->name('lapangan.index');
    Route::post('/lapangan', [LapanganController::class, 'store'])->name('lapangan.store');
    Route::get('/lapangan/{id}', [LapanganController::class, 'show'])->name('lapangan.show');
    Route::put('/lapangan/{id}', [LapanganController::class, 'update'])->name('lapangan.update');
    Route::delete('/lapangan/{id}', [LapanganController::class, 'destroy'])->name('lapangan.destroy');

    // Jadwal Lapangan
    Route::post('/lapangan/{lapanganId}/jadwal', [LapanganController::class, 'storeJadwal'])->name('lapangan.jadwal.store');
    Route::put('/lapangan/{lapanganId}/jadwal/{jadwalId}', [LapanganController::class, 'updateJadwal'])->name('lapangan.jadwal.update');
    Route::delete('/lapangan/{lapanganId}/jadwal/{jadwalId?}', [LapanganController::class, 'destroyJadwal'])->name('lapangan.jadwal.destroy');
    Route::get('/lapangan/{lapanganId}/section/{sectionId}/jadwal', [LapanganController::class, 'getSectionJadwal'])->name('lapangan.section.jadwal');

    // API Tiket (optional)
    Route::post('/lapangan/{id}/reduce-ticket/{quantity?}', [LapanganController::class, 'reduceTicket'])->name('lapangan.reduceTicket');
    Route::post('/lapangan/{id}/add-ticket/{quantity?}', [LapanganController::class, 'addTicket'])->name('lapangan.addTicket');

});

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/dashboard/admin', [AdminDashboardController::class, 'index'])
        ->name('dashboard.admin');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::get('users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::patch('users/{user}/status', [AdminUserController::class, 'updateStatus'])->name('users.update-status');
        Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        // Laporan penyalahgunaan
        Route::get('/laporan-penyalahgunaan', [AdminLaporanPenyalahgunaanController::class, 'index'])->name('laporan.penyalahgunaan.index');
        Route::get('/laporan-penyalahgunaan/{laporanPenyalahgunaan}', [AdminLaporanPenyalahgunaanController::class, 'show'])->name('laporan.penyalahgunaan.show');
        Route::patch(
            '/laporan-penyalahgunaan/{laporanPenyalahgunaan}/status',
            [AdminLaporanPenyalahgunaanController::class, 'updateStatus']
        )->name('laporan.penyalahgunaan.update-status');
        Route::delete('/laporan-penyalahgunaan/{laporanPenyalahgunaan}', [AdminLaporanPenyalahgunaanController::class, 'destroy'])->name('laporan.penyalahgunaan.destroy');

        // Banding pemilik
        Route::get('/banding', [AdminBandingPemilikController::class, 'index'])->name('banding.index');
        Route::get('/banding/{bandingPemilik}', [AdminBandingPemilikController::class, 'show'])->name('banding.show');
        Route::put('/banding/{bandingPemilik}', [AdminBandingPemilikController::class, 'update'])->name('banding.update');
        Route::get('/banding/{bandingPemilik}/lampiran', [AdminBandingPemilikController::class, 'lampiran'])->name('banding.lampiran');

        // Banner routes
        Route::get('banners', [BannerController::class, 'index'])->name('banners.index');
        Route::get('banners/create', [BannerController::class, 'create'])->name('banners.create');
        Route::post('banners', [BannerController::class, 'store'])->name('banners.store');
        Route::delete('admin/banners/{banner}', [BannerController::class, 'destroy'])->name('banners.destroy');
        Route::get('banners/{banner}/edit', [BannerController::class, 'edit'])->name('banners.edit');
        Route::put('banners/{banner}', [BannerController::class, 'update'])->name('banners.update');
        Route::patch('banners/{banner}/toggle', [BannerController::class, 'toggle'])->name('banners.toggle');

        Route::get('lapangan', [AdminLapanganController::class, 'index'])->name('lapangan.index');
        Route::get('lapangan/{lapangan}', [AdminLapanganController::class, 'show'])->name('lapangan.show');

        Route::get('pembayaran', [AdminPembayaranController::class, 'index'])->name('pembayaran.index');
        Route::put('pembayaran/{pembayaran}', [AdminPembayaranController::class, 'update'])->name('pembayaran.update');

        Route::get('akun', [AdminAccountController::class, 'edit'])->name('account.edit');
        Route::put('akun', [AdminAccountController::class, 'update'])->name('account.update');
    });
    });