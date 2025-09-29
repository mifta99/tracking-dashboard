@extends('adminlte::page')

@section('title', 'Master Puskesmas')

@section('content_header')
    <h1>Master Puskesmas</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Data Puskesmas</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal">
                <i class="fas fa-plus"></i> Tambah Puskesmas
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-filter"></i> Filter Data Puskesmas</h5>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="provinceSelect" class="form-label">Provinsi:</label>
                                <select id="provinceSelect" class="">
                                    <option value="">Semua Provinsi</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="regencySelect" class="form-label">Kabupaten/Kota:</label>
                                <select id="regencySelect" class="" disabled>
                                    <option value="">Pilih Provinsi Dulu</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="districtSelect" class="form-label">Kecamatan:</label>
                                <select id="districtSelect" class="" disabled>
                                    <option value="">Pilih Kabupaten Dulu</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="btn-group w-100">
                                    <button type="button" id="filterBtn" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Cari Data
                                    </button>
                                    <button type="button" id="resetBtn" class="btn btn-secondary" title="Reset Filter">
                                        <i class="fas fa-refresh"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="puskesmasTable" class="table table-bordered excel-table" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th class="excel-header">No</th>
                        <th class="excel-header">Nama Puskesmas</th>
                        <th class="excel-header">Provinsi</th>
                        <th class="excel-header">Kabupaten</th>
                        <th class="excel-header">Kecamatan</th>
                        <th class="excel-header">PIC Puskesmas (Petugas ASPAK)</th>
                        <th class="excel-header">Kepala Puskesmas</th>
                        <th class="excel-header">PIC Dinas Kesehatan Provinsi</th>
                        <th class="excel-header">PIC ADINKES</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $item)
                        <tr>
                            <td class="excel-cell text-center">{{ $loop->iteration }}</td>
                            <td class="excel-cell editable" data-field="name" contenteditable="false">{{ $item->name ?? '-' }}</td>
                            <td class="excel-cell">{{ $item->district->regency->province->name ?? '-' }}</td>
                            <td class="excel-cell">{{ $item->district->regency->name ?? '-' }}</td>
                            <td class="excel-cell">{{ $item->district->name ?? '-' }}</td>
                            <td class="excel-cell editable" data-field="pic" contenteditable="false">{{ $item->pic ?? '-' }}</td>
                            <td class="excel-cell editable" data-field="kepala" contenteditable="false">{{ $item->kepala ?? '-' }}</td>
                            <td class="excel-cell editable" data-field="pic_dinkes_prov" contenteditable="false">{{ $item->pic_dinkes_prov ?? '-' }}</td>
                            <td class="excel-cell editable" data-field="pic_dinkes_kab" contenteditable="false">{{ $item->pic_dinkes_kab ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="excel-cell text-center">-</td>
                            <td class="excel-cell text-center">-</td>
                            <td class="excel-cell text-center">-</td>
                            <td class="excel-cell text-center">-</td>
                            <td class="excel-cell text-center">-</td>
                            <td class="excel-cell text-center">-</td>
                            <td class="excel-cell text-center">-</td>
                            <td class="excel-cell text-center">-</td>
                            <td class="excel-cell text-center">
                                <div class="alert alert-info mb-0" style="font-size: 0.8rem; padding: 0.25rem;">
                                    <i class="fas fa-info-circle"></i> No data
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('css')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

<!-- Tom Select CSS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">

<style>
.excel-table {
    border-collapse: collapse;
    background: white;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.excel-header {
    background: linear-gradient(180deg, #f0f0f0 0%, #e0e0e0 100%);
    border: 1px solid #c0c0c0;
    padding: 8px 12px;
    text-align: center;
    font-weight: 600;
    color: #333;
    font-size: 11px;
}

.excel-cell {
    border: 1px solid #d4d4d4;
    padding: 6px 8px;
    background: white;
    font-size: 11px;
    line-height: 1.2;
    min-height: 20px;
    vertical-align: middle;
    transition: background-color 0.1s ease;
}

.excel-cell:hover {
    background-color: #f5f5f5;
}

.excel-cell.editing {
    background-color: #fff;
    border: 2px solid #4285f4;
    outline: none;
    box-shadow: 0 0 5px rgba(66, 133, 244, 0.3);
}

.excel-cell.selected {
    background-color: #e3f2fd;
    border: 2px solid #2196f3;
}

.excel-table tbody tr:nth-child(even) {
    background-color: #fafafa;
}

.excel-table tbody tr:hover {
    background-color: #f0f8ff;
}

/* DataTables custom styling */
.dataTables_wrapper .dataTables_length select {
    padding: 4px;
    margin: 0 5px;
}

.dataTables_wrapper .dataTables_filter input {
    margin-left: 5px;
    padding: 4px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.dataTables_info {
    font-size: 12px;
}

.dataTables_paginate .paginate_button {
    padding: 4px 8px !important;
    margin: 2px;
    font-size: 12px;
}

/* Tom Select Styling */
.ts-wrapper {
    position: relative;
}

.ts-control {
    border: 1px solid #ced4da !important;
    border-radius: 0.375rem !important;
    padding: 0.375rem 0.75rem !important;
    font-size: 0.875rem !important;
    min-height: calc(1.5em + 0.75rem + 2px) !important;
}

.ts-control.focus {
    border-color: #80bdff !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
}

.ts-dropdown {
    border: 1px solid #ced4da !important;
    border-radius: 0.375rem !important;
    z-index: 1060 !important;
}

/* Style untuk opsi "Semua" */
.ts-dropdown .option[data-value=""] {
    background-color: #e3f2fd !important;
    font-weight: bold !important;
    border-bottom: 1px solid #ddd !important;
    color: #1976d2 !important;
}

.ts-dropdown .option[data-value=""]:hover {
    background-color: #bbdefb !important;
}

/* Filter card styling */
.card-outline.card-primary {
    border-top: 3px solid #007bff;
}

/* Empty data styling */
.excel-cell .alert {
    font-size: 0.9rem;
    padding: 0.5rem;
    margin: 0;
}

.excel-cell .alert-info {
    background-color: #e3f2fd;
    border-color: #bbdefb;
    color: #0d47a1;
}

/* DataTables no data message */
.dataTables_empty {
    text-align: center !important;
    font-style: italic;
    color: #6c757d;
    padding: 2rem !important;
}
</style>
@stop

@section('js')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

<!-- Tom Select JS -->
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>
$(document).ready(function() {
    let selectedCell = null;
    let editingCell = null;
    let dataTable;
    let provinceSelect, regencySelect, districtSelect;

    // Check table structure first
    console.log('Table rows on load:', $('#puskesmasTable tbody tr').length);
    console.log('Table columns:', $('#puskesmasTable thead th').length);
    
    // Check if PHP data was passed to view
    const phpData = @json($data ?? []);
    console.log('PHP data from controller:', phpData.length, 'items');
    console.log('Sample PHP data:', phpData.slice(0, 2));
    
    // Initialize Tom Select first
    initializeTomSelect();

    // Initialize DataTable with delay to ensure DOM is ready
    setTimeout(function() {
        initializeDataTable();
    }, 200);

    // Tom Select Initialization
    function initializeTomSelect() {
        console.log('Initializing Tom Select...');

        // Province Select
        provinceSelect = new TomSelect('#provinceSelect', {
            valueField: 'id',
            labelField: 'name',
            searchField: 'name',
            placeholder: 'Pilih Provinsi...',
            preload: true,
            load: function(query, callback) {
                if (!query.length) {
                    fetchProvinces(callback);
                }
                callback();
            },
            onChange: function(value) {
                console.log('Province changed:', value);
                if (value) {
                    loadRegencies(value);
                    clearDistricts();
                } else {
                    clearRegencies();
                    clearDistricts();
                }
            }
        });

        // Regency Select
        regencySelect = new TomSelect('#regencySelect', {
            valueField: 'id',
            labelField: 'name',
            searchField: 'name',
            placeholder: 'Pilih Kabupaten/Kota...',
            onChange: function(value) {
                console.log('Regency changed:', value);
                if (value) {
                    loadDistricts(value);
                } else {
                    clearDistricts();
                }
            }
        });

        // District Select
        districtSelect = new TomSelect('#districtSelect', {
            valueField: 'id',
            labelField: 'name',
            searchField: 'name',
            placeholder: 'Pilih Kecamatan...'
        });

        console.log('Tom Select instances created successfully');
        
        // Test API connectivity first
        $.ajax({
            url: '{{ route("api-puskesmas.test") }}',
            method: 'GET',
            success: function(response) {
                console.log('API test successful:', response);
            },
            error: function(xhr) {
                console.error('API test failed:', xhr.status, xhr.responseText);
            }
        });
        
        // Load initial provinces with delay
        setTimeout(function() {
            fetchProvinces(function(data) {
                console.log('Adding provinces to select:', data.length);
                provinceSelect.addOptions(data);
            });
        }, 100);
    }

    // Fetch Functions
    function fetchProvinces(callback) {
        $.ajax({
            url: '{{ route("api-puskesmas.provinces") }}',
            method: 'GET',
            success: function(response) {
                console.log('Provinces response:', response);
                if (response && response.data) {
                    console.log('Provinces loaded:', response.data.length);
                    callback(response.data);
                } else {
                    console.error('Invalid provinces response format');
                    callback([]);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching provinces:', {
                    status: xhr.status,
                    responseText: xhr.responseText,
                    error: error
                });
                callback([]);
            }
        });
    }

    function loadRegencies(provinceId) {
        console.log('Loading regencies for province:', provinceId);
        
        regencySelect.clearOptions();
        regencySelect.addOption({id: '', name: 'Semua Kabupaten/Kota'});
        regencySelect.disable();

        $.ajax({
            url: '{{ route("api-puskesmas.regencies") }}',
            method: 'GET',
            data: { province_id: provinceId },
            success: function(response) {
                console.log('Regencies loaded:', response.data.length);
                regencySelect.addOptions(response.data);
                regencySelect.enable();
            },
            error: function(xhr) {
                console.error('Error fetching regencies:', xhr.responseText);
                regencySelect.enable();
            }
        });
    }

    function loadDistricts(regencyId) {
        console.log('Loading districts for regency:', regencyId);
        
        districtSelect.clearOptions();
        districtSelect.addOption({id: '', name: 'Semua Kecamatan'});
        
        if (!regencyId) {
            districtSelect.enable();
            return;
        }

        districtSelect.disable();

        $.ajax({
            url: '{{ route("api-puskesmas.districts") }}',
            method: 'GET',
            data: { regency_id: regencyId },
            success: function(response) {
                console.log('Districts loaded:', response.data.length);
                districtSelect.addOptions(response.data);
                districtSelect.enable();
            },
            error: function(xhr) {
                console.error('Error fetching districts:', xhr.responseText);
                districtSelect.enable();
            }
        });
    }

    function clearRegencies() {
        regencySelect.clear();
        regencySelect.clearOptions();
        regencySelect.addOption({id: '', name: 'Pilih Provinsi Dulu'});
        regencySelect.disable();
    }

    function clearDistricts() {
        districtSelect.clear();
        districtSelect.clearOptions();
        districtSelect.addOption({id: '', name: 'Pilih Kabupaten Dulu'});
        districtSelect.disable();
    }

    // Filter Functions
    $('#filterBtn').on('click', function() {
        filterData();
    });

    $('#resetBtn').on('click', function() {
        resetFilters();
    });

    function filterData() {
        const provinceId = provinceSelect.getValue();
        const regencyId = regencySelect.getValue();
        const districtId = districtSelect.getValue();

        // Show loading
        $('#filterBtn').html('<i class="fas fa-spinner fa-spin"></i> Mencari...');
        $('#filterBtn').prop('disabled', true);

        // Remove existing filter info
        $('#filterInfo').remove();

        console.log('Filtering with:', { provinceId, regencyId, districtId });

        $.ajax({
            url: '{{ route("api-puskesmas.fetch-data") }}',
            method: 'GET',
            data: {
                province_id: provinceId || null,
                regency_id: regencyId || null,
                district_id: districtId || null
            },
            success: function(response) {
                console.log('Raw response:', response);
                
                if (response && response.data) {
                    console.log('Filtered data received:', response.data.length);
                    updateTable(response.data);
                    
                    // Get text from TomSelect options safely
                    const provinceText = provinceId ? getOptionText(provinceSelect, provinceId) : '';
                    const regencyText = regencyId ? getOptionText(regencySelect, regencyId) : '';
                    const districtText = districtId ? getOptionText(districtSelect, districtId) : '';

                    showFilterInfo(provinceText, regencyText, districtText);
                } else {
                    console.error('Invalid response format:', response);
                    updateTable([]);
                }
            },
            error: function(xhr) {
                console.error('Error filtering data:', xhr.responseText);
                alert('Terjadi kesalahan saat memfilter data');
            },
            complete: function() {
                $('#filterBtn').html('<i class="fas fa-search"></i> Cari Data');
                $('#filterBtn').prop('disabled', false);
            }
        });
    }

    function updateTable(data) {
        console.log('Updating table with data:', data ? data.length : 0, 'items');
        console.log('Data sample:', data ? data.slice(0, 2) : 'No data');
        
        // Destroy existing DataTable safely
        if (dataTable && $.fn.DataTable.isDataTable('#puskesmasTable')) {
            dataTable.destroy();
            dataTable = null;
        }

        // Clear table content
        const tbody = $('#puskesmasTable tbody');
        tbody.empty();
        
        // Validate we have 9 header columns
        const headerCount = $('#puskesmasTable thead th').length;
        if (headerCount !== 9) {
            console.error('Header column count is not 9:', headerCount);
            return;
        }

        // Handle data population
        if (!data || data.length === 0) {
            // For empty data, add a proper row with exact column count (9 columns)
            tbody.append(`
                <tr>
                    <td class="excel-cell text-center">-</td>
                    <td class="excel-cell text-center">-</td>
                    <td class="excel-cell text-center">-</td>
                    <td class="excel-cell text-center">-</td>
                    <td class="excel-cell text-center">-</td>
                    <td class="excel-cell text-center">-</td>
                    <td class="excel-cell text-center">-</td>
                    <td class="excel-cell text-center">-</td>
                    <td class="excel-cell text-center">
                        <div class="alert alert-info mb-0" style="font-size: 0.8rem; padding: 0.25rem;">
                            <i class="fas fa-info-circle"></i> No data
                        </div>
                    </td>
                </tr>
            `);
        } else {
            // Add data rows
            data.forEach(function(item, index) {
                const row = `
                    <tr>
                        <td class="excel-cell text-center">${index + 1}</td>
                        <td class="excel-cell editable" data-field="name" contenteditable="false">${item.name || '-'}</td>
                        <td class="excel-cell">${item.provinsi || '-'}</td>
                        <td class="excel-cell">${item.kabupaten_kota || '-'}</td>
                        <td class="excel-cell">${item.kecamatan || '-'}</td>
                        <td class="excel-cell editable" data-field="pic" contenteditable="false">${item.pic || '-'}</td>
                        <td class="excel-cell editable" data-field="kepala" contenteditable="false">${item.kepala || '-'}</td>
                        <td class="excel-cell editable" data-field="pic_dinkes_prov" contenteditable="false">${item.pic_dinkes_prov || '-'}</td>
                        <td class="excel-cell editable" data-field="pic_dinkes_kab" contenteditable="false">${item.pic_dinkes_kab || '-'}</td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        // Validate table structure before reinitializing
        const finalHeaderCount = $('#puskesmasTable thead th').length;
        const finalRowCount = $('#puskesmasTable tbody tr:first td').length;
        
        console.log(`Final validation - Header: ${finalHeaderCount}, First row: ${finalRowCount}`);
        
        if (finalHeaderCount === 9 && (finalRowCount === 0 || finalRowCount === 9)) {
            // Small delay before reinitializing DataTable to ensure DOM is ready
            setTimeout(function() {
                initializeDataTable();
            }, 100);
        } else {
            console.error('Table structure validation failed, rebuilding table...');
            rebuildTableStructure();
        }
    }
    
    function rebuildTableStructure() {
        console.log('Rebuilding table structure...');
        
        // Show message to user
        const tbody = $('#puskesmasTable tbody');
        tbody.empty();
        tbody.append(`
            <tr>
                <td class="excel-cell text-center">-</td>
                <td class="excel-cell text-center">-</td>
                <td class="excel-cell text-center">-</td>
                <td class="excel-cell text-center">-</td>
                <td class="excel-cell text-center">-</td>
                <td class="excel-cell text-center">-</td>
                <td class="excel-cell text-center">-</td>
                <td class="excel-cell text-center">-</td>
                <td class="excel-cell text-center">
                    <div class="alert alert-warning mb-0" style="font-size: 0.8rem; padding: 0.25rem;">
                        <i class="fas fa-exclamation-triangle"></i> Structure Error
                    </div>
                </td>
            </tr>
        `);
        
        setTimeout(function() {
            initializeDataTable();
        }, 150);
    }

    // Helper function to safely get option text
    function getOptionText(tomSelectInstance, value) {
        try {
            const options = tomSelectInstance.options;
            const option = options[value];
            return option ? option.name : '';
        } catch (e) {
            console.warn('Error getting option text:', e);
            return '';
        }
    }

    function showFilterInfo(provinceText, regencyText, districtText) {
        let filterText = [];
        
        if (provinceText) {
            filterText.push(`Provinsi: ${provinceText}`);
        }
        
        if (regencyText) {
            filterText.push(`Kabupaten: ${regencyText}`);
        }
        
        if (districtText) {
            filterText.push(`Kecamatan: ${districtText}`);
        }

        if (filterText.length > 0) {
            const info = `
                <div id="filterInfo" class="alert alert-info alert-dismissible fade show">
                    <strong><i class="fas fa-info-circle"></i> Filter Aktif:</strong> ${filterText.join(' | ')}
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
            $('.table-responsive').before(info);
        }
    }

    function resetFilters() {
        // Clear all selections
        provinceSelect.clear();
        regencySelect.clear();
        districtSelect.clear();
        
        // Reset options
        clearRegencies();
        clearDistricts();
        
        // Remove filter info
        $('#filterInfo').remove();
        
        // Reload page to show all data
        location.reload();
    }

    function initializeDataTable() {
        try {
            console.log('Initializing DataTable...');
            
            // Validate table structure first
            const headerCount = $('#puskesmasTable thead th').length;
            const firstRowCount = $('#puskesmasTable tbody tr:first td').length;
            const totalRows = $('#puskesmasTable tbody tr').length;
            
            console.log(`Table structure - Header: ${headerCount} columns, First row: ${firstRowCount} columns, Total rows: ${totalRows}`);
            
            if (headerCount !== 9) {
                console.error('Header column count is not 9:', headerCount);
                throw new Error(`Header should have 9 columns, but has ${headerCount}`);
            }
            
            if (firstRowCount > 0 && firstRowCount !== 9) {
                console.warn(`First row has ${firstRowCount} columns instead of 9, but continuing...`);
            }
            
            // Safely destroy existing DataTable
            if (dataTable && $.fn.DataTable.isDataTable('#puskesmasTable')) {
                dataTable.destroy();
                dataTable = null;
            }
            
            dataTable = $('#puskesmasTable').DataTable({
                responsive: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                searching: true,
                ordering: true,
                info: true,
                destroy: true,
                autoWidth: false,
                processing: false,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                columnDefs: [
                    { "defaultContent": "-", "targets": "_all" },
                    { 
                        "targets": 0,
                        "orderable": false,
                        "searchable": false,
                        "className": "text-center",
                        "width": "50px"
                    },
                    { "targets": 1, "width": "200px" },
                    { "targets": [2, 3, 4], "width": "120px" },
                    { "targets": [5, 6, 7, 8], "width": "150px" }
                ],
                language: {
                    search: "Pencarian:",
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
                    emptyTable: "Tidak ada data yang ditemukan",
                    zeroRecords: "Tidak ada data yang cocok dengan pencarian",
                    processing: "Memproses data..."
                },
                drawCallback: function() {
                    bindCellEvents();
                },
                initComplete: function() {
                    console.log('DataTable initialized successfully');
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTable error:', error, thrown);
                }
            });
            
        } catch (error) {
            console.error('Error initializing DataTable:', error);
            
            // Validate table structure
            const headerCols = $('#puskesmasTable thead th').length;
            const bodyCols = $('#puskesmasTable tbody tr:first td').length;
            
            console.log(`Header columns: ${headerCols}, Body columns: ${bodyCols}`);
            
            // Show user-friendly error message
            const errorMsg = `
                <div class="alert alert-warning alert-dismissible fade show mt-3">
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong><i class="fas fa-exclamation-triangle"></i> Peringatan:</strong>
                    Terjadi kesalahan saat memuat tabel (Header: ${headerCols} cols, Body: ${bodyCols} cols). 
                    <a href="javascript:location.reload()" class="alert-link">Refresh halaman</a>
                </div>
            `;
            
            $('#puskesmasTable').closest('.table-responsive').after(errorMsg);
        }
    }

    function bindCellEvents() {
        // Handle cell selection
        $('.excel-cell').off('click').on('click', function() {
            if (selectedCell) {
                selectedCell.removeClass('selected');
            }
            
            selectedCell = $(this);
            selectedCell.addClass('selected');
        });

        // Handle double click for editing
        $('.editable').off('dblclick').on('dblclick', function() {
            if (editingCell) {
                saveEdit(editingCell);
            }

            editingCell = $(this);
            const originalValue = editingCell.text().trim();
            
            editingCell.addClass('editing');
            editingCell.attr('contenteditable', 'true');
            editingCell.focus();
            
            // Select all text
            const range = document.createRange();
            range.selectNodeContents(editingCell[0]);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
            
            // Store original value
            editingCell.data('original-value', originalValue);
        });

        // Handle Enter key to save
        $('.editable').off('keydown').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveEdit($(this));
            } else if (e.key === 'Escape') {
                cancelEdit($(this));
            }
        });
    }

    // Initial bind
    bindCellEvents();

    // Handle click outside to save
    $(document).on('click', function(e) {
        if (editingCell && !$(e.target).is(editingCell)) {
            saveEdit(editingCell);
        }
    });

    function saveEdit(cell) {
        if (!cell || !cell.hasClass('editing')) return;
        
        const newValue = cell.text().trim();
        const originalValue = cell.data('original-value');
        const field = cell.data('field');
        
        cell.removeClass('editing');
        cell.attr('contenteditable', 'false');
        
        if (newValue !== originalValue && newValue !== '') {
            // Here you would typically send an AJAX request to save the data
            console.log(`Saving ${field}: ${newValue}`);
            
            // Show success feedback
            cell.css('background-color', '#d4edda').delay(1000).queue(function() {
                $(this).css('background-color', '').dequeue();
            });
        }
        
        editingCell = null;
    }

    function cancelEdit(cell) {
        if (!cell || !cell.hasClass('editing')) return;
        
        const originalValue = cell.data('original-value');
        cell.text(originalValue);
        cell.removeClass('editing');
        cell.attr('contenteditable', 'false');
        
        editingCell = null;
    }

    // Keyboard navigation
    $(document).on('keydown', function(e) {
        if (!selectedCell || editingCell) return;
        
        let newCell = null;
        const currentRow = selectedCell.closest('tr');
        const currentCellIndex = selectedCell.index();
        
        switch(e.key) {
            case 'ArrowUp':
                newCell = currentRow.prev('tr').find('td').eq(currentCellIndex);
                break;
            case 'ArrowDown':
                newCell = currentRow.next('tr').find('td').eq(currentCellIndex);
                break;
            case 'ArrowLeft':
                newCell = selectedCell.prev('td');
                break;
            case 'ArrowRight':
                newCell = selectedCell.next('td');
                break;
        }
        
        if (newCell && newCell.length) {
            e.preventDefault();
            selectedCell.removeClass('selected');
            selectedCell = newCell;
            selectedCell.addClass('selected');
            
            // Scroll into view if needed
            newCell[0].scrollIntoView({ block: 'nearest', inline: 'nearest' });
        }
    });
});
</script>
@stop
