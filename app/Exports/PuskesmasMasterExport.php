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
    protected $additionalColumns;

    public function __construct(int $roles, array $additionalColumns = [])
    {
        $this->roles = $roles;
        $this->additionalColumns = $additionalColumns;
    }
    public function collection()
    {
        $puskesmas = \App\Models\Puskesmas::with(['district.regency.province', 'pengiriman.equipment', 'ujiFungsi', 'document'])->get();
        $data = collect();

        foreach ($puskesmas as $item) {
            if($this->roles != 2){
                // Build row data dynamically based on selected columns
                $rowData = [
                    $item->district->regency->province->name,
                    $item->district->regency->name,
                    $item->district->name,
                    $item->name,
                ];

                // Add additional columns based on selection
                $this->addColumnData($rowData, $item, 'pic', $item->pic);
                $this->addColumnData($rowData, $item, 'kepala', $item->kepala);
                $this->addColumnData($rowData, $item, 'pic_dinkes_prov', $item->pic_dinkes_prov);
                $this->addColumnData($rowData, $item, 'pic_dinkes_kab', $item->pic_dinkes_kab);
                
                // Delivery Information
                $this->addColumnData($rowData, $item, 'tgl_pengiriman', 
                    $item->pengiriman && $item->pengiriman->tgl_pengiriman ? $item->pengiriman->tgl_pengiriman->format('Y-m-d') : '');
                $this->addColumnData($rowData, $item, 'eta', $item->pengiriman->eta ?? '');
                $this->addColumnData($rowData, $item, 'resi', $item->pengiriman->resi ?? '');
                $this->addColumnData($rowData, $item, 'serial_number', 
                    $item->pengiriman && $item->pengiriman->equipment ? $item->pengiriman->equipment->serial_number : '');
                $this->addColumnData($rowData, $item, 'target_tgl', 
                    $item->pengiriman && $item->pengiriman->target_tgl ? $item->pengiriman->target_tgl->format('Y-m-d') : '');
                $this->addColumnData($rowData, $item, 'catatan', $item->pengiriman->catatan ?? '');
                $this->addColumnData($rowData, $item, 'tgl_diterima', 
                    $item->pengiriman && $item->pengiriman->tgl_diterima ? $item->pengiriman->tgl_diterima->format('Y-m-d') : '');
                $this->addColumnData($rowData, $item, 'nama_penerima', $item->pengiriman->nama_penerima ?? '');
                $this->addColumnData($rowData, $item, 'jabatan_penerima', $item->pengiriman->jabatan_penerima ?? '');
                $this->addColumnData($rowData, $item, 'instansi_penerima', $item->pengiriman->instansi_penerima ?? '');
                $this->addColumnData($rowData, $item, 'nomor_penerima', $item->pengiriman->nomor_penerima ?? '');
                
                // Testing & Installation
                $this->addColumnData($rowData, $item, 'tgl_instalasi', 
                    $item->ujiFungsi && $item->ujiFungsi->tgl_instalasi ? $item->ujiFungsi->tgl_instalasi->format('Y-m-d') : '');
                $this->addColumnData($rowData, $item, 'target_tgl_uji_fungsi', 
                    $item->ujiFungsi && $item->ujiFungsi->target_tgl_uji_fungsi ? $item->ujiFungsi->target_tgl_uji_fungsi->format('Y-m-d') : '');
                $this->addColumnData($rowData, $item, 'tgl_uji_fungsi', 
                    $item->ujiFungsi && $item->ujiFungsi->tgl_uji_fungsi ? $item->ujiFungsi->tgl_uji_fungsi->format('Y-m-d') : '');
                $this->addColumnData($rowData, $item, 'tgl_pelatihan', 
                    $item->ujiFungsi && $item->ujiFungsi->tgl_pelatihan ? $item->ujiFungsi->tgl_pelatihan->format('Y-m-d') : '');
                
                // Document Status
                $this->addColumnData($rowData, $item, 'tahapan_id', $item->pengiriman->tahapan_id ?? '');
                $this->addColumnData($rowData, $item, 'verif_kemenkes_pengiriman', 
                    $item->pengiriman && $item->pengiriman->verif_kemenkes ? 'Ya' : 'Tidak');
                $this->addColumnData($rowData, $item, 'verif_kemenkes_uji_fungsi', 
                    $item->ujiFungsi && $item->ujiFungsi->verif_kemenkes ? 'Ya' : 'Tidak');
                $this->addColumnData($rowData, $item, 'verif_kemenkes_dokumen', 
                    $item->document && $item->document->verif_kemenkes ? 'Ya' : 'Tidak');

                $data->push($rowData);
                continue;
            }
            
            // Kemenkes role data (unchanged)
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

    private function addColumnData(&$rowData, $item, $columnKey, $value)
    {
        if (in_array($columnKey, $this->additionalColumns)) {
            $rowData[] = $value;
        }
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
            // Base columns for Endo role
            $headers = [
                'Provinsi',
                'Kabupaten / Kota',
                'Kecamatan',
                'Nama Puskesmas',
            ];

            // Column mapping for additional headers
            $columnHeaders = [
                'pic' => 'PIC Puskesmas',
                'kepala' => 'Kepala Puskesmas',
                'pic_dinkes_prov' => 'PIC Dinkes Provinsi',
                'pic_dinkes_kab' => 'PIC Dinkes Kabupaten/Kota',
                'tgl_pengiriman' => 'Tanggal Pengiriman',
                'eta' => 'ETA (Hari)',
                'resi' => 'Nomor Resi',
                'serial_number' => 'Serial Number',
                'target_tgl' => 'Target Tanggal Diterima',
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
                'tahapan_id' => 'Status Tahapan',
                'verif_kemenkes_pengiriman' => 'Verifikasi Kemenkes - Pengiriman',
                'verif_kemenkes_uji_fungsi' => 'Verifikasi Kemenkes - Uji Fungsi',
                'verif_kemenkes_dokumen' => 'Verifikasi Kemenkes - Dokumen',
            ];

            // Add selected additional headers
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
