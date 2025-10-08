@extends('adminlte::page')

@section('title', 'Detail Insiden')

@section('adminlte_css_pre')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Toastr CSS for toast notifications -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endsection

@section('content_header')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div class="mb-2 mb-md-0">
            <h1 class="mb-0" style="font-size: 24px;">Detail Insiden</h1>
            <p class="text-muted mb-0">Puskesmas {{ optional($incident->puskesmas)->name ?? 'Puskesmas Tidak Diketahui' }}</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail Insiden</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')
    @php
        $puskesmas = $incident->puskesmas;
        $district = optional($puskesmas)->district;
        $regency = optional($district)->regency;
        $province = optional($regency)->province;
        $reporter = $incident->reporter->name ?? 'Pelapor Tidak Diketahui';
        $documentation = $incident->dokumentasiInsiden ?? collect();
        $reportedDate = optional($incident->tgl_kejadian)->translatedFormat('d F Y');
        if (!$reportedDate && $incident->created_at) {
            $reportedDate = $incident->created_at->translatedFormat('d F Y');
        }
        $tgl_selesai = optional($incident->tgl_selesai)->translatedFormat('d F Y');
        $statusKey = \Illuminate\Support\Str::slug(optional($incident->status)->status ?? 'Open');
        $kategoriKey = \Illuminate\Support\Str::slug(optional($incident->kategoriInsiden)->kategori ?? 'unknown');
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 raised-incident-detail">
                <div class="card-header border-0 d-flex justify-content-between align-items-center" style="background-color: #6f42c1; color: #fff;">
                    <h3 class="card-title mb-0">Rincian Insiden</h3>
                    @if(auth()->user() && auth()->user()->role->role_name == 'endo')
                    <button type="button" class="btn btn-sm btn-light ml-auto" style="background-color: #6f42c1; color: #fff;" data-toggle="modal" data-target="#editIncidentModal">
                        <i class="fas fa-edit"></i> Edit Insiden
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <table class="table table-sm table-borderless table-kv mb-0">
                                <tr><td>Nama Puskesmas</td><td>{{ optional($puskesmas)->name ?? '-' }}</td></tr>
                                <tr><td>Kecamatan</td><td>{{ optional($district)->name ?? '-' }}</td></tr>
                                <tr><td>Kabupaten / Kota</td><td>{{ optional($regency)->name ?? '-' }}</td></tr>
                                <tr><td>Provinsi</td><td>{{ optional($province)->name ?? '-' }}</td></tr>
                            </table>
                        </div>
                        <div class="col-lg-6">
                            <table class="table table-sm table-borderless table-kv mb-0 mt-4 mt-lg-0">
                                <tr><td>Tanggal Kejadian</td><td>{{ $reportedDate ?? '-' }}</td></tr>
                                <tr><td>Dilaporkan Oleh</td><td>{{ $reporter }}</td></tr>
                                <tr><td>Tahapan</td><td>
                                    @php
                                        $tahapanKey = \Illuminate\Support\Str::slug(optional($incident->tahapan)->tahapan ?? 'unknown');
                                    @endphp
                                    <span class="badge badge-pill badge-tahapan badge-tahapan-{{ $tahapanKey }}">
                                        {{ optional($incident->tahapan)->tahapan ?? '-' }}
                                    </span>
                                </td></tr>
                                <tr><td>Status</td><td>
                                    <span class="badge badge-pill badge-status badge-status-{{ $statusKey }}">
                                        {{ optional($incident->status)->status ?? '-' }}
                                    </span>
                                </td></tr>
                            </table>
                        </div>
                    </div>

                    <hr class="detail-divider">

                    <div class="row p-2">
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <h5 class="section-title">Deskripsi Insiden</h5>
                            <p class="mb-3 font-weight-bold text-dark">{{ $incident->insiden ?? '-' }}</p>
                            <p class="text-muted mb-0" style="white-space: pre-line;">{{ $incident->kronologis ?? '-' }}</p>

                            <div class="mt-3 border-top">
                                <h6 class="section-title mt-3">Detail Tambahan</h6>
                                <div class="row">
                                    <div class="col-12">
                                        <table class="table table-sm table-borderless table-kv mb-0">
                                            <tr><td>Nama Korban</td><td>{{ $incident->nama_korban ?? '-' }}</td></tr>
                                            <tr><td>Bagian/Unit:</td><td>{{ $incident->bagian ?? '-' }}</td></tr>
                                            <tr><td>Kategori Insiden</td><td>
                                                <span class="badge badge-pill badge-kategori badge-kategori-{{ $kategoriKey }}">
                                                    {{ optional($incident->kategoriInsiden)->kategori ?? '-' }}
                                                </span>
                                            </td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <h5 class="section-title">Bukti Dokumentasi</h5>
                            @if($documentation->isNotEmpty())
                                <div class="row">
                                    @foreach($documentation as $index => $doc)
                                        @php
                                            $rawUrl = $doc->link_foto;
                                            $isAbsolute = $rawUrl && \Illuminate\Support\Str::startsWith($rawUrl, ['http://', 'https://']);
                                            $url = $isAbsolute ? $rawUrl : ($rawUrl ? asset('storage/' . $rawUrl) : null);
                                            $fileName = basename($rawUrl ?? '');
                                        @endphp
                                        <div class="col-md-6 col-sm-4 mb-3">
                                            <div class="card doc-card">
                                                <div class="doc-image-container" onclick="openImageModal('{{ $url ?? '' }}', '{{ $fileName }}')">
                                                    @if($url)
                                                        <img src="{{ $url }}" alt="Dokumentasi {{ $index + 1 }}" class="doc-image">
                                                        <div class="doc-overlay">
                                                            <i class="fas fa-search-plus"></i>
                                                        </div>
                                                    @else
                                                        <div class="doc-placeholder">
                                                            <i class="far fa-image fa-2x text-muted"></i>
                                                            <p class="small text-muted mt-2">Gambar tidak tersedia</p>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="card-body p-2">
                                                    <p class="small text-muted mb-0 text-truncate" title="{{ $fileName }}">
                                                        {{ $fileName ?: 'Dokumen ' . ($index + 1) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-doc align-items-center">
                                    <i class="far fa-folder-open fa-2x text-muted mr-2"></i>
                                    <span class="text-muted">Belum ada dokumentasi yang diunggah.</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <hr class="detail-divider">

                    <div class="row p-2">
                        <h5 class="section-title">Penyelesaian</h5>
                        <table class="table table-sm table-borderless table-kv mb-0">
                            <tr><td>Tanggal Selesai </td><td>{{ $tgl_selesai ?? '-' }}</td></tr>
                            <tr><td>Tindakan</td><td>{{ $incident->tindakan ?? '-' }}</td></tr>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Edit Incident Modal -->
    @if(auth()->user() && auth()->user()->role->role_name == 'endo')
    <div class="modal fade" id="editIncidentModal" tabindex="-1" role="dialog" aria-labelledby="editIncidentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header text-white" style="background:#6f42c1;">
                    <h5 class="modal-title" id="editIncidentModalLabel">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Insiden
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editIncidentForm" method="POST" action="{{ route('reported-incidents.update', $incident->id) }}" enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf
                        @method('PATCH')

                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Petunjuk:</strong> Perbarui form di bawah untuk mengubah data insiden.
                        </div>

                        <div class="row">
                            <!-- Tanggal Kejadian -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_tgl_kejadian" class="required">Tanggal Kejadian <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="edit_tgl_kejadian" name="tgl_kejadian" value="{{ $incident->tgl_kejadian ? $incident->tgl_kejadian->format('Y-m-d') : '' }}" required>
                                </div>
                            </div>

                            <!-- Kategori Insiden -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_kategori_id" class="required">Kategori Insiden <span class="text-danger">*</span></label>
                                    <select class="form-control" id="edit_kategori_id" name="kategori_id" required>
                                        <option value="">Pilih Kategori Insiden</option>
                                        <!-- Options will be populated via AJAX -->
                                    </select>
                                </div>
                            </div>

                            <!-- Nama Korban -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_nama_korban" class="required">Nama Korban <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_nama_korban" name="nama_korban" value="{{ $incident->nama_korban }}" placeholder="Nama korban insiden" maxlength="255" required>
                                </div>
                            </div>

                            <!-- Bagian/Unit -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_bagian" class="required">Bagian/Unit <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_bagian" name="bagian" value="{{ $incident->bagian }}" placeholder="Bagian atau unit kerja" maxlength="255" required>
                                </div>
                            </div>

                            <!-- Tahapan -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="edit_tahapan_id" class="required">Tahapan <span class="text-danger">*</span></label>
                                    <select class="form-control" id="edit_tahapan_id" name="tahapan_id" required>
                                        <option value="">Pilih Tahapan</option>
                                        <!-- Options will be populated via AJAX -->
                                    </select>
                                </div>
                            </div>

                            <!-- Judul Insiden -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="edit_insiden" class="required">Judul Insiden <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_insiden" name="insiden" value="{{ $incident->insiden }}" placeholder="Masukkan judul / ringkasan insiden" maxlength="255" required>
                                    <small class="form-text text-muted">Maksimal 255 karakter</small>
                                </div>
                            </div>

                            <!-- Kronologis -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="edit_kronologis" class="required">Kronologis Kejadian <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="edit_kronologis" name="kronologis" rows="4" required placeholder="Deskripsikan kronologis kejadian secara detail..." maxlength="1000">{{ $incident->kronologis }}</textarea>
                                    <div class="d-flex justify-content-between">
                                        <small class="form-text text-muted">Deskripsikan secara detail kronologis terjadinya insiden</small>
                                        <small class="text-muted">
                                            <span id="edit-kronologis-char-count">{{ strlen($incident->kronologis ?? '') }}</span>/1000 karakter
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Tindakan -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="edit_tindakan">Tindakan</label>
                                    <textarea class="form-control" id="edit_tindakan" name="tindakan" rows="3" placeholder="Deskripsikan tindakan yang dilakukan untuk menyelesaikan insiden" maxlength="1000">{{ $incident->tindakan }}</textarea>
                                    <small class="form-text text-muted">Opsional - Deskripsikan tindakan penyelesaian insiden</small>
                                </div>
                            </div>

                            <!-- Tanggal Selesai -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="edit_tgl_selesai">Tanggal Selesai</label>
                                    <input type="date" class="form-control" id="edit_tgl_selesai" name="tgl_selesai" value="{{ $incident->tgl_selesai ? $incident->tgl_selesai->format('Y-m-d') : '' }}">
                                    <small class="form-text text-muted">Otomatis mengubah status menjadi "Selesai" jika diisi</small>
                                </div>
                            </div>

                            <!-- Dokumentasi -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="edit_dokumentasi">Dokumentasi Tambahan</label>
                                    <input type="file" class="form-control-file" id="edit_dokumentasi" name="dokumentasi[]" multiple accept="image/*,application/pdf">
                                    <small class="form-text text-muted">Upload foto atau dokumen pendukung tambahan (JPG, PNG, PDF) - Maksimal 5MB per file</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn text-white" style="background:#6f42c1;">
                            <i class="fas fa-save"></i> Perbarui Insiden
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Preview Dokumentasi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Preview" class="img-fluid rounded">
                </div>
                <div class="modal-footer">
                    <a id="downloadLink" href="" target="_blank" class="btn btn-primary">
                        <i class="fas fa-download mr-1"></i> Download
                    </a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table-kv td{padding:.35rem .25rem;vertical-align:top;font-size:.875rem;}
        .table-kv td:first-child{font-weight:600;width:230px;color:#212529;}
        .section-title-bar{font-size:.7rem;font-weight:600;letter-spacing:.5px;text-transform:uppercase;}
        .badge-status{font-size:.55rem;}
        .raised-incident-detail .detail-list dt {
            font-weight: 600;
            color: #4a5568;
        }
        .raised-incident-detail .detail-list dd {
            color: #1f2937;
        }
        .raised-incident-detail .detail-divider {
            border-top: 1px dotted #cbd5e0;
            margin: 2rem 0 1.5rem;
        }
        .raised-incident-detail .section-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .raised-incident-detail .doc-card {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .raised-incident-detail .doc-card:hover {
            border-color: #6f42c1;
            box-shadow: 0 4px 12px rgba(111, 66, 193, 0.15);
            transform: translateY(-2px);
        }
        .raised-incident-detail .doc-image-container {
            position: relative;
            height: 150px;
            overflow: hidden;
            border-radius: 12px 12px 0 0;
        }
        .raised-incident-detail .doc-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.2s ease;
        }
        .raised-incident-detail .doc-card:hover .doc-image {
            transform: scale(1.05);
        }
        .raised-incident-detail .doc-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease;
            color: white;
            font-size: 24px;
        }
        .raised-incident-detail .doc-card:hover .doc-overlay {
            opacity: 1;
        }
        .raised-incident-detail .doc-placeholder {
            height: 150px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            border-radius: 12px 12px 0 0;
        }
        .raised-incident-detail .empty-doc {
            display: inline-flex;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px dashed #cbd5e0;
            color: #6b7280;
        }
        .raised-incident-detail .badge-status-baru {
            background: rgba(255, 193, 7, 0.15);
            color: #b38301;
        }
        .raised-incident-detail .badge-status-proses {
            background: rgba(23, 162, 184, 0.15);
            color: #117a8b;
        }
        .raised-incident-detail .badge-status-selesai {
            background: rgba(40, 167, 69, 0.15);
            color: #1e7e34;
        }
        .raised-incident-detail .badge-status-open {
            background: rgba(108, 117, 125, 0.15);
            color: #495057;
        }
        /* Kematian */
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
        /* Unknown */
        .raised-incident-detail .badge-kategori-unknown {
            background: rgba(108, 117, 125, 0.15);
            color: #495057;
        }
        .raised-incident-detail .badge {
            font-weight: 600;
            padding: 0.35rem 0.75rem;
        }
        /* Tahapan badges */
        .raised-incident-detail .badge-tahapan-pengemasan {
            background: rgba(0, 136, 255, 0.15) !important; /* secondary tone */
            color: #0088ff !important;
        }

        .raised-incident-detail .badge-tahapan-dalam-pengiriman {
            background: rgba(23, 162, 184, 0.15); /* info tone */
            color: #117a8b;
        }

        .raised-incident-detail .badge-tahapan-penerimaan {
            background: rgba(0, 123, 255, 0.15); /* primary tone */
            color: #004085;
        }

        .raised-incident-detail .badge-tahapan-instalasi {
            background: rgba(255, 193, 7, 0.15); /* warning tone */
            color: #b38301;
        }

        .raised-incident-detail .badge-tahapan-uji-fungsi {
            background: rgba(128, 0, 128, 0.15); /* purple tone */
            color: #800080;
        }

        .raised-incident-detail .badge-tahapan-pelatihan-alat {
            background: rgba(52, 58, 64, 0.15); /* dark tone */
            color: #343a40;
        }

        .raised-incident-detail .badge-tahapan-aspak {
            background: rgba(40, 167, 69, 0.15); /* success tone */
            color: #1e7e34;
        }

        .raised-incident-detail .badge-tahapan-basto {
            background: rgba(220, 53, 69, 0.15); /* danger tone */
            color: #bd2130;
        }

        .raised-incident-detail .badge-tahapan-unknown {
            background: rgba(108, 117, 125, 0.15);
            color: #495057;
        }
        @media (max-width: 576px) {
            .raised-incident-detail .card-footer .btn {
                width: 100%;
            }
            .raised-incident-detail .card-footer .btn + .btn {
                margin-top: 0.5rem;
            }
        }
    </style>
@stop

@section('js')
<!-- Toastr JS for toast notifications -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
function openImageModal(imageUrl, fileName) {
    if (!imageUrl) return;

    $('#modalImage').attr('src', imageUrl);
    $('#imageModalLabel').text(fileName || 'Preview Dokumentasi');
    $('#downloadLink').attr('href', imageUrl);
    $('#imageModal').modal('show');
}

$(document).ready(function() {
    // Handle image load errors
    $('.doc-image').on('error', function() {
        $(this).closest('.doc-image-container').html(`
            <div class="doc-placeholder">
                <i class="far fa-image fa-2x text-muted"></i>
                <p class="small text-muted mt-2">Gambar tidak dapat dimuat</p>
            </div>
        `);
    });

    // Edit incident modal functionality
    $('#editIncidentModal').on('show.bs.modal', function () {
        loadEditDropdownData();
    });

    // Character counter for edit kronologis
    $('#edit_kronologis').on('input', function() {
        const current = $(this).val().length;
        $('#edit-kronologis-char-count').text(current);

        if (current > 1000) {
            $('#edit-kronologis-char-count').addClass('text-danger');
        } else {
            $('#edit-kronologis-char-count').removeClass('text-danger');
        }
    });

    // Auto-update status when tgl_selesai is filled
    $('#edit_tgl_selesai').on('change', function() {
        const tglSelesai = $(this).val();
        if (tglSelesai) {
            // Set status to "Selesai" (status_id = 2)
            $('#edit_status_id').val('2');
            toastr.info('Status otomatis diubah menjadi "Selesai" karena tanggal selesai diisi');
        }
    });

    // Load dropdown data for edit form
    function loadEditDropdownData() {
        // Load Kategori Insiden
        $.ajax({
            url: '{{ route("api.kategori-insiden") }}',
            type: 'GET',
            success: function(data) {
                let options = '<option value="">Pilih Kategori Insiden</option>';
                data.forEach(function(item) {
                    const selected = item.id == {{ $incident->kategori_id ?? 'null' }} ? 'selected' : '';
                    options += `<option value="${item.id}" ${selected}>${item.kategori}</option>`;
                });
                $('#edit_kategori_id').html(options);
            },
            error: function() {
                console.log('Failed to load kategori insiden');
            }
        });

        // Load Tahapan
        $.ajax({
            url: '{{ route("api.tahapan") }}',
            type: 'GET',
            success: function(data) {
                let options = '<option value="">Pilih Tahapan</option>';
                data.forEach(function(item) {
                    const selected = item.id == {{ $incident->tahapan_id ?? 'null' }} ? 'selected' : '';
                    options += `<option value="${item.id}" ${selected}>${item.tahapan}</option>`;
                });
                $('#edit_tahapan_id').html(options);
            },
            error: function() {
                console.log('Failed to load tahapan');
            }
        });

        // Load Status Insiden
        $.ajax({
            url: '{{ route("api.status-insiden") }}',
            type: 'GET',
            success: function(data) {
                let options = '<option value="">Pilih Status</option>';
                data.forEach(function(item) {
                    const selected = item.id == {{ $incident->status_id ?? 'null' }} ? 'selected' : '';
                    options += `<option value="${item.id}" ${selected}>${item.status}</option>`;
                });
                $('#edit_status_id').html(options);
            },
            error: function() {
                console.log('Failed to load status insiden');
            }
        });
    }

    // Handle edit form submission
    $('#editIncidentForm').on('submit', function(e) {
        e.preventDefault();

        const $submitBtn = $(this).find('button[type="submit"]');
        const originalHtml = $submitBtn.html();

        // Disable submit button and show loading
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memperbarui...');

        // Create FormData for file uploads
        const formData = new FormData(this);

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
                if (response.success) {
                    toastr.success(response.message || 'Insiden berhasil diperbarui');
                    $('#editIncidentModal').modal('hide');

                    // Reload page to show updated data
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    toastr.error(response.message || 'Terjadi kesalahan');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan saat memperbarui insiden';

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

                toastr.error(errorMessage);
            },
            complete: function() {
                // Re-enable submit button
                $submitBtn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // Reset form when edit modal closes
    $('#editIncidentModal').on('hidden.bs.modal', function() {
        $('#editIncidentForm')[0].reset();
        $('#edit-kronologis-char-count').text('0').removeClass('text-danger');
    });
});
</script>
@stop
