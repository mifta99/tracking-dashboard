@extends('adminlte::page')

@section('title', 'Detail Keluhan')

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
                <div class="card-header border-0" style="background-color: #ce8220; color: #fff;">
                    <h3 class="card-title mb-0">Rincian Keluhan</h3>
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
                                <tr><td>Tanggal Dilaporkan</td><td>{{ $reportedDate ?? '-' }}</td></tr>
                                <tr><td>Dilaporkan Oleh</td><td>{{ $reporter }}</td></tr>
                            </table>
                        </div>
                    </div>

                    <hr class="detail-divider">

                    <div class="row">
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <h5 class="section-title">Deskripsi Keluhan</h5>
                            <p class="mb-3 font-weight-bold text-dark">{{ $issue->reported_subject ?? '-' }}</p>
                            <p class="text-muted mb-0" style="white-space: pre-line;">{{ $issue->reported_issue ?? '-' }}</p>
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
                <div class="card-header border-0" style="background-color: #28a745; color: #fff;">
                    <h3 class="card-title mb-0">Tindak Lanjut</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <table class="table table-sm table-borderless table-kv mb-0">
                                <tr><td>Total Downtime</td><td>{{ $issue->total_downtime ?? '-' }}</td></tr>
                                <tr><td>Detail Tindak Lanjut</td><td>{{ $issue->detail_tindak_lanjut ?? '-' }}</td></tr>
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
                                <tr><td>Diproses Oleh</td><td>{{ $issue->processed_by ?? '-' }}</td></tr>
                                <tr><td>Tanggal Diproses</td><td>{{ optional($issue->processed_date)->translatedFormat('d F Y') ?? '-' }}</td></tr>
                                <tr><td>Diselesaikan Oleh</td><td>{{ $issue->resolved_by ?? '-' }}</td></tr>
                                <tr><td>Tanggal Selesai</td><td>{{ optional($issue->resolved_date)->translatedFormat('d F Y') ?? '-' }}</td></tr>
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
@stop

@section('css')
    <style>
        .table-kv td{padding:.35rem .25rem;vertical-align:top;font-size:.875rem;}
        .table-kv td:first-child{font-weight:600;width:230px;color:#212529;}
        .section-title-bar{font-size:.7rem;font-weight:600;letter-spacing:.5px;text-transform:uppercase;}
        .badge-status{font-size:.55rem;}
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
        .raised-issue-detail .badge {
            font-weight: 600;
            padding: 0.35rem 0.75rem;
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
});
</script>
@stop
