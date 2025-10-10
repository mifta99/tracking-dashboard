<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PuskesmasMasterExport implements FromCollection, WithHeadings,ShouldAutoSize,WithEvents
{
    protected $roles;
    protected $additionalColumns;
    public function __construct(int $roles, array $additionalColumns = [])
    {
        $this->roles = $roles;
        $this->additionalColumns = $additionalColumns;
    }
    public function collection()
    {
        $puskesmas = \App\Models\Puskesmas::with([
            'district.regency.province', 
            'pengiriman' => function($query) {
            $query->with(['equipment' => function($equipmentQuery) {
                $equipmentQuery->latest('id');
            }]);
            }, 
            'ujiFungsi', 
            'document'
        ])->get();
        $data = collect();

        foreach ($puskesmas as $item) {
            if($this->roles != 2){
                // Build row data dynamically based on selected columns
                $rowData = [
                    $item->id,
                    $item->district->regency->province->name,
                    $item->district->regency->name,
                    $item->district->name,
                    $item->name,
                ];

                // Additional dynamic columns
                $this->addColumnData($rowData, $item, 'pic', $item->pic);
                $this->addColumnData($rowData, $item, 'kepala', $item->kepala);
                $this->addColumnData($rowData, $item, 'pic_dinkes_prov', $item->pic_dinkes_prov);
                $this->addColumnData($rowData, $item, 'pic_dinkes_kab', $item->pic_dinkes_kab);
                $this->addColumnData($rowData, $item, 'tgl_pengiriman', $item->pengiriman && $item->pengiriman->tgl_pengiriman ? $item->pengiriman->tgl_pengiriman->format('d-m-Y') : null);
                $this->addColumnData($rowData, $item, 'eta', $item->pengiriman && $item->pengiriman->eta ? $item->pengiriman->eta->format('d-m-Y') : '');
                $this->addColumnData($rowData, $item, 'resi', $item->pengiriman ? $item->pengiriman->resi : '');
                $this->addColumnData($rowData, $item, 'serial_number', ($item->pengiriman && $item->pengiriman->equipment) ? $item->pengiriman->equipment->serial_number : '');
                $this->addColumnData($rowData, $item, 'catatan', $item->pengiriman ? $item->pengiriman->catatan : '');
                $this->addColumnData($rowData, $item, 'tgl_diterima', $item->pengiriman && $item->pengiriman->tgl_diterima ? $item->pengiriman->tgl_diterima->format('d-m-Y') : null);
                $this->addColumnData($rowData, $item, 'nama_penerima', $item->pengiriman ? $item->pengiriman->nama_penerima : '');
                $this->addColumnData($rowData, $item, 'jabatan_penerima', $item->pengiriman ? $item->pengiriman->jabatan_penerima : '');
                $this->addColumnData($rowData, $item, 'instansi_penerima', $item->pengiriman ? $item->pengiriman->instansi_penerima : '');
                $this->addColumnData($rowData, $item, 'nomor_penerima', $item->pengiriman ? $item->pengiriman->nomor_penerima : '');
                $this->addColumnData($rowData, $item, 'tgl_instalasi', $item->ujiFungsi && $item->ujiFungsi->tgl_instalasi ? $item->ujiFungsi->tgl_instalasi->format('d-m-Y') : null);
                $this->addColumnData($rowData, $item, 'target_tgl_uji_fungsi', $item->ujiFungsi && $item->ujiFungsi->target_tgl_uji_fungsi ? $item->ujiFungsi->target_tgl_uji_fungsi->format('d-m-Y') : null);
                $this->addColumnData($rowData, $item, 'tgl_uji_fungsi', $item->ujiFungsi && $item->ujiFungsi->tgl_uji_fungsi ? $item->ujiFungsi->tgl_uji_fungsi->format('d-m-Y') : null);
                $this->addColumnData($rowData, $item, 'tgl_pelatihan', $item->ujiFungsi && $item->ujiFungsi->tgl_pelatihan ? $item->ujiFungsi->tgl_pelatihan->format('d-m-Y') : null);

                $data->push($rowData);
                continue;
            }

            // Kemenkes role data (unchanged base, tapi biarkan tanggal tetap raw kalau ada)
            $data->push([
                $item->id,
                $item->district->regency->province->name,
                $item->district->regency->name,
                $item->district->name,
                $item->name,
                $item->alamat,
                $item->pic,
                $item->kepala,
                $item->no_hp,
                $item->no_hp_alternatif,
                $item->pic_dinkes_kab,
                $item->pic_dinkes_prov,
            ]);
        }

        return $data;
    }

    private function addColumnData(&$rowData, $item, $columnKey, $value)
    {
        if (in_array($columnKey, $this->additionalColumns)) {
            $rowData[] = $value instanceof \Carbon\Carbon ? $value : $value; // biarkan Carbon atau null/string
        }
    }
    public function headings(): array
    {
        if($this->roles == 2){
            return [
            'ID Puskesmas',
            'Provinsi',
            'Kabupaten / Kota',
            'Kecamatan',
            'Nama Puskesmas',
            'Alamat',
            'PIC Puskesmas',
            'Kepala Puskesmas',
            'No HP',
            'No HP Alternatif',
            'PIC Kabupaten / Kota',
            'PIC Dinas Kesehatan Provinsi',
            ];
        }else{
            $headers = [
                'ID Puskesmas',
                'Provinsi',
                'Kabupaten / Kota',
                'Kecamatan',
                'Nama Puskesmas',
            ];

            $columnHeaders = [
                'pic' => 'PIC Puskesmas',
                'no_hp' => 'No HP',
                'no_hp_alternatif' => 'No HP Alternatif',
                'kepala' => 'Kepala Puskesmas',
                'pic_dinkes_prov' => 'PIC Dinkes Provinsi',
                'pic_dinkes_kab' => 'PIC Dinkes Kabupaten/Kota',
                'tgl_pengiriman' => 'Tanggal Pengiriman',
                'eta' => 'ETA',
                'resi' => 'Nomor Resi',
                'serial_number' => 'Serial Number',
                'catatan' => 'Catatan',
                'tgl_diterima' => 'Tanggal Diterima',
                'nama_penerima' => 'Nama Penerima',
                'jabatan_penerima' => 'Jabatan Penerima',
                'instansi_penerima' => 'Instansi Penerima',
                'nomor_penerima' => 'Nomor Penerima',
                'tgl_instalasi' => 'Tanggal Instalasi',
                'target_tgl_uji_fungsi' => 'Target Tanggal Uji Fungsi',
                'tgl_uji_fungsi' => 'Tanggal Uji Fungsi',
                'tgl_pelatihan' => 'Tanggal Pelatihan',
            ];

            foreach ($this->additionalColumns as $column) {
                if (isset($columnHeaders[$column])) {
                    $headers[] = $columnHeaders[$column];
                }
            }

            return $headers;
        }
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $headings = $this->headings();
                $columnCount = count($headings);
                $highestColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCount);

                // Styling header
                $event->sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
                    'font' => [ 'bold' => true ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN ],
                    ],
                ]);

                $event->sheet->getDelegate()->setAutoFilter("A1:{$highestColumn}1");

                // Tentukan kolom tanggal aktual berdasarkan selected additionalColumns (roles != 2)
                if ($this->roles != 2) {
                    $dateHeadersMap = [
                        'ETA',
                        'Tanggal Pengiriman',
                        'Tanggal Diterima',
                        'Tanggal Instalasi',
                        'Target Tanggal Uji Fungsi',
                        'Tanggal Uji Fungsi',
                        'Tanggal Pelatihan',
                    ];

                    // Scan header dan format sebagai tanggal Excel
                    foreach ($headings as $index => $header) {
                        if (in_array($header, $dateHeadersMap)) {
                            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
                            // Format number untuk seluruh kolom data (mulai baris 2)
                            $event->sheet->getStyle("{$colLetter}2:{$colLetter}" . $event->sheet->getHighestRow())
                                ->getNumberFormat()->setFormatCode('dd-mm-yyyy');
                        }
                    }
                }
            }
        ];
    }
}
