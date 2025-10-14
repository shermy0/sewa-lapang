<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $laporan;

    public function __construct($laporan)
    {
        $this->laporan = $laporan;
    }

    // Data untuk Excel
    public function collection()
    {
        return collect(array_map(function($item){
            return [
                'Tanggal'      => $item->tanggal_laporan,
                'User'         => $item->user->name ?? '-',
                'Status'       => ucfirst($item->status),
                'Total Harga'  => $item->total_harga,
            ];
        }, $this->laporan));
    }

    // Header kolom
    public function headings(): array
    {
        return ['Tanggal', 'User', 'Status', 'Total Harga'];
    }

    // Nama sheet
    public function title(): string
    {
        return 'Laporan Pemesanan';
    }

    // Styling Excel
    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // Header bold + background hijau + font putih
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '41A67E'],
            ],
        ]);

        // Border untuk seluruh tabel
        $sheet->getStyle('A1:D' . $highestRow)
              ->getBorders()->getAllBorders()
              ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Format kolom Total Harga sebagai mata uang
        $sheet->getStyle('D2:D' . $highestRow)
              ->getNumberFormat()
              ->setFormatCode('"Rp "#,##0');
    }
}
