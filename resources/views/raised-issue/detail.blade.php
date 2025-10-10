@extends('adminlte::page')

@section('title', 'Detail Keluhan')

@section('adminlte_css_pre')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Toastr CSS for toast notifications -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endsection

@section('content_header')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div class="mb-2 mb-md-0">
            <h1 class="mb-0" style="font-size: 24px;">Detail Keluhan</h1>
            <p class="text-muted mb-0">Puskesmas {{ optional($issue->puskesmas)->name ?? 'Puskesmas Tidak Diketahui' }}</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail Keluhan</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')
    @php
        $puskesmas = $issue->puskesmas;
        $district = optional($puskesmas)->district;
        $regency = optional($district)->regency;
        $province = optional($regency)->province;
        $reporter = $issue->reporter->name ?? 'Pelapor Tidak Diketahui';
        $pengiriman = optional($puskesmas)->pengiriman;
        $documentation = $issue->dokumentasiKeluhan ?? collect();
        $reportedDate = optional($issue->reported_date)->translatedFormat('d F Y');
        if (!$reportedDate && $issue->created_at) {
            $reportedDate = $issue->created_at->translatedFormat('d F Y');
        }
        $statusKey = \Illuminate\Support\Str::slug(optional($issue->statusKeluhan)->status ?? 'Open');
        $kategoriKey = \Illuminate\Support\Str::slug(optional($issue->kategoriKeluhan)->kategori ?? 'unknown');
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 raised-issue-detail">
                <div class="card-header border-0 d-flex align-items-center" style="background-color: #ce8220; color: #fff;">
                    <h3 class="card-title mb-0">Rincian Keluhan</h3>
                    @if(auth()->user() && auth()->user()->role->role_name == 'puskesmas' && $issue->status_id == 1)
                    <button class="btn btn-sm ml-auto" style="background-color: #ce8220; color: #fff;" data-toggle="modal" data-target="#editLaporanKeluhanModal">
                        <i class="fas fa-edit"></i> Edit Laporan Keluhan
                    </button>
                    @endif
                </div>
    <!-- Edit Laporan Keluhan Modal -->
    @if(auth()->user() && auth()->user()->role->role_name == 'puskesmas' && $issue->status_id == 1)
    <div class="modal fade" id="editLaporanKeluhanModal" tabindex="-1" role="dialog" aria-labelledby="editLaporanKeluhanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #ce8220; color: #fff;">
                    <h5 class="modal-title" id="editLaporanKeluhanModalLabel">Edit Laporan Keluhan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editLaporanKeluhanForm" action="{{ route('raised-issue.update-laporan', $issue->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="reported_subject">Ringkasan Keluhan</label>
                            <input type="text" class="form-control" id="reported_subject" name="reported_subject" value="{{ $issue->reported_subject }}" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_opsi_keluhan_id" class="required">Opsi Keluhan <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_opsi_keluhan_id" name="opsi_keluhan_id" required>
                                <option value="">-- Pilih Opsi Keluhan --</option>
                                @foreach($opsiKeluhan as $opsi)
                                <option value="{{ $opsi->id }}" data-kategori="{{ $opsi->kategori_keluhan_id }}" {{ $issue->opsi_keluhan_id == $opsi->id ? 'selected' : '' }}>
                                    {{ $opsi->opsi }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_reported_name" class="required">Nama Pelapor <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_reported_name" name="reported_name" value="{{ $issue->reported_name }}" maxlength="255" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_reported_hp" class="required">Nomor HP Pelapor <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_reported_hp" name="reported_hp" value="{{ $issue->reported_hp }}" maxlength="20" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="reported_issue">Deskripsi Detail Keluhan</label>
                            <textarea class="form-control" id="reported_issue" name="reported_issue" rows="4" required>{{ $issue->reported_issue }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="bukti_dokumentasi">Bukti Dokumentasi (bisa upload ulang, file gambar, multiple)</label>
                            <input type="file" class="form-control-file" id="bukti_dokumentasi" name="bukti_dokumentasi[]" accept="image/*" multiple>
                            <small class="form-text text-muted">Abaikan jika tidak ingin mengubah dokumentasi.</small>
                        </div>
                        @if($documentation->isNotEmpty())
                        <div class="mb-2">
                            <label>Dokumentasi Saat Ini:</label>
                            <div class="row">
                                @foreach($documentation as $index => $doc)
                                    @php
                                        $rawUrl = $doc->link_foto;
                                        $isAbsolute = $rawUrl && \Illuminate\Support\Str::startsWith($rawUrl, ['http://', 'https://']);
                                        $url = $isAbsolute ? $rawUrl : ($rawUrl ? asset('storage/' . $rawUrl) : null);
                                        $fileName = basename($rawUrl ?? '');
                                    @endphp
                                    <div class="col-md-3 col-4 mb-2">
                                        <img src="{{ $url }}" alt="Dokumentasi {{ $index + 1 }}" class="img-thumbnail" style="height:80px;object-fit:cover;">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn" style="background-color: #ce8220; color: #fff;">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
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
                                <tr><td>Tanggal Dilaporkan</td><td>{{ $reportedDate ?? '-' }}</td></tr>
                                {{-- <tr><td>Dilaporkan Oleh</td><td>{{ $reporter }}</td></tr>                                 --}}
                                <tr><td>Nama Pelapor</td><td>{{ $issue->reported_name ?? '-' }}</td></tr>
                                <tr><td>Nomor HP Pelapor</td><td>{{ $issue->reported_hp ?? '-' }}</td></tr>
                            </table>
                        </div>
                    </div>

                    <hr class="detail-divider">

                    <div class="row">
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <h5 class="section-title">Deskripsi Keluhan</h5>
                            <table class="table table-sm table-borderless table-kv mb-0 mt-4 mt-lg-0">
                                <tr><td>Opsi Keluhan</td><td>
                                    @if(optional($issue->opsiKeluhan)->opsi)
                                        @php $opsiKey = \Illuminate\Support\Str::slug(optional($issue->opsiKeluhan)->opsi); @endphp
                                        <span class="badge badge-pill badge-status badge-opsi-{{ $opsiKey }}">
                                            {{ optional($issue->opsiKeluhan)->opsi }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td></tr>
                                <tr><td>Ringkasan Keluhan</td><td>{{ $issue->reported_subject ?? '-' }}</td></tr>
                                <tr><td>Rincian</td><td>{{ $issue->reported_issue ?? '-' }}</td></tr>
                            </table>

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
                </div>
                <div class="card-footer bg-light border-0 d-flex justify-content-between align-items-center flex-column flex-md-row">
                    <div class="text-muted small mb-2 mb-md-0">Terakhir diperbarui: {{ optional($issue->updated_at)->locale('id')->diffForHumans() ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tindak Lanjut Card -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 raised-issue-detail">
                <div class="card-header border-0 d-flex align-items-center" style="background-color: #28a745; color: #fff;">
                    <h3 class="card-title mb-0">Tindak Lanjut</h3>
                    @if(auth()->user() && auth()->user()->role->role_name == 'endo')
                    <button class="btn btn-sm btn-success ml-auto" data-toggle="modal" data-target="#tindakLanjutModal">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <table class="table table-sm table-borderless table-kv mb-0">
                                <tr><td>Total Downtime</td><td>{{ $issue->total_downtime ? $issue->total_downtime . ' Hari' : '-' }} </td></tr>
                                <tr><td>Detail Tindak Lanjut</td><td>{{ $issue->action_taken ?? '-' }}</td></tr>
                                <tr><td>Catatan</td><td>{{ $issue->catatan ?? '-' }}</td></tr>
                                <tr><td>Status</td><td>
                                    <span class="badge badge-pill badge-status badge-status-{{ $statusKey }}">
                                        {{ optional($issue->statusKeluhan)->status ?? '-' }}
                                    </span>
                                </td></tr>
                                <tr><td>Kategori Keluhan</td><td>
                                    <span class="badge badge-pill badge-status badge-kategori-{{ $kategoriKey }}">
                                        {{ optional($issue->kategoriKeluhan)->kategori ?? '-' }}
                                    </span>
                                </td></tr>
                            </table>
                        </div>
                        <div class="col-lg-6">
                            <table class="table table-sm table-borderless table-kv mb-0 mt-4 mt-lg-0">
                                <tr><td>Diproses Oleh</td><td>{{ $issue->proceed_by ?? '-' }}</td></tr>
                                <tr><td>Tanggal Diproses</td><td>{{ optional($issue->proceed_date)->translatedFormat('d F Y') ?? '-' }}</td></tr>
                                <tr><td>Diselesaikan Oleh</td><td>{{ $issue->resolved_by ?? '-' }}</td></tr>
                                <tr><td>Tanggal Selesai</td><td>{{ optional($issue->resolved_date)->translatedFormat('d F Y') ?? '-' }}</td></tr>
                                <tr><td>Lampiran Dokumen Penyelesaian</td><td>
                                    @if($issue->doc_selesai)
                                        <a href="{{ asset('storage/' . $issue->doc_selesai) }}" target="_blank" class="text-primary">
                                            <i class="fas fa-file-download mr-1"></i>
                                            Lihat Dokumen
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

    <!-- Tindak Lanjut Edit Modal -->
    @if(auth()->user() && auth()->user()->role->role_name == 'endo')
    <div class="modal fade" id="tindakLanjutModal" tabindex="-1" role="dialog" aria-labelledby="tindakLanjutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="tindakLanjutModalLabel">Edit Tindak Lanjut</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="tindakLanjutForm" action="{{ route('raised-issue.update-tindak-lanjut', $issue->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="total_downtime">Total Downtime (Hari)</label>
                                    <input type="text" class="form-control" id="total_downtime" name="total_downtime" value="{{ $issue->total_downtime }}">
                                </div>
                                <div class="form-group">
                                    <label for="kategori_id">Kategori Keluhan</label>
                                    <select class="form-control" id="kategori_id" name="kategori_id">
                                        <option value="">Pilih Kategori</option>
                                        @foreach($kategoriKeluhan as $kategori)
                                        <option value="{{ $kategori->id }}" {{ $issue->kategori_id == $kategori->id ? 'selected' : '' }}>
                                            {{ $kategori->kategori }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="action_taken">Detail Tindak Lanjut</label>
                                    <textarea class="form-control" id="action_taken" name="action_taken" rows="3">{{ $issue->action_taken }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="catatan">Catatan</label>
                                    <textarea class="form-control" id="catatan" name="catatan" rows="2">{{ $issue->catatan }}</textarea>
                                </div>


                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proceed_by">Diproses Oleh</label>
                                    <input type="text" class="form-control" id="proceed_by" name="proceed_by" value="{{ $issue->proceed_by }}">
                                </div>
                                <div class="form-group">
                                    <label for="proceed_date">Tanggal Diproses</label>
                                    <input type="date" class="form-control" id="proceed_date" name="proceed_date" value="{{ optional($issue->proceed_date)->translatedFormat('Y-m-d') }}">
                                </div>
                                <div class="form-group">
                                    <label for="resolved_by">Diselesaikan Oleh</label>
                                    <input type="text" class="form-control" id="resolved_by" name="resolved_by" value="{{ $issue->resolved_by }}">
                                </div>
                                <div class="form-group">
                                    <label for="resolved_date">Tanggal Selesai</label>
                                    <input type="date" class="form-control" id="resolved_date" name="resolved_date" value="{{ optional($issue->resolved_date)->translatedFormat('Y-m-d') }}">
                                </div>
                                <div class="form-group">
                                    <label for="doc_selesai">Lampiran Dokumen Penyelesaian</label>
                                    <input type="file" class="form-control-file" id="doc_selesai" name="doc_selesai" accept="image/*,.pdf,.doc,.docx">
                                    <small class="form-text text-muted">Upload dokumen penyelesaian (gambar, PDF, Word)</small>
                                    @if($issue->doc_selesai)
                                    <div class="mt-2">
                                        <small class="text-muted">Dokumen saat ini: </small>
                                        <a href="{{ asset('storage/' . $issue->doc_selesai) }}" target="_blank" class="text-primary">
                                            {{ basename($issue->doc_selesai) }}
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@stop

@section('css')
    <style>
        .table-kv td{padding:.35rem .25rem;vertical-align:top;font-size:.875rem;}
        .table-kv td:first-child{font-weight:600;width:230px;color:#212529;}
        .section-title-bar{font-size:.7rem;font-weight:600;letter-spacing:.5px;text-transform:uppercase;}
        .badge-status{font-size:.875rem;}
        .raised-issue-detail .detail-list dt {
            font-weight: 600;
            color: #4a5568;
        }
        .raised-issue-detail .detail-list dd {
            color: #1f2937;
        }
        .raised-issue-detail .detail-divider {
            border-top: 1px dotted #cbd5e0;
            margin: 2rem 0 1.5rem;
        }
        .raised-issue-detail .section-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .raised-issue-detail .doc-card {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .raised-issue-detail .doc-card:hover {
            border-color: #ce8220;
            box-shadow: 0 4px 12px rgba(206, 130, 32, 0.15);
            transform: translateY(-2px);
        }
        .raised-issue-detail .doc-image-container {
            position: relative;
            height: 150px;
            overflow: hidden;
            border-radius: 12px 12px 0 0;
        }
        .raised-issue-detail .doc-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.2s ease;
        }
        .raised-issue-detail .doc-card:hover .doc-image {
            transform: scale(1.05);
        }
        .raised-issue-detail .doc-overlay {
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
        .raised-issue-detail .doc-card:hover .doc-overlay {
            opacity: 1;
        }
        .raised-issue-detail .doc-placeholder {
            height: 150px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            border-radius: 12px 12px 0 0;
        }
        .raised-issue-detail .empty-doc {
            display: inline-flex;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px dashed #cbd5e0;
            color: #6b7280;
        }
        .raised-issue-detail .badge-status-baru {
            background: rgba(255, 193, 7, 0.15);
            color: #b38301;
        }
        .raised-issue-detail .badge-status-proses {
            background: rgba(23, 162, 184, 0.15);
            color: #117a8b;
        }
        .raised-issue-detail .badge-status-selesai {
            background: rgba(40, 167, 69, 0.15);
            color: #1e7e34;
        }
        .raised-issue-detail .badge-status-open {
            background: rgba(108, 117, 125, 0.15);
            color: #495057;
        }
        .raised-issue-detail .badge-kategori-rendah {
            background: rgba(255, 193, 7, 0.15);
            color: #b38301;
        }
        .raised-issue-detail .badge-kategori-sedang {
            background: rgba(23, 162, 184, 0.15);
            color: #117a8b;
        }
        .raised-issue-detail .badge-kategori-kritis {
            background: rgba(220, 53, 69, 0.15);
            color: #721c24;
        }
        .raised-issue-detail .badge-kategori-unknown {
            background: rgba(108, 117, 125, 0.15);
            color: #495057;
        }
        /* Opsi Keluhan Badge Styles */
        .raised-issue-detail .badge-opsi,
        .raised-issue-detail [class*="badge-opsi-"] {
            background: rgba(106, 90, 205, 0.15);
            color: #6a5acd;
        }
        .raised-issue-detail .badge {
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            font-size: .875rem;
        }
        @media (max-width: 576px) {
            .raised-issue-detail .card-footer .btn {
                width: 100%;
            }
            .raised-issue-detail .card-footer .btn + .btn {
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

    // Automatic status update based on form fields
    function updateStatusPreview() {
        const proceedBy = $('#proceed_by').val();
        const proceedDate = $('#proceed_date').val();
        const resolvedBy = $('#resolved_by').val();
        const resolvedDate = $('#resolved_date').val();

        let statusText = 'Baru';
        let statusClass = 'badge-status-baru';

        if (resolvedBy && resolvedDate) {
            statusText = 'Selesai';
            statusClass = 'badge-status-selesai';
        } else if (proceedBy || proceedDate) {
            statusText = 'Proses';
            statusClass = 'badge-status-proses';
        }

        $('#status_preview').removeClass('badge-status-baru badge-status-proses badge-status-selesai')
                           .addClass(statusClass)
                           .text(statusText);
    }

    // Listen for changes in form fields
    $('#proceed_by, #proceed_date, #resolved_by, #resolved_date').on('input change', updateStatusPreview);

    // Initialize status preview when modal opens
    $('#tindakLanjutModal').on('shown.bs.modal', function() {
        updateStatusPreview();
    });

    // Handle form submission
    $('#tindakLanjutForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.text();

        // Show loading state
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

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
                    toastr.success(response.message || 'Tindak lanjut berhasil diperbarui');
                    $('#tindakLanjutModal').modal('hide');
                    // Reload page to show updated data
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error(response.message || 'Terjadi kesalahan');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join('<br>');
                }
                toastr.error(errorMessage);
            },
            complete: function() {
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Handle Opsi Keluhan change in Edit Laporan Keluhan modal
    $('#edit_opsi_keluhan_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const kategoriId = selectedOption.data('kategori');

        // Add hidden input for kategori_id or update if exists
        let kategoriInput = $('#edit_kategori_id_hidden');
        if (kategoriInput.length === 0) {
            $('<input>', {
                type: 'hidden',
                id: 'edit_kategori_id_hidden',
                name: 'kategori_id',
                value: kategoriId || ''
            }).appendTo('#editLaporanKeluhanForm');
        } else {
            kategoriInput.val(kategoriId || '');
        }
    });

    // Handle Edit Laporan Keluhan form submission
    $('#editLaporanKeluhanForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        // Ensure kategori_id is included
        const selectedOption = $('#edit_opsi_keluhan_id').find('option:selected');
        const kategoriId = selectedOption.data('kategori') || $('#edit_kategori_id_hidden').val() || '';
        formData.set('kategori_id', kategoriId);

        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.text();

        // Show loading state
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

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
                    toastr.success(response.message || 'Laporan keluhan berhasil diperbarui');
                    $('#editLaporanKeluhanModal').modal('hide');
                    // Reload page to show updated data
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error(response.message || 'Terjadi kesalahan');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join('<br>');
                }
                toastr.error(errorMessage);
            },
            complete: function() {
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Trigger opsi keluhan change event when edit modal opens to set initial kategori_id
    $('#editLaporanKeluhanModal').on('shown.bs.modal', function() {
        $('#edit_opsi_keluhan_id').trigger('change');
    });
});
</script>
@stop
