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
                                <h4 class="text-white font-weight-bold mb-1">{{$countDataProvince}}</h4>
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
                                <h4 class="text-white font-weight-bold mb-1">{{ $countRegency }}</h4>
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
                                <h4 class="text-dark font-weight-bold mb-1">{{ $countDistrict}}</h4>
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
                <div class="col-12">
                    <div class="card card-collapsible tracking-summary-card">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Tracking Summary</h3>
                            <div class="card-tools d-flex align-items-center ml-auto justify-content-between flex-wrap flex-md-nowrap">
                                <div class="form-inline mr-2">
                                    <label for="trackingChartMode" class="mr-2 mb-0 text-white-50">Tampilan Chart:</label>
                                    <select id="trackingChartMode" class="form-control form-control-sm">
                                        <option value="cascade">Cascade Waterfall</option>
                                        <option value="block">Blok Horizontal</option>
                                        <option value="bar">Bar Vertikal</option>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body tracking-summary-body">
                            <div id="trackingSummaryChart" class="chart-canvas"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-lg-6">
                    <div class="card card-collapsible monthly-chart-card">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Keluhan Yang Terjadi pada 5 Bulan Terakhir</h3>
                            <div class="card-tools ml-auto">
                                <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="monthlyIssueChart" class="chart-canvas"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mt-3 mt-lg-0">
                    <div class="card card-collapsible monthly-chart-card">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Insiden Yang Terjadi pada 5 Bulan Terakhir</h3>
                            <div class="card-tools ml-auto">
                                <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="monthlyIncidentChart" class="chart-canvas"></div>
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
                        @foreach($tahapan as $tahap)
                            <option value="{{ $tahap->tahap_ke }}">{{ $tahap->tahapan }}</option>
                        @endforeach
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
        .card-collapsible .card-header .btn-tool {
            color: #ffffff;
        }
        .card-collapsible .card-header .form-inline label {
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 0;
        }
        .card-collapsible .card-header .form-control {
            min-width: 165px;
        }
        .tracking-summary-card .tracking-summary-body {
            padding: 1.25rem;
        }
        .chart-canvas {
            width: 100%;
            height: 320px;
        }
        .tracking-summary-card .chart-canvas {
            height: 360px;
        }
        .monthly-chart-card .chart-canvas {
            height: 300px;
        }
        @media (max-width: 992px) {
            .tracking-summary-card .chart-canvas {
                height: 320px;
            }
            .monthly-chart-card .chart-canvas {
                height: 260px;
            }
        }
        @media (max-width: 576px) {
            .card-collapsible .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .card-collapsible .card-header .card-tools {
                width: 100%;
                justify-content: space-between;
                margin-top: 0.75rem;
            }
            .card-collapsible .card-header .form-inline {
                width: 100%;
            }
            .card-collapsible .card-header .form-control {
                width: 100%;
            }
            .tracking-summary-card .chart-canvas {
                height: 280px;
            }
            .monthly-chart-card .chart-canvas {
                height: 220px;
            }
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
    $(document).ready(function () {
        'use strict';

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
                    targets: 5,
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
                            switch (tahapId) {
                                @foreach ($dataStatus as $status => $count)
                                case {{ $loop->index + 1 }}:
                                    @php
                                        $statusText = ucfirst(str_replace("_", " ", $status));
                                        $badgeClass = 'badge-secondary';
                                        
                                        // Match colors with verification request index
                                        switch($status) {
                                            case 'Pengemasan':
                                                $badgeClass = 'badge-secondary';
                                                break;
                                            case 'Dalam Pengiriman':
                                                $badgeClass = 'badge-info';
                                                break;
                                            case 'Penerimaan':
                                                $badgeClass = 'badge-primary';
                                                break;
                                            case 'Instalasi':
                                                $badgeClass = 'badge-warning text-dark';
                                                break;
                                            case 'Uji Fungsi':
                                                $badgeClass = 'bg-purple text-white';
                                                break;
                                            case 'Pelatihan Alat':
                                                $badgeClass = 'badge-dark';
                                                break;
                                            case 'ASPAK':
                                                $badgeClass = 'badge-success';
                                                break;
                                            case 'BASTO':
                                                $badgeClass = 'badge-danger';
                                                break;
                                            default:
                                                $badgeClass = 'badge-light';
                                                break;
                                        }
                                    @endphp
                                    return '<div class="d-flex justify-content-center align-items-center"><span class="badge {{ $badgeClass }} badge-sm">{{ $statusText }}</span></div>';
                                @endforeach
                            }
                        }
                        return '<div class="d-flex justify-content-center align-items-center"><span class="badge badge-secondary badge-sm">Belum Diproses</span></div>';
                    }
                },
                {
                    targets: 7,
                    render: function (data, type, row) {
                        const detailUrl = '{{ route("verification-request.detail", ":id") }}'.replace(':id', row.id);
                        return `<div class="d-flex justify-content-center align-items-center">
                                    <a href="${detailUrl}" class="text-secondary" title="Lihat Detail">
                                        <i class="fas fa-search"></i>
                                    </a>
                                </div>`;
                    }
                }
            ],
            order: [[4, 'asc']]
        });

        // Initial loads
        loadProvinces();
        loadTableData();

        // Filter handlers
        $('#provinsi').on('change', function () {
            const provinceId = $(this).val();
            resetSelect($('#kabupaten'), 'Pilih Kabupaten');
            resetSelect($('#kecamatan'), 'Pilih Kecamatan');

            if (provinceId) {
                loadRegencies(provinceId);
            }
            loadTableData();
        });

        $('#kabupaten').on('change', function () {
            const regencyId = $(this).val();
            resetSelect($('#kecamatan'), 'Pilih Kecamatan');

            if (regencyId) {
                loadDistricts(regencyId);
            }
            loadTableData();
        });

        $('#kecamatan').on('change', loadTableData);
        $('#status').on('change', loadTableData);

        function resetSelect($select, placeholder) {
            $select.empty().append(`<option value="">${placeholder}</option>`);
        }

        function loadProvinces() {
            $.get(provincesUrl)
                .done(function (response) {
                    if (response.success && response.data) {
                        const $select = $('#provinsi');
                        resetSelect($select, 'Pilih Provinsi');
                        response.data.forEach(function (province) {
                            $select.append(`<option value="${province.id}">${province.name}</option>`);
                        });
                    }
                })
                .fail(function (xhr, status, error) {
                    console.error('Failed to load provinces:', error);
                });
        }

        function loadRegencies(provinceId) {
            $.get(regenciesUrl, { province_id: provinceId })
                .done(function (response) {
                    if (response.success && response.data) {
                        const $select = $('#kabupaten');
                        resetSelect($select, 'Pilih Kabupaten');
                        response.data.forEach(function (regency) {
                            $select.append(`<option value="${regency.id}">${regency.name}</option>`);
                        });
                    }
                })
                .fail(function (xhr, status, error) {
                    console.error('Failed to load regencies:', error);
                });
        }

        function loadDistricts(regencyId) {
            $.get(districtsUrl, { regency_id: regencyId })
                .done(function (response) {
                    if (response.success && response.data) {
                        const $select = $('#kecamatan');
                        resetSelect($select, 'Pilih Kecamatan');
                        response.data.forEach(function (district) {
                            $select.append(`<option value="${district.id}">${district.name}</option>`);
                        });
                    }
                })
                .fail(function (xhr, status, error) {
                    console.error('Failed to load districts:', error);
                });
        }

        function loadTableData() {
            const filters = {};
            const provinceId = $('#provinsi').val();
            const regencyId = $('#kabupaten').val();
            const districtId = $('#kecamatan').val();
            const statusId = $('#status').val();

            if (provinceId) filters.province_id = provinceId;
            if (regencyId) filters.regency_id = regencyId;
            if (districtId) filters.district_id = districtId;
            if (statusId) filters.status = statusId;

            $('#reported-incidents-table tbody').html('<tr><td colspan="8" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>');

            $.get(fetchDataUrl, filters)
                .done(function (response) {
                    if (response.success) {
                        table.clear();
                        if (response.data && response.data.length > 0) {
                            table.rows.add(response.data);
                        }
                        table.draw();
                    } else {
                        console.error('API returned error:', response.message || 'Unknown error');
                        table.clear().draw();
                    }
                })
                .fail(function (xhr, status, error) {
                    console.error('Failed to load table data:', error);
                    $('#reported-incidents-table tbody').html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data. Silakan coba lagi.</td></tr>');
                });
        }

        window.refreshTableData = loadTableData;

        // ===== Tracking Summary & Monthly Charts =====
        const trackingStatusOrder = [
            @foreach ($dataStatus as $status => $count)
            { key: '{{ $status }}', label: '{{ ucfirst(str_replace("_", " ", $status)) }}' },
            @endforeach
        ];
        const trackingBarColors = ['#1d4ed8', '#0ea5e9', '#22c55e', '#eab308', '#ef4444', '#a855f7', '#f97316', '#64748b'];
        const trackingRawStatusData = @json($dataStatus);
        console.log('trackingRawStatusData:', trackingRawStatusData);
        // Randomize trackingRawStatusData values for demo purposes
        (function () {
            const currentValues = Object.values(trackingRawStatusData || {}).map(v => Number(v) || 0);
            const maxExisting = currentValues.length ? Math.max(...currentValues) : 50;
            const upperBound = Number.isFinite(maxExisting) && maxExisting > 0 ? maxExisting : 50;
            const randInt = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min;

            trackingStatusOrder.forEach(item => {
                trackingRawStatusData[item.key] = randInt(0, upperBound);
            });
        })();
        const trackingCategories = trackingStatusOrder.map(item => item.label);
        const trackingValues = trackingStatusOrder.map(item => Number(trackingRawStatusData[item.key] ?? 0));

        let trackingChartInstance = null;
        let currentTrackingMode = 'cascade';

        const monthLabels = getLastFiveMonths();
        const issueData = [5, 9, 4, 11, 7];
        const incidentData = [2, 3, 1, 4, 2];
        let monthlyIssueChartInstance = null;
        let monthlyIncidentChartInstance = null;

        renderTrackingChart(currentTrackingMode);
        monthlyIssueChartInstance = renderMonthlyBarChart('monthlyIssueChart', 'Keluhan', issueData, '#dc3545', monthlyIssueChartInstance);
        monthlyIncidentChartInstance = renderMonthlyBarChart('monthlyIncidentChart', 'Insiden', incidentData, '#1d4ed8', monthlyIncidentChartInstance);

        $('#trackingChartMode').on('change', function () {
            const selected = this.value || 'cascade';
            currentTrackingMode = selected;
            renderTrackingChart(selected);
        });

        let resizeTimeout;
        $(window).on('resize', function () {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function () {
                renderTrackingChart(currentTrackingMode);
                monthlyIssueChartInstance = renderMonthlyBarChart('monthlyIssueChart', 'Keluhan', issueData, '#dc3545', monthlyIssueChartInstance);
                monthlyIncidentChartInstance = renderMonthlyBarChart('monthlyIncidentChart', 'Insiden', incidentData, '#1d4ed8', monthlyIncidentChartInstance);
            }, 200);
        });

        $(document).on('expanded.lte.cardwidget collapsed.lte.cardwidget', function () {
            setTimeout(function () {
                renderTrackingChart(currentTrackingMode);
                monthlyIssueChartInstance = renderMonthlyBarChart('monthlyIssueChart', 'Keluhan', issueData, '#dc3545', monthlyIssueChartInstance);
                monthlyIncidentChartInstance = renderMonthlyBarChart('monthlyIncidentChart', 'Insiden', incidentData, '#1d4ed8', monthlyIncidentChartInstance);
            }, 220);
        });

        function getScreenFlags() {
            const width = window.innerWidth || document.documentElement.clientWidth;
            return {
                isSmall: width <= 576,
                isTablet: width <= 768,
                isDesktop: width >= 992
            };
        }

        function renderTrackingChart(mode) {
            const chartEl = document.getElementById('trackingSummaryChart');
            if (!chartEl) {
                return;
            }
            if (!trackingChartInstance) {
                trackingChartInstance = echarts.init(chartEl, null, { renderer: 'canvas', devicePixelRatio: window.devicePixelRatio || 1 });
            }

            const flags = getScreenFlags();
            let option;
            switch (mode) {
                case 'block':
                    option = buildBlockOption(flags);
                    break;
                case 'bar':
                    option = buildStandardBarOption(flags);
                    break;
                case 'cascade':
                default:
                    option = buildCascadeOption(flags);
            }

            trackingChartInstance.setOption(option, true);
            trackingChartInstance.resize();
        }

        function buildCascadeOption(flags) {
            let runningTotal = 0;
            const assistData = [];
            const segments = trackingValues.map((value, index) => {
                assistData.push(runningTotal);
                runningTotal += value;
                return {
                    value,
                    itemStyle: {
                        color: trackingBarColors[index % trackingBarColors.length],
                        borderRadius: [0, 6, 6, 0]
                    }
                };
            });

            const trimmedCategories = trackingCategories.map(label => {
                if (flags.isTablet && label.length > 18) {
                    return `${label.substring(0, 18)}…`;
                }
                return label;
            });

            return {
                backgroundColor: 'transparent',
                title: {
                    text: 'Distribusi Status Tracking',
                    left: 'center',
                    top: 12,
                    textStyle: {
                        fontSize: flags.isSmall ? 14 : 16,
                        fontWeight: 600,
                        color: '#2d3748'
                    }
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: { type: 'shadow' },
                    formatter: params => {
                        const current = params.find(item => item.seriesName === 'Jumlah');
                        if (!current) {
                            return '';
                        }
                        return `${current.name}<br/>Jumlah: ${current.value}`;
                    }
                },
                grid: {
                    left: '8%',
                    right: '8%',
                    top: 60,
                    bottom: flags.isSmall ? 55 : 45,
                    containLabel: true
                },
                xAxis: {
                    type: 'value',
                    name: 'Jumlah',
                    nameLocation: 'middle',
                    nameGap: flags.isSmall ? 24 : 32,
                    nameTextStyle: {
                        fontSize: flags.isSmall ? 10 : 12
                    },
                    axisLine: { lineStyle: { color: '#94a3b8' } },
                    axisLabel: {
                        color: '#475569',
                        fontSize: flags.isSmall ? 10 : 12
                    },
                    splitLine: { lineStyle: { color: 'rgba(148,163,184,0.25)' } }
                },
                yAxis: {
                    type: 'category',
                    data: trimmedCategories,
                    axisTick: { show: false },
                    axisLine: { lineStyle: { color: '#94a3b8' } },
                    axisLabel: {
                        color: '#475569',
                        fontSize: flags.isSmall ? 10 : 12,
                        interval: 0
                    }
                },
                series: [
                    {
                        type: 'bar',
                        stack: 'total',
                        silent: true,
                        itemStyle: {
                            borderColor: 'transparent',
                            color: 'transparent'
                        },
                        data: assistData
                    },
                    {
                        name: 'Jumlah',
                        type: 'bar',
                        stack: 'total',
                        barWidth: flags.isTablet ? '40%' : '45%',
                        label: {
                            show: true,
                            position: 'right',
                            color: '#1e293b',
                            fontWeight: 600,
                            fontSize: flags.isSmall ? 10 : 12,
                            formatter: ({ value }) => value
                        },
                        data: segments
                    }
                ]
            };
        }

        function buildBlockOption(flags) {
            return {
                backgroundColor: 'transparent',
                title: {
                    text: 'Distribusi Status Tracking',
                    left: 'center',
                    top: 12,
                    textStyle: {
                        fontSize: flags.isSmall ? 14 : 16,
                        fontWeight: 600,
                        color: '#2d3748'
                    }
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: { type: 'shadow' },
                    formatter: params => params
                        .filter(item => item.value !== undefined && item.value > 0)
                        .map(item => `${item.marker} ${item.seriesName}: ${item.value}`)
                        .join('<br/>')
                },
                legend: {
                    orient: 'horizontal',
                    left: 'center',
                    bottom: 10,
                    textStyle: { fontSize: flags.isSmall ? 10 : 12 },
                    itemWidth: flags.isSmall ? 12 : 14,
                    itemHeight: flags.isSmall ? 8 : 10,
                    itemGap: flags.isSmall ? 8 : 12,
                    formatter: name => {
                        const idx = trackingCategories.indexOf(name);
                        const value = idx > -1 ? trackingValues[idx] : 0;
                        const maxLength = flags.isSmall ? 12 : 18;
                        const displayName = name.length > maxLength ? `${name.substring(0, maxLength)}…` : name;
                        return `${displayName}: ${value}`;
                    }
                },
                grid: {
                    left: '8%',
                    right: '8%',
                    top: 60,
                    bottom: flags.isSmall ? 110 : 85,
                    containLabel: true
                },
                xAxis: {
                    type: 'value',
                    name: 'Jumlah',
                    nameTextStyle: { fontSize: flags.isSmall ? 10 : 12 },
                    axisLine: { lineStyle: { color: '#94a3b8' } },
                    axisLabel: {
                        color: '#475569',
                        fontSize: flags.isSmall ? 10 : 12
                    },
                    splitLine: { lineStyle: { color: 'rgba(148,163,184,0.25)' } }
                },
                yAxis: {
                    type: 'category',
                    data: ['Status'],
                    axisTick: { show: false },
                    axisLine: { lineStyle: { color: '#94a3b8' } },
                    axisLabel: {
                        color: '#475569',
                        fontSize: flags.isSmall ? 10 : 12
                    }
                },
                series: trackingCategories.map((name, index) => ({
                    name,
                    type: 'bar',
                    stack: 'total',
                    barWidth: flags.isTablet ? '45%' : '55%',
                    itemStyle: {
                        color: trackingBarColors[index % trackingBarColors.length],
                        borderRadius: [4, 4, 4, 4]
                    },
                    label: {
                        show: trackingValues[index] > 0,
                        position: trackingValues[index] > 5 ? 'inside' : 'right',
                        color: trackingValues[index] > 5 ? '#ffffff' : '#1e293b',
                        fontWeight: 600,
                        fontSize: flags.isSmall ? 10 : 12,
                        formatter: ({ value }) => value
                    },
                    data: [trackingValues[index]]
                }))
            };
        }

        function buildStandardBarOption(flags) {
            const trimmedCategories = trackingCategories.map(label => {
                if (flags.isSmall && label.length > 12) {
                    return `${label.substring(0, 12)}…`;
                }
                return label;
            });

            return {
                backgroundColor: 'transparent',
                title: {
                    text: 'Distribusi Status Tracking',
                    left: 'center',
                    top: 12,
                    textStyle: {
                        fontSize: flags.isSmall ? 14 : 16,
                        fontWeight: 600,
                        color: '#2d3748'
                    }
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: { type: 'shadow' },
                    formatter: params => params
                        .map(item => `${item.marker} ${item.name}: ${item.value}`)
                        .join('<br/>')
                },
                grid: {
                    left: '8%',
                    right: '5%',
                    top: 60,
                    bottom: flags.isTablet ? 85 : 60,
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: trimmedCategories,
                    axisTick: { alignWithLabel: true },
                    axisLine: { lineStyle: { color: '#94a3b8' } },
                    axisLabel: {
                        color: '#475569',
                        rotate: flags.isTablet ? 30 : 15,
                        fontSize: flags.isSmall ? 9 : 12,
                        interval: 0
                    }
                },
                yAxis: {
                    type: 'value',
                    name: 'Jumlah',
                    nameTextStyle: { fontSize: flags.isSmall ? 10 : 12 },
                    axisLine: { show: false },
                    splitLine: { lineStyle: { color: 'rgba(148,163,184,0.25)' } },
                    axisLabel: {
                        color: '#475569',
                        fontSize: flags.isSmall ? 10 : 12
                    }
                },
                series: [{
                    name: 'Jumlah',
                    type: 'bar',
                    barWidth: flags.isTablet ? '40%' : '50%',
                    itemStyle: {
                        color: params => trackingBarColors[params.dataIndex % trackingBarColors.length],
                        borderRadius: [6, 6, 0, 0]
                    },
                    label: {
                        show: true,
                        position: 'top',
                        color: '#1e293b',
                        fontWeight: 600,
                        fontSize: flags.isSmall ? 10 : 12,
                        formatter: ({ value }) => value
                    },
                    data: trackingValues
                }]
            };
        }

        function getLastFiveMonths() {
            const arr = [];
            const now = new Date();
            for (let i = 4; i >= 0; i--) {
                const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
                const formatter = d.toLocaleDateString('id-ID', { month: 'short', year: '2-digit' });
                arr.push(formatter);
            }
            return arr;
        }

        function renderMonthlyBarChart(elId, seriesName, seriesData, barColor, existingInstance) {
            const el = document.getElementById(elId);
            if (!el) {
                return existingInstance;
            }

            let chart = existingInstance;
            if (!chart) {
                chart = echarts.init(el, null, { renderer: 'canvas', devicePixelRatio: window.devicePixelRatio || 1 });
            }

            const flags = getScreenFlags();
            const option = {
                backgroundColor: 'transparent',
                tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
                grid: {
                    left: '8%',
                    right: '5%',
                    top: 20,
                    bottom: flags.isTablet ? 60 : 45,
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: monthLabels,
                    axisLine: { lineStyle: { color: '#94a3b8' } },
                    axisLabel: {
                        color: '#475569',
                        fontSize: flags.isSmall ? 10 : 12
                    }
                },
                yAxis: {
                    type: 'value',
                    name: 'Jumlah',
                    nameTextStyle: {
                        color: '#475569',
                        padding: [0, 0, 5, 0],
                        fontSize: flags.isSmall ? 10 : 12
                    },
                    axisLine: { show: false },
                    splitLine: { lineStyle: { color: 'rgba(148,163,184,0.25)' } },
                    axisLabel: {
                        color: '#475569',
                        fontSize: flags.isSmall ? 10 : 12
                    }
                },
                series: [{
                    name: seriesName,
                    type: 'bar',
                    data: seriesData,
                    barGap: '10%',
                    barWidth: flags.isTablet ? '30%' : '35%',
                    itemStyle: {
                        borderRadius: [6, 6, 0, 0],
                        color: barColor
                    },
                    label: {
                        show: true,
                        position: 'top',
                        color: '#334155',
                        fontWeight: 600,
                        fontSize: flags.isSmall ? 10 : 12,
                        formatter: ({ value }) => value
                    }
                },
            {
                    name: seriesName,
                    type: 'bar',
                    data: seriesData,
                    barWidth: flags.isTablet ? '30%' : '35%',
                    itemStyle: {
                        borderRadius: [6, 6, 0, 0],
                        color: barColor
                    },
                    label: {
                        show: true,
                        position: 'top',
                        color: '#334155',
                        fontWeight: 600,
                        fontSize: flags.isSmall ? 10 : 12,
                        formatter: ({ value }) => value
                    }
                }]
            };

            chart.setOption(option, true);
            chart.resize();
            return chart;
        }
    });
    </script>
@stop
















