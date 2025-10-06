@extends('adminlte::page')

@section('title', 'Daftar Revisi')

@section('content_header')
    <h1>Daftar Revisi</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header bg-danger">
            <h3 class="card-title">Daftar Dokumen yang Memerlukan Revisi</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" onclick="refreshTableData()">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
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
                <button type="button" class="btn btn-primary btn-sm mr-1" onclick="refreshTableData()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="resetFilters()">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
        </div>
            </div>
            <table class="table table-bordered table-striped table-sm" id="revisionsTable">
                <thead>
                    <tr>
                        <th style="font-size: 11pt;">No.</th>
                        <th style="font-size: 11pt;">Provinsi</th>
                        <th style="font-size: 11pt;">Kabupaten/Kota</th>
                        <th style="font-size: 11pt;">Kecamatan</th>
                        <th style="font-size: 11pt;">Nama Puskesmas</th>
                        <th style="font-size: 11pt;">Tanggal Revisi</th>
                        <th style="font-size: 11pt;">Dokumen Revisi</th>
                        <th class="text-center" style="font-size: 11pt;">Actions</th>
                    </tr>
                </thead>
                <tbody style="font-size: 10pt;">
                    <!-- Data will be populated here via AJAX -->
                </tbody>
            </table>
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
            font-size: 0.875rem;
        }
    </style>
@stop

@section('js')
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script>
    $(document).ready(function() {
        const fetchDataUrl = '{{ route('daftar-revisi.fetch-data') }}';
        const provincesUrl = '{{ route('api-puskesmas.provinces') }}';
        const regenciesUrl = '{{ route('api-puskesmas.regencies') }}';
        const districtsUrl = '{{ route('api-puskesmas.districts') }}';
        
        // Initialize DataTable
        const table = $('#revisionsTable').DataTable({
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
                { data: 'kabupaten', name: 'kabupaten', width: '15%' },
                { data: 'kecamatan', name: 'kecamatan', width: '15%' },
                { data: 'nama_puskesmas', name: 'nama_puskesmas', width: '20%' },
                { data: 'latest_created_at', name: 'latest_created_at', orderable: false, width: '12%' },
                { data: 'document_types', name: 'document_types', orderable: false, width: '18%' },
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
                    targets: 6, // Document Types
                    render: function (data, type, row) {
                        let badges = '';
                        const badgeClasses = ['badge-primary', 'badge-success', 'badge-info', 'badge-warning', 'badge-secondary', 'badge-dark', 'badge-danger'];
                        
                        row.document_types.forEach((docType, index) => {
                            let badgeClass = badgeClasses[index % badgeClasses.length];
                            const docTypeLower = docType.toLowerCase();
                            
                            // Special handling for common document types
                            switch (docTypeLower) {
                                case 'kalibrasi':
                                    badgeClass = 'badge-primary';
                                    break;
                                case 'bast':
                                case 'berita acara serah terima':
                                    badgeClass = 'badge-success';
                                    break;
                                case 'instalasi':
                                    badgeClass = 'badge-info';
                                    break;
                                case 'uji fungsi':
                                    badgeClass = 'badge-warning';
                                    break;
                                case 'pelatihan':
                                    badgeClass = 'badge-secondary';
                                    break;
                                case 'basto':
                                    badgeClass = 'badge-dark';
                                    break;
                                case 'aspak':
                                    badgeClass = 'badge-danger';
                                    break;
                            }
                            
                            badges += `<span class="badge ${badgeClass} mr-1 mb-1">${docType}</span>`;
                        });
                        
                        badges += `<br><small class="text-muted">${row.revision_count} revisi aktif</small>`;
                        return `<div style="max-width:300px; white-space:normal;">${badges}</div>`;
                    }
                },
                {
                    targets: 7, // Actions
                    render: function (data, type, row) {
                        const detailUrl = '{{ route('verification-request.detail', ':id') }}'.replace(':id', row.puskesmas_id);
                        return `<div class="d-flex justify-content-center align-items-center">
                                    <a href="${detailUrl}" class="text-secondary" title="Lihat Detail">
                                        <i class="fas fa-search"></i>
                                    </a>
                                </div>`;
                    }
                }
            ],
            order: [[5, 'desc']] // Sort by latest created date by default (most recent first)
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
        });        // Reset select helper
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
            $('#revisionsTable tbody').html('<tr><td colspan="8" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>');
            
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
                        console.log(`Loaded ${count} revision records`);
                    } else {
                        console.error('API returned error:', response.message || 'Unknown error');
                        table.clear().draw();
                    }
                })
                .fail(function(xhr, status, error) {
                    console.error('Failed to load table data:', error);
                    $('#revisionsTable tbody').html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data. Silakan coba lagi.</td></tr>');
                });
        }

        // Add refresh functionality
        window.refreshTableData = function() {
            loadTableData();
        };

        // Add reset filters functionality
        window.resetFilters = function() {
            // Reset province dropdown to default selection
            $('#provinsi').val('');
            
            // Enable and reset kabupaten dropdown with all options
            $('#kabupaten').prop('disabled', false);
            resetSelect($('#kabupaten'), 'Pilih Kabupaten');
            // Load all regencies for all provinces
            $.get(regenciesUrl).done(function(response) {
                if (response.success && response.data) {
                    const $select = $('#kabupaten');
                    resetSelect($select, 'Pilih Kabupaten');
                    response.data.forEach(function(regency) {
                        $select.append(`<option value="${regency.id}">${regency.name}</option>`);
                    });
                }
            });
            
            // Enable and reset kecamatan dropdown with all options
            $('#kecamatan').prop('disabled', false);
            resetSelect($('#kecamatan'), 'Pilih Kecamatan');
            // Load all districts for all regencies
            $.get(districtsUrl).done(function(response) {
                if (response.success && response.data) {
                    const $select = $('#kecamatan');
                    resetSelect($select, 'Pilih Kecamatan');
                    response.data.forEach(function(district) {
                        $select.append(`<option value="${district.id}">${district.name}</option>`);
                    });
                }
            });
            
            // Reload table data with no filters (showing all data)
            loadTableData();
        };
    });
    </script>
@stop
