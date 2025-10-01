@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1 style="font-size: 24px;">Dashboard Tracking Project T-Piece</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
                        @if (session('status'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{ session('status') }}
                </div>
            @endif
            <div class="bg-primary text-white p-1 rounded ps-1 font-weight-bold mb-3">
                Distribusi Jumlah Alat Kesehatan
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card" style="background-color: #09c0d8; height: 80px;">
                        <div class="card-body d-flex justify-content-between align-items-center position-relative h-100">
                            <div>
                                <h4 class="text-white mb-1">Card 1</h4>
                                <p class="text-white mb-0">Content for card 1</p>
                            </div>
                            <div class="text-secondary position-absolute" style="font-size: 3rem; right: 15px; top: 50%; transform: translateY(-50%); opacity: 0.25;">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card" style="background-color: #30db58; height: 80px;">
                        <div class="card-body d-flex justify-content-between align-items-center position-relative h-100">
                            <div>
                                <h4 class="text-white mb-1">Jumlah Puskesmas</h4>
                                <p class="text-white mb-0">{{ $countPuskesmas }}</p>
                            </div>
                            <div class="text-secondary position-absolute" style="font-size: 3rem; right: 15px; top: 50%; transform: translateY(-50%); opacity: 0.25;">
                                <i class="fas fa-hospital"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card" style="background-color: #f7c531; height: 80px;">
                        <div class="card-body d-flex justify-content-between align-items-center position-relative h-100">
                            <div>
                                <h4 class="text-white mb-1">Card 3</h4>
                                <p class="text-white mb-0">Content for card 3</p>
                            </div>
                            <div class="text-secondary position-absolute" style="font-size: 3rem; right: 15px; top: 50%; transform: translateY(-50%); opacity: 0.25;">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card" style="background-color: #e02b3d; height: 80px;">
                        <div class="card-body d-flex justify-content-between align-items-center position-relative h-100">
                            <div>
                                <h4 class="text-white mb-1">Card 4</h4>
                                <p class="text-white mb-0">Content for card 4</p>
                            </div>
                            <div class="text-secondary position-absolute" style="font-size: 3rem; right: 15px; top: 50%; transform: translateY(-50%); opacity: 0.25;">
                                <i class="fas fa-cog"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            Tracking Summary
                        </div>
                        <div class="card-body">
                            <canvas id="pieChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            Monthly Issue Report
                        </div>
                        <div class="card-body">
                            <canvas id="barChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-primary text-white p-1 rounded ps-1 font-weight-bold mb-3">
               Data Pengiriman
            </div>

            <div class="row mb-2">
                <div class="col-md-2">
                    <label class="small mb-1">Provinsi</label>
                    <select class="form-control form-control-sm" id="provinsi" name="provinsi">
                        <option value="">Pilih Provinsi</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small mb-1">Kabupaten/Kota</label>
                    <select class="form-control form-control-sm" id="kabupaten" name="kabupaten">
                        <option value="">Pilih Kabupaten</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small mb-1">Kecamatan</label>
                    <select class="form-control form-control-sm" id="kecamatan" name="kecamatan">
                        <option value="">Pilih Kecamatan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small mb-1">&nbsp;</label>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm" onclick="refreshTableData()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Data Puskesmas & Status Pengiriman</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" onclick="refreshTableData()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped table-sm" id="reported-incidents-table">
                        <thead>
                            <tr>
                                <th style="font-size: 11pt;">No.</th>
                                <th style="font-size: 11pt;">Provinsi</th>
                                <th style="font-size: 11pt;">Kabupaten/Kota</th>
                                <th style="font-size: 11pt;">Kecamatan</th>
                                <th style="font-size: 11pt;">Nama Puskesmas</th>
                                <th style="font-size: 11pt;">Tanggal Pengiriman</th>
                                <th style="font-size: 11pt;">Status</th>
                                <th class="text-center" style="font-size: 11pt;">Actions</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 10pt;">
                            <!-- Data will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <style>
        .table-responsive {
            font-size: 0.875rem;
        }
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        .table-sm th, .table-sm td {
            padding: 0.3rem;
        }
    </style>
@stop

@section('js')
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // API endpoints
        const fetchDataUrl = '{{ route('api-puskesmas.fetch-data') }}';
        const provincesUrl = '{{ route('api-puskesmas.provinces') }}';
        const regenciesUrl = '{{ route('api-puskesmas.regencies') }}';
        const districtsUrl = '{{ route('api-puskesmas.districts') }}';
        
        // Initialize DataTable
        const table = $('#reported-incidents-table').DataTable({
            processing: true,
            serverSide: false,
            responsive: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
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
                emptyTable: "Tidak ada data yang tersedia",
                zeroRecords: "Tidak ada data yang cocok"
            },
            columns: [
                { data: null, orderable: false, searchable: false, width: '5%' },
                { data: 'provinsi', name: 'provinsi', width: '15%' },
                { data: 'kabupaten_kota', name: 'kabupaten_kota', width: '15%' },
                { data: 'kecamatan', name: 'kecamatan', width: '15%' },
                { data: 'name', name: 'name', width: '20%' },
                { data: 'tanggal_pengiriman', name: 'tanggal_pengiriman', orderable: false, width: '12%' },
                { data: 'status', name: 'status', orderable: false, width: '10%' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, width: '8%' }
            ],
            columnDefs: [
                {
                    targets: 0,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    targets: 5, // Tanggal Pengiriman
                    render: function (data, type, row) {
                        if (row.pengiriman && row.pengiriman.tgl_pengiriman) {
                            const date = new Date(row.pengiriman.tgl_pengiriman);
                            return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                        }
                        return '<span class="text-muted">-</span>';
                    }
                },
                {
                    targets: 6, // Status
                    render: function (data, type, row) {
                        if (row.pengiriman) {
                            if (row.pengiriman.tgl_diterima) {
                                return '<span class="badge badge-success badge-sm">Diterima</span>';
                            } else if (row.pengiriman.tgl_pengiriman) {
                                return '<span class="badge badge-warning badge-sm">Dalam Pengiriman</span>';
                            } else {
                                return '<span class="badge badge-info badge-sm">Siap Kirim</span>';
                            }
                        }
                        return '<span class="badge badge-secondary badge-sm">Belum Diproses</span>';
                    }
                },
                {
                    targets: 7, // Actions
                    render: function (data, type, row) {
                        const detailUrl = '{{ route('verification-request.show', ':id') }}'.replace(':id', row.id);
                        return `<div class="d-flex justify-content-center align-items-center">
                                    <a href="${detailUrl}" class="text-secondary" title="Lihat Detail">
                                        <i class="fas fa-search"></i>
                                    </a>
                                </div>`;
                    }
                }
            ],
            order: [[4, 'asc']] // Sort by name by default
        });
        
        // Load provinces on page load
        loadProvinces();
        
        // Load initial data
        loadTableData();
        
        // Province change handler
        $('#provinsi').on('change', function() {
            const provinceId = $(this).val();
            resetSelect($('#kabupaten'), 'Pilih Kabupaten');
            resetSelect($('#kecamatan'), 'Pilih Kecamatan');
            
            if (provinceId) {
                loadRegencies(provinceId);
            }
            loadTableData();
        });
        
        // Regency change handler
        $('#kabupaten').on('change', function() {
            const regencyId = $(this).val();
            resetSelect($('#kecamatan'), 'Pilih Kecamatan');
            
            if (regencyId) {
                loadDistricts(regencyId);
            }
            loadTableData();
        });
        
        // District change handler
        $('#kecamatan').on('change', function() {
            loadTableData();
        });
        
        // Reset select helper
        function resetSelect($select, placeholder) {
            $select.empty().append(`<option value="">${placeholder}</option>`);
        }
        
        // Load provinces function
        function loadProvinces() {
            $.get(provincesUrl)
                .done(function(response) {
                    if (response.success && response.data) {
                        const $select = $('#provinsi');
                        resetSelect($select, 'Pilih Provinsi');
                        response.data.forEach(function(province) {
                            $select.append(`<option value="${province.id}">${province.name}</option>`);
                        });
                    }
                })
                .fail(function(xhr, status, error) {
                    console.error('Failed to load provinces:', error);
                });
        }
        
        // Load regencies function
        function loadRegencies(provinceId) {
            $.get(regenciesUrl, { province_id: provinceId })
                .done(function(response) {
                    if (response.success && response.data) {
                        const $select = $('#kabupaten');
                        resetSelect($select, 'Pilih Kabupaten');
                        response.data.forEach(function(regency) {
                            $select.append(`<option value="${regency.id}">${regency.name}</option>`);
                        });
                    }
                })
                .fail(function(xhr, status, error) {
                    console.error('Failed to load regencies:', error);
                });
        }
        
        // Load districts function
        function loadDistricts(regencyId) {
            $.get(districtsUrl, { regency_id: regencyId })
                .done(function(response) {
                    if (response.success && response.data) {
                        const $select = $('#kecamatan');
                        resetSelect($select, 'Pilih Kecamatan');
                        response.data.forEach(function(district) {
                            $select.append(`<option value="${district.id}">${district.name}</option>`);
                        });
                    }
                })
                .fail(function(xhr, status, error) {
                    console.error('Failed to load districts:', error);
                });
        }
        
        // Load table data function
        function loadTableData() {
            const filters = {};
            
            // Only add filters if they have values
            const provinceId = $('#provinsi').val();
            const regencyId = $('#kabupaten').val();
            const districtId = $('#kecamatan').val();
            
            if (provinceId) filters.province_id = provinceId;
            if (regencyId) filters.regency_id = regencyId;
            if (districtId) filters.district_id = districtId;
            
            // Show loading message
            $('#reported-incidents-table tbody').html('<tr><td colspan="8" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>');
            
            $.get(fetchDataUrl, filters)
                .done(function(response) {
                    if (response.success) {
                        table.clear();
                        if (response.data && response.data.length > 0) {
                            table.rows.add(response.data);
                        }
                        table.draw();
                        
                        // Update info
                        const count = response.data ? response.data.length : 0;
                        console.log(`Loaded ${count} puskesmas records`);
                    } else {
                        console.error('API returned error:', response.message || 'Unknown error');
                        table.clear().draw();
                    }
                })
                .fail(function(xhr, status, error) {
                    console.error('Failed to load table data:', error);
                    $('#reported-incidents-table tbody').html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data. Silakan coba lagi.</td></tr>');
                });
        
        // Add refresh functionality
        window.refreshTableData = function() {
            loadTableData();
        };
        }
    });
    
    </script>
@stop
