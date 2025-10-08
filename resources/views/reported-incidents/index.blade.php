@extends('adminlte::page')

@section('title', 'Reported Incidents')

@section('content_header')
    <h1>Reported Incidents</h1>
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content')
    <div class="card shadow-sm">
        <div class="card-header py-2 pr-1 d-flex align-items-center" style="background:#6f42c1;">
            <span class="section-title-bar text-white" style="font-size:.7rem;font-weight:600;letter-spacing:.5px;text-transform:uppercase;">Pelaporan Insiden</span>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm" id="reported-incidents-table">
                    <thead>
                        <tr>
                            <th style="font-size: 11pt;">No.</th>
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
            $('#reported-incidents-table').DataTable({
                processing: true,
                serverSide: false,
                responsive: true,
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
                order: [[1, 'desc']], // Sort by date descending
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
                    { data: 'tgl_kejadian', name: 'tgl_kejadian', width: '12%' },
                    { data: 'insiden', name: 'insiden', width: '25%' },
                    { data: 'tahapan', name: 'tahapan', width: '15%' },
                    { data: 'kategori_insiden', name: 'kategori_insiden', width: '15%' },
                    { data: 'status', name: 'status', width: '12%' },
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
                        targets: 2, // Insiden column
                        render: function (data, type, row) {
                            if (data && data.length > 80) {
                                return `<div style="max-width:250px; white-space:normal;">${data.substring(0, 80)}...</div>`;
                            }
                            return `<div style="max-width:250px; white-space:normal;">${data || '-'}</div>`;
                        }
                    },
                    {
                        targets: 5, // Status column
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
                        targets: 6, // Actions
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


        });
    </script>
@stop
