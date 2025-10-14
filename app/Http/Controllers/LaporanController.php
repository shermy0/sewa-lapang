<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanExport;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    // Halaman laporan
    public function index(Request $request)
    {
        // Data dummy sementara
        $laporan = [
            (object)['tanggal_laporan'=>'2025-10-01','user'=>(object)['name'=>'Bayu'],'status'=>'selesai','total_harga'=>150000],
            (object)['tanggal_laporan'=>'2025-10-02','user'=>(object)['name'=>'Dinda'],'status'=>'pending','total_harga'=>100000],
            (object)['tanggal_laporan'=>'2025-10-03','user'=>(object)['name'=>'Rian'],'status'=>'dibatalkan','total_harga'=>200000],
        ];

        return view('laporan.index', compact('laporan'));
    }

    // Export Excel
    public function exportExcel()
    {
        $laporan = $this->getDummyLaporan();
        return Excel::download(new LaporanExport($laporan), 'laporan.xlsx');
    }

    // Export PDF
    public function exportPdf()
    {
        $laporan = $this->getDummyLaporan();
        $pdf = Pdf::loadView('laporan.pdf', compact('laporan'));
        return $pdf->download('laporan.pdf');
    }

    // Data dummy
    private function getDummyLaporan()
    {
        return [
            (object)['tanggal_laporan'=>'2025-10-01','user'=>(object)['name'=>'Bayu'],'status'=>'selesai','total_harga'=>150000],
            (object)['tanggal_laporan'=>'2025-10-02','user'=>(object)['name'=>'Dinda'],'status'=>'pending','total_harga'=>100000],
            (object)['tanggal_laporan'=>'2025-10-03','user'=>(object)['name'=>'Rian'],'status'=>'dibatalkan','total_harga'=>200000],
        ];
    }
}
