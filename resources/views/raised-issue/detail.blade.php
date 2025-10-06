@extends('adminlte::page')

@section('title', 'Detail Kendala')

@section('content_header')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div class="mb-2 mb-md-0">
            <h1 class="mb-0" style="font-size: 24px;">Detail Kendala</h1>
            <p class="text-muted mb-0">Kendala - {{ optional($issue->puskesmas)->name ?? 'Puskesmas Tidak Diketahui' }}</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('raised-issue.index') }}">Raised Issues</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail Kendala</li>
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
        $pengiriman = optional($puskesmas)->pengiriman;
        $documentation = $issue->dokumentasiKeluhan ?? collect();
        $reportedDate = optional($issue->reported_date)->translatedFormat('d F Y');
        if (!$reportedDate && $issue->created_at) {
            $reportedDate = $issue->created_at->translatedFormat('d F Y');
        }
        $statusKey = \Illuminate\Support\Str::slug(optional($issue->statusKeluhan)->status ?? 'Open');
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 raised-issue-detail">
                <div class="card-header border-0" style="background-color: #ce8220; color: #fff;">
                    <h3 class="card-title mb-0">Rincian Kendala</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <dl class="row detail-list mb-0">
                                <dt class="col-sm-5">Nama Puskesmas</dt>
                                <dd class="col-sm-7">{{ optional($puskesmas)->name ?? '-' }}</dd>

                                <dt class="col-sm-5">Kecamatan</dt>
                                <dd class="col-sm-7">{{ optional($district)->name ?? '-' }}</dd>

                                <dt class="col-sm-5">Kabupaten / Kota</dt>
                                <dd class="col-sm-7">{{ optional($regency)->name ?? '-' }}</dd>

                                <dt class="col-sm-5">Provinsi</dt>
                                <dd class="col-sm-7">{{ optional($province)->name ?? '-' }}</dd>
                            </dl>
                        </div>
                        <div class="col-lg-6">
                            <dl class="row detail-list mb-0 mt-4 mt-lg-0">
                                <dt class="col-sm-5">Tanggal Dilaporkan</dt>
                                <dd class="col-sm-7">{{ $reportedDate ?? '-' }}</dd>

                                <dt class="col-sm-5">PIC Puskesmas</dt>
                                <dd class="col-sm-7">{{ optional($puskesmas)->pic ?? '-' }}</dd>

                                <dt class="col-sm-5">Tanggal Laporan Diterima</dt>
                                <dd class="col-sm-7">{{ '-' }}</dd>

                                <dt class="col-sm-5">Status</dt>
                                <dd class="col-sm-7">
                                    <span class="badge badge-pill badge-status-{{ $statusKey }}">
                                        {{ optional($issue->statusKeluhan)->status ?? 'Open' }}
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <hr class="detail-divider">

                    <div class="row">
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <h5 class="section-title">Keluhan</h5>
                            <p class="mb-3 font-weight-bold text-dark">{{ $issue->reported_subject ?? '-' }}</p>
                            <p class="text-muted mb-0" style="white-space: pre-line;">{{ $issue->reported_issue ?? '-' }}</p>
                        </div>
                        <div class="col-lg-6">
                            <h5 class="section-title">Bukti Dokumentasi</h5>
                            @if($documentation->isNotEmpty())
                                <div class="d-flex flex-wrap">
                                    @foreach($documentation as $doc)
                                        @php
                                            $rawUrl = $doc->link_foto;
                                            $isAbsolute = $rawUrl && \Illuminate\Support\Str::startsWith($rawUrl, ['http://', 'https://']);
                                            $url = $isAbsolute ? $rawUrl : ($rawUrl ? \Illuminate\Support\Facades\Storage::url($rawUrl) : null);
                                        @endphp
                                        <a href="{{ $url ?? '#' }}" class="doc-thumb" target="{{ $url ? '_blank' : '_self' }}" @if(!$url) aria-disabled="true" @endif>
                                            <div class="thumb-inner">
                                                <i class="far fa-image fa-2x mb-2 text-muted"></i>
                                                <span class="small text-muted">Lihat Dokumen</span>
                                            </div>
                                        </a>
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
                    <div class="d-flex flex-column flex-sm-row">
                        <a href="{{ route('raised-issue.index') }}" class="btn btn-outline-secondary btn-sm mb-2 mb-sm-0 mr-sm-2">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
                        </a>
                        @if(auth()->user() && auth()->user()->id === $issue->reported_by)
                            <a href="{{ route('raised-issue.index') }}#keluhan-{{ $issue->id }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit mr-1"></i> Kelola Keluhan
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
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
        .raised-issue-detail .doc-thumb {
            width: 120px;
            height: 120px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            transition: all 0.2s ease;
            margin-right: 0.75rem;
            margin-bottom: 0.75rem;
        }
        .raised-issue-detail .doc-thumb:hover {
            border-color: #ce8220;
            box-shadow: 0 10px 20px rgba(206, 130, 32, 0.15);
        }
        .raised-issue-detail .doc-thumb[aria-disabled="true"] {
            pointer-events: none;
            opacity: 0.6;
        }
        .raised-issue-detail .doc-thumb .thumb-inner {
            text-align: center;
        }
        .raised-issue-detail .empty-doc {
            display: inline-flex;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px dashed #cbd5e0;
            color: #6b7280;
        }
        .raised-issue-detail .badge-status-open {
            background: rgba(78, 205, 196, 0.1);
            color: #1c7c6b;
        }
        .raised-issue-detail .badge-status-progress {
            background: rgba(255, 193, 7, 0.15);
            color: #b38301;
        }
        .raised-issue-detail .badge-status-closed {
            background: rgba(52, 211, 153, 0.15);
            color: #0f766e;
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
