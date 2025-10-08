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
        $statusKey = \Illuminate\Support\Str::slug(optional($incident->status)->status ?? 'Open');
        $kategoriKey = \Illuminate\Support\Str::slug(optional($incident->kategoriInsiden)->kategori ?? 'unknown');
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 raised-incident-detail">
                <div class="card-header border-0" style="background-color: #6f42c1; color: #fff;">
                    <h3 class="card-title mb-0">Rincian Insiden</h3>
                </div>
                <div class="card-body">
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
                                <tr><td>Tahapan</td><td>{{ optional($incident->tahapan)->tahapan ?? '-' }}</td></tr>
                                <tr><td>Status</td><td>
                                    <span class="badge badge-pill badge-status badge-status-{{ $statusKey }}">
                                        {{ optional($incident->status)->status ?? '-' }}
                                    </span>
                                </td></tr>
                            </table>
                        </div>
                    </div>

                    <hr class="detail-divider">

                    <div class="row p-4">
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <h5 class="section-title">Deskripsi Insiden</h5>
                            <p class="mb-3 font-weight-bold text-dark">{{ $incident->insiden ?? '-' }}</p>
                            <p class="text-muted mb-0" style="white-space: pre-line;">{{ $incident->kronologis ?? '-' }}</p>

                            <div class="mt-3">
                                <h6 class="section-title">Detail Tambahan</h6>
                                <div class="row">
                                    <div class="col-12">
                                        <small class="text-muted font-weight-bold">Nama Korban:</small> {{ $incident->nama_korban ?? '-' }}<br>
                                        <small class="text-muted font-weight-bold">Bagian/Unit:</small> {{ $incident->bagian ?? '-' }}<br>
                                        <small class="text-muted font-weight-bold">Kategori:</small>
                                        <span class="badge badge-pill badge-status badge-kategori-{{ $kategoriKey }}">
                                            {{ optional($incident->kategoriInsiden)->kategori ?? '-' }}
                                        </span>
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
        .raised-incident-detail .badge-kategori-rendah {
            background: rgba(255, 193, 7, 0.15);
            color: #b38301;
        }
        .raised-incident-detail .badge-kategori-sedang {
            background: rgba(23, 162, 184, 0.15);
            color: #117a8b;
        }
        .raised-incident-detail .badge-kategori-kritis {
            background: rgba(220, 53, 69, 0.15);
            color: #721c24;
        }
        .raised-incident-detail .badge-kategori-unknown {
            background: rgba(108, 117, 125, 0.15);
            color: #495057;
        }
        .raised-incident-detail .badge {
            font-weight: 600;
            padding: 0.35rem 0.75rem;
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
});
</script>
@stop
