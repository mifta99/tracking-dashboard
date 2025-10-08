@extends('adminlte::page')

@section('title', 'Pelaporan Keluhan')

@section('content_header')
    <h1>Pelaporan Keluhan</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header text-md font-weight-bold d-flex justify-content-between align-items-center" style="background-color: #ce8220; color: white;">
            <h3 class="card-title mb-0">Data Keluhan</h3>
        </div>

        <div class="card-body">
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
                            <option value="1">Open</option>
                            <option value="2">Progress</option>
                            <option value="3">Closed</option>
                        </select>
                    </div>
                    <div class="form-group col-md-2 mb-2">
                        <label class="small font-weight-bold mb-1">Tanggal</label>
                        <input type="month" id="filter-month" class="form-control form-control-sm" />
                    </div>
                    <div class="form-group col-md-2 mb-2 d-flex align-items-end">
                        <button id="btn-reset-filter" class="btn btn-secondary btn-sm btn-block"><i class="fas fa-undo mr-1"></i>Reset</button>
                    </div>
                </div>
            </div>
            @endif
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm" id="issues-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th style="font-size: 11pt;">No.</th>
                            <th style="font-size: 11pt;">Provinsi</th>
                            <th style="font-size: 11pt;">Kabupaten</th>
                            <th style="font-size: 11pt;">Kecamatan</th>
                            <th style="font-size: 11pt;">Nama Puskesmas</th>
                            <th style="font-size: 11pt;">Tanggal Dilaporkan</th>
                            <th style="font-size: 11pt;">Keluhan</th>
                            <th style="font-size: 11pt;">Kategori Keluhan</th>
                            <th style="font-size: 11pt;">Jumlah Downtime</th>
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
    <!-- Toastr CSS for toast notifications -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <style>
        /* DataTable styling to match detail page */
        #issues-table {
            font-size: 0.875rem;
        }

        #issues-table thead th {
            font-size: 11pt;
            font-weight: 600;
            background-color: #f8f9fa;
            white-space: nowrap;
        }

        #issues-table tbody td {
            vertical-align: middle;
            font-size: 10pt;
        }

        .dataTables_wrapper .dataTables_filter input { border-radius:4px; }
        .badge { font-size: 11px; }

        /* Modal Styles */
        .modal-header {
            background: linear-gradient(135deg, #ce8220, #f4a261);
            color: white;
        }

        .form-group label {
            font-weight: 600;
            color: #2c3e50;
        }

        .required::after {
            content: ' *';
            color: #e74c3c;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #ce8220, #f4a261);
            border: none;
            color: white;
            font-weight: 600;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #b8741c, #e8925a);
            color: white;
        }
    </style>
@stop

@section('js')
    <!-- Toastr JS for toast notifications -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function(){
            // Initialize DataTable
            const table = $('#issues-table').DataTable({
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
                    emptyTable: "Tidak ada data keluhan yang tersedia",
                    zeroRecords: "Tidak ada data yang cocok"
                },
                columns: [
                    { data: null, orderable: false, searchable: false, width: '5%' },
                    { data: 'province_name', name: 'province_name', width: '10%' },
                    { data: 'regency_name', name: 'regency_name', width: '10%' },
                    { data: 'district_name', name: 'district_name', width: '10%' },
                    { data: 'puskesmas_name', name: 'puskesmas_name', width: '15%' },
                    { data: 'tanggal_dilaporkan', name: 'tanggal_dilaporkan', width: '10%' },
                    { data: 'keluhan', name: 'keluhan', width: '20%' },
                    { data: 'kategori_keluhan', name: 'kategori_keluhan', width: '10%' },
                    { data: 'jumlah_downtime', name: 'jumlah_downtime', width: '8%' },
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
                        targets: 6, // Keluhan column
                        render: function (data, type, row) {
                            if (data && data.length > 100) {
                                return `<div style="max-width:300px; white-space:normal;">${data.substring(0, 100)}...</div>`;
                            }
                            return `<div style="max-width:300px; white-space:normal;">${data || '-'}</div>`;
                        }
                    },
                    {
                        targets: 7, // Kategori column
                        render: function (data, type, row) {
                            let badgeClass = 'badge-secondary';
                            const kategoriLower = (data || '').toLowerCase();

                            switch (kategoriLower) {
                                case 'rendah':
                                    badgeClass = 'badge-warning';
                                    break;
                                case 'sedang':
                                    badgeClass = 'badge-info';
                                    break;
                                case 'kritis':
                                    badgeClass = 'badge-danger';
                                    break;
                            }

                            return `<span class="badge ${badgeClass}">${data || '-'}</span>`;
                        }
                    },
                    {
                        targets: 9, // Status column
                        render: function (data, type, row) {
                            let badgeClass = 'badge-secondary';
                            const statusLower = (data || '').toLowerCase();

                            switch (statusLower) {
                                case 'baru':
                                    badgeClass = 'badge-warning';
                                    break;
                                case 'proses':
                                    badgeClass = 'badge-info';
                                    break;
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
                            const detailUrl = '{{ route("raised-issue.detail", ":id") }}'.replace(':id', row.id);
                            return `<div class="d-flex justify-content-center align-items-center">
                                        <a href="${detailUrl}" class="text-secondary" title="Lihat Detail">
                                            <i class="fas fa-search"></i>
                                        </a>
                                    </div>`;
                        }
                    }
                ],
                ajax: {
                    url: '{{ route('keluhan.fetch-data') }}',
                    type: 'GET',
                    dataSrc: function(json) {
                        if (json.success) {
                            return json.data;
                        } else {
                            console.error('Error loading keluhan data:', json.message);
                            return [];
                        }
                    },
                    error: function(xhr, error, code) {
                        console.error('AJAX Error:', error);
                        toastr.error('Gagal memuat data keluhan');
                    }
                }
            });

            // Province -> Regency -> District cascading using existing API endpoints (assumes already created)
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

            loadProvinces();

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
            $('#filter-district, #filter-status, #filter-month').on('change keyup', function(){
                table.draw();
            });
            $('#btn-reset-filter').on('click', function(e){
                e.preventDefault();
                $('#filter-province').val('');
                $('#filter-regency').html('<option value="">Semua</option>').prop('disabled', true);
                $('#filter-district').html('<option value="">Semua</option>').prop('disabled', true);
                $('#filter-status').val('');
                $('#filter-month').val('');
                table.draw();
            });

            // Custom filtering plug-in
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex){
                if(settings.nTable.id !== 'issues-table') return true;
                const province = $('#filter-province').val();
                const regency = $('#filter-regency').val();
                const district = $('#filter-district').val();
                const status = $('#filter-status').val();
                const month = $('#filter-month').val(); // format YYYY-MM

                // Data columns mapping (after hiding region columns they still exist)
                const rowProvince = data[1];
                const rowRegency = data[2];
                const rowDistrict = data[3];
                const rowStatusHtml = data[8];
                // Extract status id from original DOM row attribute if needed
                const rowNode = table.row(dataIndex).node();
                const rowStatusId = $(rowNode).data('status') ? String($(rowNode).data('status')) : '';
                const dateCell = $(rowNode).find('td[data-date]').data('date'); // YYYY-MM

                if(province && rowProvince !== province) return false;
                if(regency && rowRegency !== regency) return false;
                if(district && rowDistrict !== district) return false;
                if(status && rowStatusId !== status) return false;
                if(month && dateCell !== month) return false;
                return true;
            });

            // Redraw after adding custom search
            table.draw();


        });
    </script>
@stop
