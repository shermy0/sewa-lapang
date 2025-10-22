<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PencairanDana;
use App\Models\RekeningPemilik;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class DisbursementController extends Controller
{
    public function kirimDana(Request $request)
    {
        $user = Auth::user();
        $rekening = RekeningPemilik::where('pemilik_id', $user->id)->first();

        if (!$rekening) {
            return back()->with('error', 'Data rekening belum diisi.');
        }

        $jumlah = $request->input('jumlah'); // nominal yang ingin dicairkan

        try {
            $payload = [
                'bank_code' => strtoupper($rekening->nama_bank),
                'account_number' => $rekening->nomor_rekening,
                'account_holder_name' => $rekening->atas_nama,
                'amount' => (int) $jumlah,
                'remark' => 'Pencairan dana sewa lapangan',
            ];

            $response = Http::withBasicAuth(env('MIDTRANS_SERVER_KEY'), '')
                ->post(env('MIDTRANS_PAYOUT_URL'), $payload);

            $data = $response->json();

            // Simpan ke tabel pencairan_dana
            PencairanDana::create([
                'pemilik_id' => $user->id,
                'bank_tujuan' => $rekening->nama_bank,
                'nomor_rekening' => $rekening->nomor_rekening,
                'atas_nama' => $rekening->atas_nama,
                'jumlah' => $jumlah,
                'status' => $data['status'] ?? 'proses',
                'disbursement_id' => $data['disbursement_id'] ?? null,
            ]);

            return back()->with('success', 'Permintaan pencairan dana berhasil dikirim!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim pencairan: ' . $e->getMessage());
        }
    }
}
