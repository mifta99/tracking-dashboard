@extends('adminlte::page')

@section('title', 'Raised Issues')

@section('content_header')
    <h1>Raised Issues</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header text-md font-weight-bold d-flex justify-content-between align-items-center" style="background-color: #ce8220; color: white;">
            <h3 class="card-title mb-0">Data Keluhan</h3>
            <div class="d-flex align-items-center">
                <div class="mr-2">
                    <span class="badge badge-light" style="color:#ce8220; background:#fff;">Filter & Manage</span>
                </div>
                @if(auth()->user() && auth()->user()->role->name === 'puskesmas')
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" id="btn-add-issue">
                        <i class="fas fa-plus"></i> Tambah Keluhan Baru
                    </button>
                </div>
                @endif
            </div>
        </div>
        <div class="card-body">
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

            <table class="table table-bordered table-striped table-sm text-sm" id="issues-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Provinsi</th>
                        <th>Kabupaten</th>
                        <th>Kecamatan</th>
                        <th>Nama Puskesmas</th>
                        <th>Tanggal Keluhan</th>
                        <th>PIC Puskesmas</th>
                        <th>Keluhan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $issue)
                    <tr 
                        data-province="{{ $issue->reporter->puskesmas->provinsi_name ?? '' }}" 
                        data-regency="{{ $issue->reporter->puskesmas->kabupaten_name ?? '' }}" 
                        data-district="{{ $issue->reporter->puskesmas->kecamatan_name ?? '' }}"
                        data-status="{{ $issue->status_id }}"
                    >
                        <td>{{ $issue->id }}</td>
                        <td>{{ $issue->reporter->puskesmas->provinsi_name ?? '-' }}</td>
                        <td>{{ $issue->reporter->puskesmas->kabupaten_name ?? '-' }}</td>
                        <td>{{ $issue->reporter->puskesmas->kecamatan_name ?? '-' }}</td>
                        <td>{{ $issue->reporter->puskesmas->name ?? 'N/A' }}</td>
                        <td data-date="{{ $issue->created_at->format('Y-m') }}">{{ $issue->created_at->format('d-m-Y') }}</td>
                        <td>{{ $issue->reporter->puskesmas->kepala  ?? 'N/A' }}</td>
                        <td style="max-width:250px; white-space:normal;">{{ $issue->reported_issue }}</td>
                        <td class="text-center">
                            @php
                                $statusClass = '';
                                $statusLabel = ucfirst(str_replace('_', ' ', $issue->statusKeluhan->status ?? 'N/A'));
                                switch($issue->status_id) {
                                    case 1: $statusClass = 'badge-danger'; break;
                                    case 2: $statusClass = 'badge-warning'; break;
                                    case 3: $statusClass = 'badge-success'; break;
                                    default: $statusClass = 'badge-secondary';
                                }
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="text-center">
                            <a href="" class="text-center btn btn-xs btn-outline-primary"><i class="fas fa-search"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <style>
        #issues-table thead th { white-space: nowrap; }
        #issues-table tbody td { vertical-align: middle; }
        .dataTables_wrapper .dataTables_filter input { border-radius:4px; }
        .badge { font-size: 11px; }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(function(){
            // Initialize DataTable
            const table = $('#issues-table').DataTable({
                pageLength: 25,
                order: [[0,'desc']],
                responsive: true,
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Data tidak ditemukan',
                    paginate: { next: '▶', previous: '◀' }
                },
                columnDefs: [
                    { targets: [1,2,3], visible: false }, // hide region columns but usable for filtering
                    { targets: 7, width: '25%' },
                    { targets: -1, orderable:false, searchable:false }
                ]
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