@extends('adminlte::page')

@section('title', 'Pelaporan Insiden')

@section('content_header')
    <h1>Pelaporan Insiden</h1>
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content')
    <div class="card shadow-sm">
        <div class="card-header py-2 pr-1 d-flex align-items-center" style="background:#6f42c1;">
            <span class="section-title-bar text-white" style="font-size:.7rem;font-weight:600;letter-spacing:.5px;text-transform:uppercase;">Data Insiden</span>
        </div>
        <div class="card-body p-3">
            @if(auth()->user() && auth()->user()->role->role_name != 'puskesmas')
            <!-- Filter Section -->
            <div class="mb-3 p-3 border rounded" style="background:#f8f9fc;">
                <div class="form-row">
                    <div class="form-group col-md-2 mb-2">
                        <label class="small font-weight-bold mb-1">Provinsi</label>
                        <select id="filter-province" class="form-control form-control-sm">
                            <option value="">Semua</option>
                        </select>
                    </div>
                    <div class="form-group col-md-2 mb-2">
                        <label class="small font-weight-bold mb-1">Kabupaten</label>
                        <select id="filter-regency" class="form-control form-control-sm" disabled>
                            <option value="">Semua</option>
                        </select>
                    </div>
                    <div class="form-group col-md-2 mb-2">
                        <label class="small font-weight-bold mb-1">Kecamatan</label>
                        <select id="filter-district" class="form-control form-control-sm" disabled>
                            <option value="">Semua</option>
                        </select>
                    </div>
                    <div class="form-group col-md-2 mb-2">
                        <label class="small font-weight-bold mb-1">Status</label>
                        <select id="filter-status" class="form-control form-control-sm">
                            <option value="">Semua</option>
                        </select>
                    </div>
                    <div class="form-group col-md-2 mb-2">
                        <label class="small font-weight-bold mb-1">Kategori Insiden</label>
                        <select id="filter-kategori" class="form-control form-control-sm">
                            <option value="">Semua</option>
                        </select>
                    </div>
                    <div class="form-group col-md-2 mb-2 d-flex align-items-end">
                        <button id="btn-reset-filter" class="btn btn-secondary btn-sm btn-block"><i class="fas fa-undo mr-1"></i>Reset</button>
                    </div>
                </div>
            </div>
            @endif
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm" id="reported-incidents-table">
                    <thead>
                        <tr>
                            <th style="font-size: 11pt;">No.</th>
                            <th style="font-size: 11pt;">Provinsi</th>
                            <th style="font-size: 11pt;">Kabupaten</th>
                            <th style="font-size: 11pt;">Kecamatan</th>
                            <th style="font-size: 11pt;">Nama Puskesmas</th>
                            <th style="font-size: 11pt;">Tanggal Kejadian</th>
                            <th style="font-size: 11pt;">Insiden</th>
                            <th style="font-size: 11pt;">Tahapan</th>
                            <th style="font-size: 11pt;">Kategori Insiden</th>
                            <th style="font-size: 11pt;">Status</th>
                            <th class="text-center" style="font-size: 11pt;">Actions</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 10pt;">
                        <!-- Data will be populated here via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <!-- Toastr CSS for toast notifications -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        .section-title-bar{font-size:.7rem;font-weight:600;letter-spacing:.5px;text-transform:uppercase;}
        #reported-incidents-table {
            font-size: 0.85rem;
        }

        #reported-incidents-table thead th {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            font-weight: 600;
        }

        #reported-incidents-table tbody td {
            vertical-align: middle;
            border-color: #dee2e6;
        }

        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        /* Tahapan Badge Styles */
        .badge-tahapan-pengemasan {
            background: rgba(0, 136, 255, 0.15) !important; /* secondary tone */
            color: #0088ff !important;
        }

        .badge-tahapan-dalam-pengiriman {
            background: rgba(23, 162, 184, 0.15) !important; /* info tone */
            color: #117a8b !important;
        }

        .badge-tahapan-penerimaan {
            background: rgba(0, 123, 255, 0.15) !important; /* primary tone */
            color: #004085 !important;
        }

        .badge-tahapan-instalasi {
            background: rgba(255, 193, 7, 0.15) !important; /* warning tone */
            color: #b38301 !important;
        }

        .badge-tahapan-uji-fungsi {
            background: rgba(128, 0, 128, 0.15) !important; /* purple tone */
            color: #800080 !important;
        }

        .badge-tahapan-pelatihan-alat {
            background: rgba(52, 58, 64, 0.15) !important; /* dark tone */
            color: #343a40 !important;
        }

        .badge-tahapan-aspak {
            background: rgba(40, 167, 69, 0.15) !important; /* success tone */
            color: #1e7e34 !important;
        }

        .badge-tahapan-basto {
            background: rgba(220, 53, 69, 0.15) !important; /* danger tone */
            color: #bd2130 !important;
        }

        .badge-tahapan-unknown {
            background: rgba(108, 117, 125, 0.15) !important;
            color: #495057 !important;
        }

        /* Kategori Insiden Badge Styles */
        .badge-kategori-kematian {
            background: rgba(220, 53, 69, 0.15) !important;
            color: #bd2130 !important;
        }
        .badge-kategori-tindakan-kekerasan {
            background: rgba(255, 87, 34, 0.15) !important;
            color: #e64a19 !important;
        }
        .badge-kategori-pemindahan-tanpa-prosedur-yang-semestinya {
            background: rgba(255, 193, 7, 0.15) !important;
            color: #b38301 !important;
        }
        .badge-kategori-cedera-dengan-waktu-kerja-hilang {
            background: rgba(0, 123, 255, 0.15) !important;
            color: #004085 !important;
        }
        .badge-kategori-eksploitasi-dan-kekerasan-seksual-pelecehan-seksual {
            background: rgba(156, 39, 176, 0.15) !important;
            color: #6a1b9a !important;
        }
        .badge-kategori-pekerja-anak {
            background: rgba(255, 152, 0, 0.15) !important;
            color: #b36b00 !important;
        }
        .badge-kategori-pekerja-paksa {
            background: rgba(33, 150, 243, 0.15) !important;
            color: #0d47a1 !important;
        }
        .badge-kategori-dampak-tak-terduga-terhadap-sumber-daya-warisan-budaya {
            background: rgba(76, 175, 80, 0.15) !important;
            color: #1e7e34 !important;
        }
        .badge-kategori-dampak-tak-terduga-terhadap-keanekaragaman-hayati {
            background: rgba(0, 150, 136, 0.15) !important;
            color: #00695c !important;
        }
        .badge-kategori-wabah-penyakit {
            background: rgba(23, 162, 184, 0.15) !important;
            color: #117a8b !important;
        }
        .badge-kategori-kecelakaan-pencemaran-lingkungan {
            background: rgba(63, 81, 181, 0.15) !important;
            color: #283593 !important;
        }
        .badge-kategori-lainnya {
            background: rgba(108, 117, 125, 0.15) !important;
            color: #6c757d !important;
        }

    </style>
@stop

@section('js')
    <!-- Toastr JS for toast notifications -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>
        // Configure toastr options
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        $(document).ready(function() {
            // Initialize DataTable
            const table = $('#reported-incidents-table').DataTable({
                processing: true,
                serverSide: false,
                responsive: true,
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                order: [[5, 'desc']], // Sort by date descending
                language: {
                    processing: "Memuat data...",
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    },
                    emptyTable: "Tidak ada data insiden yang tersedia",
                    zeroRecords: "Tidak ada data yang cocok"
                },
                columns: [
                    { data: null, orderable: false, searchable: false, width: '5%' },
                    { data: 'province_name', name: 'province_name', width: '10%' },
                    { data: 'regency_name', name: 'regency_name', width: '10%' },
                    { data: 'district_name', name: 'district_name', width: '10%' },
                    { data: 'puskesmas_name', name: 'puskesmas_name', width: '15%' },
                    { data: 'tgl_kejadian', name: 'tgl_kejadian', width: '10%' },
                    { data: 'insiden', name: 'insiden', width: '20%' },
                    { data: 'tahapan', name: 'tahapan', width: '10%' },
                    { data: 'kategori_insiden', name: 'kategori_insiden', width: '12%' },
                    { data: 'status', name: 'status', width: '8%' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, width: '6%' }
                ],
                columnDefs: [
                    {
                        targets: 0,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        targets: 6, // Insiden column
                        render: function (data, type, row) {
                            if (data && data.length > 80) {
                                return `<div style="max-width:250px; white-space:normal;">${data.substring(0, 80)}...</div>`;
                            }
                            return `<div style="max-width:250px; white-space:normal;">${data || '-'}</div>`;
                        }
                    },
                    {
                        targets: 7, // Tahapan column
                        render: function (data, type, row) {
                            let badgeClass = 'badge-secondary';
                            const tahapanLower = (data || '').toLowerCase().replace(/[^a-z0-9]/g, '-');

                            switch (tahapanLower) {
                                case 'pengemasan':
                                    badgeClass = 'badge-tahapan-pengemasan';
                                    break;
                                case 'dalam-pengiriman':
                                    badgeClass = 'badge-tahapan-dalam-pengiriman';
                                    break;
                                case 'penerimaan':
                                    badgeClass = 'badge-tahapan-penerimaan';
                                    break;
                                case 'instalasi':
                                    badgeClass = 'badge-tahapan-instalasi';
                                    break;
                                case 'uji-fungsi':
                                    badgeClass = 'badge-tahapan-uji-fungsi';
                                    break;
                                case 'pelatihan-alat':
                                    badgeClass = 'badge-tahapan-pelatihan-alat';
                                    break;
                                case 'aspak':
                                    badgeClass = 'badge-tahapan-aspak';
                                    break;
                                case 'basto':
                                    badgeClass = 'badge-tahapan-basto';
                                    break;
                                default:
                                    badgeClass = 'badge-secondary';
                                    break;
                            }

                            return `<span class="badge badge-pill ${badgeClass}">${data || '-'}</span>`;
                        }
                    },
                    {
                        targets: 8, // Kategori Insiden column
                        render: function (data, type, row) {
                            let badgeClass = 'badge-secondary';
                            const kategoriLower = (data || '').toLowerCase().replace(/[^a-z0-9]/g, '-');

                            switch (kategoriLower) {
                                case 'kematian':
                                    badgeClass = 'badge-kategori-kematian';
                                    break;
                                case 'tindakan-kekerasan':
                                    badgeClass = 'badge-kategori-tindakan-kekerasan';
                                    break;
                                case 'pemindahan-tanpa-prosedur-yang-semestinya':
                                    badgeClass = 'badge-kategori-pemindahan-tanpa-prosedur-yang-semestinya';
                                    break;
                                case 'cedera-dengan-waktu-kerja-hilang':
                                    badgeClass = 'badge-kategori-cedera-dengan-waktu-kerja-hilang';
                                    break;
                                case 'eksploitasi-dan-kekerasan-seksual-pelecehan-seksual':
                                    badgeClass = 'badge-kategori-eksploitasi-dan-kekerasan-seksual-pelecehan-seksual';
                                    break;
                                case 'pekerja-anak':
                                    badgeClass = 'badge-kategori-pekerja-anak';
                                    break;
                                case 'pekerja-paksa':
                                    badgeClass = 'badge-kategori-pekerja-paksa';
                                    break;
                                case 'dampak-tak-terduga-terhadap-sumber-daya-warisan-budaya':
                                    badgeClass = 'badge-kategori-dampak-tak-terduga-terhadap-sumber-daya-warisan-budaya';
                                    break;
                                case 'dampak-tak-terduga-terhadap-keanekaragaman-hayati':
                                    badgeClass = 'badge-kategori-dampak-tak-terduga-terhadap-keanekaragaman-hayati';
                                    break;
                                case 'wabah-penyakit':
                                    badgeClass = 'badge-kategori-wabah-penyakit';
                                    break;
                                case 'kecelakaan-pencemaran-lingkungan':
                                    badgeClass = 'badge-kategori-kecelakaan-pencemaran-lingkungan';
                                    break;
                                case 'lainnya':
                                    badgeClass = 'badge-kategori-lainnya';
                                    break;
                                // Fallback for simple categories
                                default:
                                    badgeClass = 'badge-secondary';
                                    break;
                            }

                            return `<span class="badge badge-pill ${badgeClass}">${data || '-'}</span>`;
                        }
                    },
                    {
                        targets: 9, // Status column
                        render: function (data, type, row) {
                            let badgeClass = 'badge-secondary';
                            const statusLower = (data || '').toLowerCase();

                            switch (statusLower) {
                                case 'open':
                                case 'buka':
                                    badgeClass = 'badge-danger';
                                    break;
                                case 'in_progress':
                                case 'proses':
                                    badgeClass = 'badge-warning';
                                    break;
                                case 'closed':
                                case 'selesai':
                                    badgeClass = 'badge-success';
                                    break;
                            }

                            return `<span class="badge ${badgeClass}">${data || '-'}</span>`;
                        }
                    },
                    {
                        targets: 10, // Actions
                        render: function (data, type, row) {
                            const detailUrl = '{{ route("insiden.detail", ":id") }}'.replace(':id', row.id);
                            return `<div class="d-flex justify-content-center align-items-center">
                                        <a href="${detailUrl}" class="text-secondary" title="Lihat Detail">
                                            <i class="fas fa-search"></i>
                                        </a>
                                    </div>`;
                        }
                    }
                ],
                ajax: {
                    url: '{{ route('insiden.fetch-data') }}',
                    type: 'GET',
                    dataSrc: function(json) {
                        if (json.success) {
                            return json.data;
                        } else {
                            console.error('Error loading insiden data:', json.message);
                            return [];
                        }
                    },
                    error: function(xhr, error, code) {
                        console.error('AJAX Error:', error);
                        toastr.error('Gagal memuat data insiden');
                    }
                }
            });

            // Province -> Regency -> District cascading using existing API endpoints
            const provincesUrl = '{{ route('api-puskesmas.provinces') }}';
            const regenciesUrl = '{{ route('api-puskesmas.regencies') }}';
            const districtsUrl = '{{ route('api-puskesmas.districts') }}';

            function loadProvinces(){
                $.get(provincesUrl).done(r=>{
                    if(r.success){
                        const $p = $('#filter-province');
                        r.data.forEach(p=> $p.append(`<option value="${p.name}" data-id="${p.id}">${p.name}</option>`));
                    }
                });
            }
            function loadRegencies(provinceId){
                $('#filter-regency').prop('disabled', true).html('<option value="">Semua</option>');
                $('#filter-district').prop('disabled', true).html('<option value="">Semua</option>');
                if(!provinceId) return;
                $.get(regenciesUrl,{ province_id: provinceId }).done(r=>{
                    if(r.success){
                        const $r = $('#filter-regency');
                        r.data.forEach(it=> $r.append(`<option value="${it.name}" data-id="${it.id}">${it.name}</option>`));
                        $r.prop('disabled', false);
                    }
                });
            }
            function loadDistricts(regencyId){
                $('#filter-district').prop('disabled', true).html('<option value="">Semua</option>');
                if(!regencyId) return;
                $.get(districtsUrl,{ regency_id: regencyId }).done(r=>{
                    if(r.success){
                        const $d = $('#filter-district');
                        r.data.forEach(it=> $d.append(`<option value="${it.name}" data-id="${it.id}">${it.name}</option>`));
                        $d.prop('disabled', false);
                    }
                });
            }

            // Load master data for kategori and status filters
            function loadMasterData(){
                // Load status options
                $.ajax({
                    url: '{{ route("api.status-insiden") }}',
                    method: 'GET',
                    success: function(data) {
                        const $status = $('#filter-status');
                        data.forEach(function(item) {
                            $status.append(`<option value="${item.status}">${item.status}</option>`);
                        });
                    },
                    error: function() {
                        console.log('Failed to load status insiden');
                    }
                });

                // Load kategori options
                $.ajax({
                    url: '{{ route("api.kategori-insiden") }}',
                    method: 'GET',
                    success: function(data) {
                        const $kategori = $('#filter-kategori');
                        data.forEach(function(item) {
                            $kategori.append(`<option value="${item.kategori}">${item.kategori}</option>`);
                        });
                    },
                    error: function() {
                        console.log('Failed to load kategori insiden');
                    }
                });
            }

            // Initialize filters
            loadProvinces();
            loadMasterData();

            // Filter handlers
            $('#filter-province').on('change', function(){
                const selected = $(this).find(':selected').data('id');
                loadRegencies(selected);
                table.draw();
            });
            $('#filter-regency').on('change', function(){
                const selected = $(this).find(':selected').data('id');
                loadDistricts(selected);
                table.draw();
            });
            $('#filter-district, #filter-status, #filter-kategori').on('change keyup', function(){
                table.draw();
            });
            $('#btn-reset-filter').on('click', function(e){
                e.preventDefault();
                $('#filter-province').val('');
                $('#filter-regency').html('<option value="">Semua</option>').prop('disabled', true);
                $('#filter-district').html('<option value="">Semua</option>').prop('disabled', true);
                $('#filter-status').val('');
                $('#filter-kategori').val('');
                table.draw();
            });

            // Custom filtering plug-in
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex){
                if(settings.nTable.id !== 'reported-incidents-table') return true;
                const province = $('#filter-province').val();
                const regency = $('#filter-regency').val();
                const district = $('#filter-district').val();
                const status = $('#filter-status').val();
                const kategori = $('#filter-kategori').val();

                // Data columns mapping
                const rowProvince = data[1];
                const rowRegency = data[2];
                const rowDistrict = data[3];
                const rowStatus = data[9]; // Status column
                const rowKategori = data[8]; // Kategori column

                if(province && rowProvince !== province) return false;
                if(regency && rowRegency !== regency) return false;
                if(district && rowDistrict !== district) return false;
                if(status && rowStatus.toLowerCase() !== status.toLowerCase()) return false;
                if(kategori && rowKategori.toLowerCase() !== kategori.toLowerCase()) return false;
                return true;
            });



        });
    </script>
@stop
