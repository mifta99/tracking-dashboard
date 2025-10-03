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
                    <div class="card" style="background-color: #17A2B8; height: 80px;">
                        <div class="card-body d-flex justify-content-between align-items-center position-relative h-100">
                            <div>
                                <h4 class="text-white font-weight-bold mb-1">15</h4>
                                <p class="text-white mb-0">Provinsi</p>
                            </div>
                            <div class=" position-absolute" style="color: #147483;font-size: 3rem; right: 15px; top: 50%; transform: translateY(-50%);">
                                <i class="fas fa-landmark"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card" style="background-color: #28A745; height: 80px;">
                        <div class="card-body d-flex justify-content-between align-items-center position-relative h-100">
                            <div>
                                <h4 class="text-white font-weight-bold mb-1">{{ 53 }}</h4>
                                <p class="text-white mb-0">Kabupaten/Kota</p>
                            </div>
                            <div class=" position-absolute" style="color: #137a2b;font-size: 3rem; right: 15px; top: 50%; transform: translateY(-50%); ">
                            <i class="fas fa-city"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card" style="background-color: #FFC107; height: 80px;">
                        <div class="card-body d-flex justify-content-between align-items-center position-relative h-100">
                            <div>
                                <h4 class="text-dark font-weight-bold mb-1">{{ 53 }}</h4>
                                <p class="text-dark mb-0">Kecamatan</p>
                            </div>
                            <div class=" position-absolute" style="color: #927418;font-size: 3rem; right: 15px; top: 50%; transform: translateY(-50%); ">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card" style="background-color: #DC3545; height: 80px;">
                        <div class="card-body d-flex justify-content-between align-items-center position-relative h-100">
                            <div>
                                <h4 class="text-white font-weight-bold mb-1">{{ $countPuskesmas }}</h4>
                                <p class="text-white mb-0">Puskesmas</p>
                            </div>
                            <div class=" position-absolute" style="color: #861a25; font-size: 3rem; right: 15px; top: 50%; transform: translateY(-50%);">
                                <i class="fas fa-hospital"></i>
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
                            <div id="trackingSummaryChart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            Monthly Issue & Incident Report
                        </div>
                        <div class="card-body">
                            <div id="monthlyIssueChart" style="height:300px; width:100%;"></div>
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
                    <label class="small mb-1">Status</label>
                    <select class="form-control form-control-sm" id="status" name="status">
                        <option value="">Semua Status</option>
                        
                        <option value="1">Shipment Process</option>
                        <option value="2">On Delivery</option>
                        <option value="3">Received</option>
                        <option value="4">Instalasi</option>
                        <option value="5">Uji Fungsi</option>
                        <option value="6">Pelatihan Alat</option>
                        <option value="7">BASTO</option>
                        <option value="8">ASPAK</option>
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
                                <th class="text-center" style="font-size: 11pt;">Status</th>
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
    
    <!-- ECharts (Pie Chart Replacement for Highcharts) -->
    <script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
    
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
                    targets: 6,
                    render: function (data, type, row) {
                        if (row.pengiriman) {
                            const tahapId = row.pengiriman.tahapan_id;
                            switch(tahapId) {
                                case 1:
                                    return '<div class="d-flex justify-content-center align-items-center"><span class="badge badge-info badge-sm">Shipment Process</span></div>';
                                case 2:
                                    return '<div class="d-flex justify-content-center align-items-center"><span class="badge badge-warning badge-sm">On Delivery</span></div>';
                                case 3:
                                    return '<div class="d-flex justify-content-center align-items-center"><span class="badge badge-success badge-sm">Received</span></div>';
                                case 4:
                                    return '<div class="d-flex justify-content-center align-items-center"><span class="badge badge-primary badge-sm">Instalasi</span></div>';
                                case 5:
                                    return '<div class="d-flex justify-content-center align-items-center"><span class="badge badge-dark badge-sm">Uji Fungsi</span></div>';
                                case 6:
                                    return '<div class="d-flex justify-content-center align-items-center"><span class="badge badge-light badge-sm text-dark">Pelatihan Alat</span></div>';
                                case 7:
                                    return '<div class="d-flex justify-content-center align-items-center"><span class="badge badge-danger badge-sm">BASTO</span></div>';
                                case 8:
                                    return '<div class="d-flex justify-content-center align-items-center"><span class="badge badge-success badge-sm">ASPAK</span></div>';
                                default:
                                    return '<div class="d-flex justify-content-center align-items-center"><span class="badge badge-secondary badge-sm">Belum Diproses</span></div>';
                            }
                        }
                        return '<div class="d-flex justify-content-center align-items-center"><span class="badge badge-secondary badge-sm">Belum Diproses</span></div>';
                    }
                },
                {
                    targets: 7, // Actions
                    render: function (data, type, row) {
                        const detailUrl = '{{ route('verification-request.detail', ':id') }}'.replace(':id', row.id);
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
        
        // Status change handler
        $('#status').on('change', function() {
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
            const statusId = $('#status').val();
            
            if (provinceId) filters.province_id = provinceId;
            if (regencyId) filters.regency_id = regencyId;
            if (districtId) filters.district_id = districtId;
            if (statusId) filters.status = statusId;
            
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
        
        // Load tracking pie chart (ECharts)
        function loadTrackingPieChart() {
            const chartEl = document.getElementById('trackingSummaryChart');
            if(!chartEl){
                console.warn('Chart container not found');
                return;
            }
            const chart = echarts.init(chartEl, null, { renderer: 'canvas' });

            // Static sample data (replace with dynamic if needed)
            const dataItems = [
                { value: {{ $dataStatus['shipment_process'] }}, name: 'Shipment Process' },
                { value: {{ $dataStatus['on_delivery'] }},  name: 'On Delivery' },
                { value: {{ $dataStatus['received'] }}, name: 'Received' },
                { value: {{ $dataStatus['installation'] }}, name: 'Instalasi' },
                { value: {{ $dataStatus['function_test'] }},  name: 'Uji Fungsi' },
                { value: {{ $dataStatus['item_training'] }},  name: 'Pelatihan Alat' },
                { value: {{ $dataStatus['basto'] }},  name: 'BASTO' },
                { value: {{ $dataStatus['aspak'] }}, name: 'ASPAK' },
            ];

            const option = {
                backgroundColor: 'transparent',
                title: {
                    text: 'Distribusi Status Tracking',
                    left: 'center',
                    top: 10,
                    textStyle: { fontSize: 16, fontWeight: 600, color: '#2d3748' }
                },
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} ({d}%)'
                },
                legend: {
                    type: 'scroll',
                    bottom: 0,
                    textStyle: { fontSize: 12 }
                },
                series: [
                    {
                        name: 'Status',
                        type: 'pie',
                        radius: ['40%', '70%'],
                        center: ['50%', '50%'],
                        avoidLabelOverlap: true,
                        itemStyle: {
                            borderRadius: 8,
                            borderColor: '#fff',
                            borderWidth: 2
                        },
                        label: {
                            show: true,
                            formatter: '{b}: {d}%'
                        },
                        labelLine: {
                            show: true,
                            smooth: true,
                            length: 10,
                            length2: 10
                        },
                        emphasis: {
                            itemStyle: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0,0,0,0.25)'
                            }
                        },
                        data: dataItems,
                        color: [
                            '#00b8d9', '#ffc400', '#36b37e', '#6554c0', '#ff5630',
                            '#ff8b00', '#ff4d4f', '#2684ff', '#6b778c'
                        ]
                    }
                ]
            };

            chart.setOption(option);
            window.addEventListener('resize', () => chart.resize());
        }
        
        // Load Monthly Issue Bar Chart (last 5 months)
        function loadMonthlyIssueChart(){
            const el = document.getElementById('monthlyIssueChart');
            if(!el){ return; }
            const chart = echarts.init(el);

            // Helper to get last 5 month labels
            function getLastFiveMonths(){
                const arr = [];
                const now = new Date();
                for(let i=4;i>=0;i--){
                    const d = new Date(now.getFullYear(), now.getMonth()-i, 1);
                    const formatter = d.toLocaleDateString('id-ID',{ month:'short', year:'2-digit'});
                    arr.push(formatter);
                }
                return arr;
            }

            const monthLabels = getLastFiveMonths();

            // Placeholder static data (replace with API response later)
            // Example endpoint suggestion: route('api-issues.monthly') returning [{month:'2025-06', total:12}, ...]
            const issueData = [5, 9, 4, 11, 7];
            const incidentData = [2, 3, 1, 4, 2]; 

            const option = {
                backgroundColor: 'transparent',
                tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
                grid: { left: '5%', right: '5%', top: 40, bottom: 50, containLabel: true },
                title: { text: 'Keluhan & Insiden 5 Bulan Terakhir', left: 'center', top: 5, textStyle:{ fontSize:14, fontWeight:600 } },
                legend: {
                    data: ['Keluhan', 'Insiden'],
                    top: 25,
                    textStyle: { fontSize: 12 }
                },
                xAxis: {
                    type: 'category',
                    data: monthLabels,
                    axisLine: { lineStyle: { color: '#94a3b8' } },
                    axisLabel: { color: '#475569', fontSize: 12 }
                },
                yAxis: {
                    type: 'value',
                    name: 'Jumlah',
                    nameTextStyle:{ color:'#475569', padding:[0,0,5,0]},
                    axisLine: { show:false },
                    splitLine: { lineStyle: { color: 'rgba(148,163,184,0.25)' } },
                    axisLabel: { color: '#475569' }
                },
                series: [
                    {
                        name: 'Keluhan',
                        type: 'bar',
                        data: issueData,
                        barWidth: '20%',
                        itemStyle: {
                            borderRadius: [6,6,0,0],
                            color: '#dc3545'
                        },
                        label: { show: true, position: 'top', color: '#334155', fontWeight: 600 }
                    },
                    {
                        name: 'Insiden',
                        type: 'bar',
                        data: incidentData,
                        barWidth: '20%',
                        itemStyle: {
                            borderRadius: [6,6,0,0],
                            color: '#ffc107'
                        },
                        label: { show: true, position: 'top', color: '#334155', fontWeight: 600 }
                    }
                ],
                toolbox: {
                    feature: {
                        saveAsImage: { title:'Save' }
                    },
                    right: 20
                }
            };

            chart.setOption(option);
            window.addEventListener('resize', ()=> chart.resize());

        }

        // Load charts on page load
        loadTrackingPieChart();
        loadMonthlyIssueChart();

        // Add refresh functionality
        window.refreshTableData = function() {
            loadTableData();
        };
        }
    });
    
    </script>
@stop
