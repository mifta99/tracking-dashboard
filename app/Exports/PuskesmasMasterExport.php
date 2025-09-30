<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class PuskesmasMasterExport implements FromCollection, WithHeadings,ShouldAutoSize,WithEvents
{
    protected $roles;

    public function __construct(int $roles)
    {
        $this->roles = $roles;
    }
    public function collection()
    {
        $puskesmas = \App\Models\Puskesmas::all();
        $data = collect();

        foreach ($puskesmas as $item) {
            if($this->roles != 2){
                $data->push([
                    $item->district->regency->province->name,
                    $item->district->regency->name,
                    $item->district->name,
                    $item->name,
                    $item->pengiriman && $item->pengiriman->tgl_pengiriman ? $item->pengiriman->tgl_pengiriman->format('Y-m-d') : '',
                    $item->pengiriman && $item->pengiriman->eta ? $item->pengiriman->eta->format('Y-m-d') : '',
                    $item->pengiriman->resi ?? '',
                    $item->pengiriman && $item->pengiriman->equipment ? $item->pengiriman->equipment->serial_number : '',
                    $item->pengiriman && $item->pengiriman->target_alat_diterima ? $item->pengiriman->target_alat_diterima->format('Y-m-d') : '',
                    $item->pengiriman->catatan ?? '',
                    $item->pengiriman && $item->pengiriman->tgl_diterima ? $item->pengiriman->tgl_diterima->format('Y-m-d') : '',
                    $item->pengiriman->nama_penerima ?? '',
                    $item->pengiriman->jabatan_penerima ?? '',
                    $item->pengiriman->instansi_penerima ?? '',
                    $item->pengiriman->nomor_penerima ?? '',
                ]);
                continue;
            }
            $data->push([
                $item->district->regency->province->name,
                $item->district->regency->name,
                $item->district->name,
                $item->name,
                $item->pic,
                $item->kepala,
                $item->pic_dinkes_kab,
                $item->pic_dinkes_prov,
            ]);
        }

        return $data;
    }
    public function headings(): array
    {
        if($this->roles == 2){
            return [
            'Provinsi',
            'Kabupaten / Kota',
            'Kecamatan',
            'Nama Puskesmas',
            'PIC Puskesmas (Petugas ASPAK)',
            'Kepala Puskesmas',
            'PIC Kabupaten / Kota',
            'PIC Dinas Kesehatan Provinsi',
            ];
        }else{
            return [
            'Provinsi',
            'Kabupaten / Kota',
            'Kecamatan',
            'Nama Puskesmas',
            'Tanggal Pengiriman',
            'ETA',
            'RESI',
            'Serial Number',
            'Target Alat Diterima',
            'Catatan',
            'Tanggal Diterima',
            'Nama Penerima',
            'Jabatan Penerima',
            'Instansi Penerima',
            'Nomor Penerima',
            ];
        }
    }
        public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                if($this->roles == 2){
                    $highestColumn = 'H'; // Column H for roles 2
                }else{
                    $highestColumn = 'O'; // Column P for other roles
                }
                    $event->sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                $event->sheet->getDelegate()->setAutoFilter("A1:{$highestColumn}1");
            }
        ];
    }
}
