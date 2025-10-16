<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lapangan;
use App\Models\Pemesanan;
use App\Models\JadwalLapangan;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Auth;
use Midtrans\Snap;
use Barryvdh\DomPDF\Facade\Pdf;


class PemesananController extends Controller
{

public function downloadTiket($id)
{
    $pemesanan = Pemesanan::findOrFail($id);
    $pdf = Pdf::loadView('penyewa.tiket-download', compact('pemesanan'))
              ->setPaper('a4', 'landscape');
    return $pdf->download('Tiket_'.$pemesanan->kode_tiket.'.pdf');
}
    public function create($lapangan_id)
{
    $lapangan = Lapangan::findOrFail($lapangan_id);
    $userId = Auth::id();

    $jadwalTersedia = JadwalLapangan::where('lapangan_id', $lapangan_id)
        ->where('tersedia', true)
        ->orderBy('tanggal')
        ->orderBy('jam_mulai')
        ->get();

    // 🔹 Cek apakah user sudah punya pemesanan menunggu di lapangan ini
    $pemesananPending = Pemesanan::where('penyewa_id', $userId)
        ->where('lapangan_id', $lapangan_id)
        ->where('status', 'menunggu')
        ->with('pembayaran')
        ->first();

    return view('pemesanan.create', compact('lapangan', 'jadwalTersedia', 'pemesananPending'));
}

    // ========================== HALAMAN TIKET ==========================
    public function riwayatTiket()
    {
        $userId = Auth::id();
        $sudahDibayar = Pemesanan::where('penyewa_id', $userId)
                        ->where('status', 'dibayar')
                        ->get();

        return view('penyewa.tiket', compact('sudahDibayar'));
    }

    // ========================== HALAMAN MENUNGGU PEMBAYARAN ==========================
    public function riwayatBelum()
    {
        $userId = Auth::id();
        $belumDibayar = Pemesanan::where('penyewa_id', $userId)
                        ->where('status', 'menunggu')
                        ->get();

        return view('penyewa.pembayaran', compact('belumDibayar'));
    }

    // ========================== HALAMAN RIWAYAT / DIBATALKAN ==========================
    public function riwayatBatal()
    {
        $userId = Auth::id();
        $dibatalkan = Pemesanan::where('penyewa_id', $userId)
                        ->where('status', 'batal')
                        ->orWhere('status', 'di-scan')
                        ->get();

        return view('penyewa.riwayat', compact('dibatalkan'));
    }


public function getSnapToken(Request $request)
{
    $lapangan = Lapangan::findOrFail($request->lapangan_id);
    $jadwal = JadwalLapangan::findOrFail($request->jadwal_id);

    // cek pemesanan menunggu sebelumnya
    $pemesanan = Pemesanan::firstOrCreate(
        [
            'penyewa_id' => Auth::id(),
            'lapangan_id' => $lapangan->id,
            'jadwal_id' => $jadwal->id,
            'status' => 'menunggu'
        ],
        [] // jika tidak ada, otomatis dibuat
    );

    $snapToken = Snap::getSnapToken([
        'transaction_details' => [
            'order_id' => 'ORDER-' . $pemesanan->id,
            'gross_amount' => $lapangan->harga_per_jam,
        ],
        'customer_details' => [
            'first_name' => Auth::user()->name,
            'email' => Auth::user()->email,
        ],
    ]);

    // update atau buat pembayaran pending baru
    Pembayaran::updateOrCreate(
        ['pemesanan_id' => $pemesanan->id],
        [
            'metode' => 'midtrans',
            'jumlah' => $lapangan->harga_per_jam,
            'status' => 'pending',
            'order_id' => 'ORDER-' . $pemesanan->id,
            'snap_token' => $snapToken,
        ]
    );

    return response()->json([
        'snap_token' => $snapToken,
        'pemesanan_id' => $pemesanan->id
    ]);
}

public function batalkan($id)
{
    $pemesanan = Pemesanan::findOrFail($id);

    if ($pemesanan->penyewa_id != Auth::id()) {
        abort(403, 'Tidak boleh membatalkan pemesanan orang lain.');
    }

    $pemesanan->update(['status' => 'batal']);

    // Jadwal kembali tersedia
    if ($pemesanan->jadwal) {
        $pemesanan->jadwal->update(['tersedia' => true]);
    }

    // Hapus atau update pembayaran (optional)
    if ($pemesanan->pembayaran) {
        $pemesanan->pembayaran->update(['status' => 'batal']);
    }

    return redirect()->back()->with('success', 'Pemesanan berhasil dibatalkan.');
}


public function getSnapTokenAgain(Pemesanan $pemesanan)
{
    try {
        $lapangan = $pemesanan->lapangan;

        // buat order_id unik tiap generate token
        $uniqueOrderId = 'ORDER-' . $pemesanan->id . '-' . time();

        $snapToken = Snap::getSnapToken([
            'transaction_details' => [
                'order_id' => $uniqueOrderId,
                'gross_amount' => $lapangan->harga_per_jam,
            ],
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ],
        ]);

        // pastikan pembayaran ada
        $pembayaran = $pemesanan->pembayaran;
        if(!$pembayaran){
            $pembayaran = Pembayaran::create([
                'pemesanan_id' => $pemesanan->id,
                'metode' => 'midtrans',
                'jumlah' => $lapangan->harga_per_jam,
                'status' => 'pending',
                'order_id' => $uniqueOrderId,
                'snap_token' => $snapToken,
            ]);
        } else {
            $pembayaran->update([
                'snap_token' => $snapToken,
                'status' => 'pending',
                'order_id' => $uniqueOrderId,
            ]);
        }

        return response()->json([
            'snap_token' => $snapToken,
            'pemesanan_id' => $pemesanan->id
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}



    // Simpan pemesanan awal (status menunggu)
    public function store(Request $request)
    {
        $request->validate([
            'lapangan_id' => 'required|exists:lapangan,id',
            'jadwal_id' => 'required|exists:jadwal_lapangan,id',
            'snap_token' => 'required',
        ]);

        $jadwal = JadwalLapangan::findOrFail($request->jadwal_id);
        if (!$jadwal->tersedia) {
            return back()->with('error', 'Jadwal sudah dipesan!');
        }

        $lapangan = Lapangan::findOrFail($request->lapangan_id);

        // 🔹 Simpan pemesanan dengan status menunggu
        $pemesanan = Pemesanan::create([
            'penyewa_id' => Auth::id(),
            'lapangan_id' => $lapangan->id,
            'jadwal_id' => $jadwal->id,
            'status' => 'menunggu',
        ]);

        // 🔹 Simpan pembayaran pending
        Pembayaran::create([
            'pemesanan_id' => $pemesanan->id,
            'metode' => 'midtrans',
            'jumlah' => $lapangan->harga_per_jam,
            'status' => 'pending',
            'order_id' => 'ORDER-' . time(),
            'snap_token' => $request->snap_token,
        ]);

        return response()->json([
            'snap_token' => $request->snap_token,
            'pemesanan_id' => $pemesanan->id,
        ]);
    }

    // Update status sukses
public function updateSuccess(Request $request, $id)
{
    $pemesanan = Pemesanan::findOrFail($id);

    // Ubah status pemesanan & buat kode tiket
    $pemesanan->update([
        'status' => 'dibayar',
        'kode_tiket' => $this->generateShortTicketCode(),
    ]);

    // Update status pembayaran
    $pembayaran = $pemesanan->pembayaran;
    $pembayaran->update([
        'status' => 'berhasil',
    ]);

    // 🔹 Tambahan penting: tandai jadwal jadi tidak tersedia
    $jadwal = $pemesanan->jadwal;
    if ($jadwal) {
        $jadwal->update(['tersedia' => false]);
    }

    return response()->json(['success' => true]);
}


        private function generateShortTicketCode()
    {
        $prefix = 'LPN'; // bisa diganti misal "LPN" untuk lapangan
        $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)); 
        return $prefix . $random; // contoh hasil: TK7F3C9A
    }

    

}