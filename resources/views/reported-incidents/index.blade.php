@extends('adminlte::page')

@section('title', 'Reported Incidents')

@section('content_header')
    <h1>Reported Incidents</h1>
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content')
    {{-- <div class="card">
        <div class="card-header">
            <h3 class="card-title">Reported Incidents List</h3>

        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="reported-incidents-table">
                <thead class="thead-light">
                    <tr>
                        <th rowspan="3" class="align-middle text-center" style="width:40px">NO</th>
                        <th rowspan="3" class="align-middle text-center" style="width:120px">TANGGAL KEJADIAN</th>
                        <th rowspan="3" class="align-middle text-center" style="width:150px">NAMA</th>
                        <th rowspan="3" class="align-middle text-center" style="width:120px">BAGIAN</th>
                        <th rowspan="3" class="align-middle text-center" style="width:150px">KRONOLOGIS KEJADIAN</th>
                        <th colspan="6" class="text-center">TINDAKAN KOREKSI</th>
                        <th colspan="6" class="text-center">TINDAKAN KOREKTIF</th>
                    </tr>
                    <tr>
                        <!-- Tindakan Koreksi columns -->
                        <th rowspan="2" class="align-middle text-center" style="width:120px">RENCANA TINDAKAN KOREKSI
                        </th>
                        <th rowspan="2" class="align-middle text-center" style="width:100px">PELAKSANA TINDAKAN</th>
                        <th rowspan="2" class="align-middle text-center" style="width:100px">TANGGAL SELESAI</th>
                        <th colspan="3" class="text-center">VERIFIKASI</th>
                        <!-- Tindakan Korektif columns -->
                        <th rowspan="2" class="align-middle text-center" style="width:120px">RENCANA TINDAKAN KOREKTIF
                        </th>
                        <th rowspan="2" class="align-middle text-center" style="width:100px">PELAKSANA TINDAKAN</th>
                        <th rowspan="2" class="align-middle text-center" style="width:100px">TANGGAL SELESAI</th>
                        <th colspan="3" class="text-center">VERIFIKASI</th>
                    </tr>
                    <tr>
                        <!-- Verifikasi sub-columns for Tindakan Koreksi -->
                        <th class="text-center" style="width:80px">HASIL</th>
                        <th class="text-center" style="width:100px">TANGGAL</th>
                        <th class="text-center" style="width:100px">PELAKSANA</th>
                        <!-- Verifikasi sub-columns for Tindakan Korektif -->
                        <th class="text-center" style="width:80px">HASIL</th>
                        <th class="text-center" style="width:100px">TANGGAL</th>
                        <th class="text-center" style="width:100px">PELAKSANA</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="17" class="text-center text-muted py-4">
                            <i class="fas fa-info-circle"></i> No incidents reported yet
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div> --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="h5 mb-0">Start of Incident</span>
            <button type="button" class="btn btn-sm btn-primary ml-auto" data-toggle="modal" data-target="#newIncidentModal">
                <i class="fas fa-plus"></i> New Incident
            </button>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0" id="reported-incidents-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width:160px">Tanggal</th>
                        <th class="text-center">Kategori Insiden</th>
                        <th class="text-center" style="width:140px">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $items = isset($reportedIncidents) ? $reportedIncidents : (isset($incidents) ? $incidents : collect());
                    @endphp
                    @forelse($items as $incident)
                        @php
                            $date = $incident->tanggal_kejadian ?? $incident->date ?? null;
                            $category = $incident->kategori_insiden ?? $incident->category ?? '-';
                            $status = $incident->status ?? 'unknown';
                            $badgeClass = [
                                'open' => 'danger',
                                'in_progress' => 'warning',
                                'closed' => 'success',
                            ][$status] ?? 'secondary';
                        @endphp
                        <tr>
                            <td class="text-center">{{ $date ? \Illuminate\Support\Carbon::parse($date)->translatedFormat('d-m-Y') : '-' }}</td>
                            <td>{{ $category }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ $badgeClass }}">
                                    {{ \Illuminate\Support\Str::of($status)->replace('_', ' ')->title() }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">
                                <i class="fas fa-info-circle"></i> No incidents reported yet
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- New Incident Modal -->
    <div class="modal fade" id="newIncidentModal" tabindex="-1" role="dialog" aria-labelledby="newIncidentModalLabel" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h4 class="modal-title" id="newIncidentModalLabel">
                        <i class="fas fa-plus-circle"></i> Lapor Insiden Baru
                    </h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="newIncidentForm" method="POST" action="{{ route('reported-incidents.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-info-circle"></i> Informasi Dasar
                                </h5>
                            </div>

                            <!-- Tanggal Kejadian -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tgl_kejadian"><i class="fas fa-calendar"></i> Tanggal Kejadian *</label>
                                    <input type="date" class="form-control" id="tgl_kejadian" name="tgl_kejadian" required>
                                </div>
                            </div>

                            <!-- Kategori Insiden -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kategori_id"><i class="fas fa-tags"></i> Kategori Insiden *</label>
                                    <select class="form-control" id="kategori_id" name="kategori_id" required>
                                        <option value="">Pilih Kategori Insiden</option>
                                        <!-- Options will be populated via AJAX or server-side -->
                                    </select>
                                </div>
                            </div>

                            <!-- Nama Korban -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_korban"><i class="fas fa-user"></i> Nama Korban *</label>
                                    <input type="text" class="form-control" id="nama_korban" name="nama_korban" required>
                                </div>
                            </div>

                            <!-- Bagian/Unit -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bagian"><i class="fas fa-building"></i> Bagian/Unit *</label>
                                    <input type="text" class="form-control" id="bagian" name="bagian" required>
                                </div>
                            </div>

                            <!-- Tahapan -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tahapan_id"><i class="fas fa-step-forward"></i> Tahapan</label>
                                    <select class="form-control" id="tahapan_id" name="tahapan_id">
                                        <option value="">Pilih Tahapan</option>
                                        <!-- Options will be populated via AJAX or server-side -->
                                    </select>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status_id"><i class="fas fa-flag"></i> Status</label>
                                    <select class="form-control" id="status_id" name="status_id">
                                        <option value="">Pilih Status</option>
                                        <!-- Options will be populated via AJAX or server-side -->
                                    </select>
                                </div>
                            </div>

                            <!-- Judul Insiden -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="insiden"><i class="fas fa-exclamation-triangle"></i> Judul Insiden *</label>
                                    <input type="text" class="form-control" id="insiden" name="insiden" required>
                                </div>
                            </div>

                            <!-- Kronologis -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="kronologis"><i class="fas fa-list-ol"></i> Kronologis Kejadian *</label>
                                    <textarea class="form-control" id="kronologis" name="kronologis" rows="4" required placeholder="Deskripsikan kronologis kejadian secara detail..."></textarea>
                                </div>
                            </div>

                            <!-- Dokumentasi -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="dokumentasi"><i class="fas fa-paperclip"></i> Dokumentasi (Optional)</label>
                                    <input type="file" class="form-control-file" id="dokumentasi" name="dokumentasi[]" multiple accept="image/*,application/pdf">
                                    <small class="form-text text-muted">Upload foto atau dokumen pendukung (JPG, PNG, PDF)</small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" form="newIncidentForm" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Insiden
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Load dropdown data when modal is opened
            $('#newIncidentModal').on('show.bs.modal', function () {
                loadDropdownData();
            });

            // Load dropdown data
            function loadDropdownData() {
                // Load Kategori Insiden
                $.ajax({
                    url: '{{ route("api.kategori-insiden") }}',
                    method: 'GET',
                    success: function(data) {
                        let options = '<option value="">Pilih Kategori Insiden</option>';
                        data.forEach(function(item) {
                            options += `<option value="${item.id}">${item.name}</option>`;
                        });
                        $('#kategori_id').html(options);
                    },
                    error: function() {
                        console.log('Failed to load kategori insiden');
                    }
                });

                // Load Tahapan
                $.ajax({
                    url: '{{ route("api.tahapan") }}',
                    method: 'GET',
                    success: function(data) {
                        let options = '<option value="">Pilih Tahapan</option>';
                        data.forEach(function(item) {
                            options += `<option value="${item.id}">${item.tahapan}</option>`;
                        });
                        $('#tahapan_id').html(options);
                    },
                    error: function() {
                        console.log('Failed to load tahapan');
                    }
                });

                // Load Status Insiden
                $.ajax({
                    url: '{{ route("api.status-insiden") }}',
                    method: 'GET',
                    success: function(data) {
                        let options = '<option value="">Pilih Status</option>';
                        data.forEach(function(item) {
                            options += `<option value="${item.id}">${item.name}</option>`;
                        });
                        $('#status_id').html(options);
                    },
                    error: function() {
                        console.log('Failed to load status insiden');
                    }
                });
            }

            // Handle form submission
            $('#newIncidentForm').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                // Show loading state
                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Sedang memproses laporan insiden',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Insiden berhasil dilaporkan',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            $('#newIncidentModal').modal('hide');
                            $('#newIncidentForm')[0].reset();
                            // Reload page or update table
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'Terjadi kesalahan saat menyimpan insiden';

                        if (xhr.status === 422) {
                            // Validation errors
                            let errors = xhr.responseJSON.errors;
                            let errorText = '';
                            Object.keys(errors).forEach(function(key) {
                                errorText += errors[key][0] + '\n';
                            });
                            errorMessage = errorText;
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: errorMessage
                        });
                    }
                });
            });

            // Set default date to today
            $('#tgl_kejadian').val(new Date().toISOString().split('T')[0]);
        });
    </script>
@stop
