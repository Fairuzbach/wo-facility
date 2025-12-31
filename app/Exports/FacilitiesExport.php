<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class FacilitiesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->data;
    }

    // 1. MAPPING DATA
    public function map($wo): array
    {
        // A. Logika Plant Asal Mesin
        $originPlant = '-';
        if ($wo->machine && $wo->machine->plant) {
            $originPlant = $wo->machine->plant->name;
        }

        // B. Logika Status Label
        // Default: Ambil status utama dan rapikan (contoh: in_progress -> In Progress)
        $statusLabel = ucfirst(str_replace('_', ' ', $wo->status));

        // [UPDATE] Override jika status internal adalah waiting_spv
        if ($wo->internal_status === 'waiting_spv') {
            $statusLabel = 'Waiting Approval';
        }

        return [
            $wo->ticket_num,
            $wo->created_at ? $wo->created_at->format('d-m-Y H:i') : '-',
            $wo->requester_name,
            $wo->requester_division ?? '-',
            $wo->plant ?? '-',
            $wo->machine ? $wo->machine->name : '-',
            $originPlant,
            $wo->category,
            $wo->description,

            // Menggunakan Label Status yang sudah diolah di atas
            $statusLabel,

            $wo->technicians->pluck('name')->join(', '),
            $wo->actual_completion_date ? \Carbon\Carbon::parse($wo->actual_completion_date)->format('d-m-Y H:i') : '-',
            $wo->completion_note ?? '-',
        ];
    }

    // 2. JUDUL HEADER
    public function headings(): array
    {
        return [
            'No. Tiket',
            'Tgl Lapor',
            'Pelapor',
            'Divisi Pelapor',
            'Lokasi Pengerjaan',
            'Mesin',
            'Plant Asal Mesin',
            'Kategori',
            'Deskripsi Masalah',
            'Status',
            'Teknisi',
            'Tgl Selesai',
            'Catatan Penyelesaian / Pembatalan'
        ];
    }

    // 3. STYLING
    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastColumn = 'M'; // Total 13 Kolom

        // Header Style
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Data Style
        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ]);

        return [];
    }
}
