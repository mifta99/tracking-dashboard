@extends('adminlte::page')

@section('title', 'Detail Puskesmas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center w-100">
        <h1 class="h4 mb-0">Puskesmas {{ $puskesmas->name }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0 small">
                <li class="breadcrumb-item"><a href="{{ route('verification-request.index') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail</li>
            </ol>
        </nav>
    </div>
@endsection

@section('css')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Toastr CSS for toast notifications -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <style>
        .table-kv td{padding:.35rem .25rem;vertical-align:top;font-size:.875rem;}
        .table-kv td:first-child{font-weight:600;width:230px;color:#212529;}
        .section-title-bar{font-size:.7rem;font-weight:600;letter-spacing:.5px;text-transform:uppercase;}
        .badge-status{font-size:.55rem;}
        /* Shipment status flow (bottom) */
        .shipment-status-flow-wrapper{max-width:880px;margin:1.5rem auto 0;}
        .shipment-status-flow{display:flex;justify-content:space-between;position:relative;}
        .shipment-status-flow .sf-step{flex:1;text-align:center;position:relative;padding:0 .5rem;}
        .shipment-status-flow .sf-step:not(:last-child):after{content:"";position:absolute;top:28px;left:50%;width:100%;height:8px;background:#e0e0e0;border-radius:4px;z-index:1;transition:background .4s;}
        .shipment-status-flow .sf-circle{width:56px;height:56px;border-radius:50%;background:#d9d9d9;margin:0 auto 8px;display:flex;align-items:center;justify-content:center;font-size:1.35rem;color:#6c757d;position:relative;z-index:2;box-shadow:0 0 0 4px #fff inset,0 2px 5px rgba(0,0,0,.15);transition:all .35s;}
        .shipment-status-flow .sf-label{font-size:.68rem;font-weight:600;letter-spacing:.6px;text-transform:uppercase;color:#666;}
        .shipment-status-flow .sf-step.pending .sf-label{color:#9aa0a6;font-weight:500;}
        .shipment-status-flow .sf-step.done .sf-circle,
        .shipment-status-flow .sf-step.active .sf-circle{background:#28a745;color:#fff;}
        .shipment-status-flow .sf-step.done:not(:last-child):after,
        .shipment-status-flow .sf-step.active:not(:last-child):after{background:#28a745;}
        .shipment-status-flow .sf-step.final.done .sf-circle{box-shadow:0 0 0 4px #fff inset,0 0 0 4px #28a745,0 2px 8px rgba(0,0,0,.3);}

        /* Verification Status Indicators */
        .shipment-status-flow .sf-step .verification-indicator {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            border: 2px solid #fff;
            z-index: 3;
        }

        /* Verified Steps - Blue ring and blue indicator */
        .shipment-status-flow .sf-step.verified-step .sf-circle {
            box-shadow: 0 0 0 4px #fff inset, 0 0 0 6px #007bff, 0 2px 8px rgba(0,0,0,.2);
        }
        .shipment-status-flow .sf-step.verified-step .verification-indicator {
            background: #007bff;
            color: white;
        }

        /* Revision Steps - Red ring and warning indicator */
        .shipment-status-flow .sf-step.revision-step .sf-circle {
            box-shadow: 0 0 0 4px #fff inset, 0 0 0 6px #dc3545, 0 2px 8px rgba(0,0,0,.2);
        }
        .shipment-status-flow .sf-step.revision-step .verification-indicator {
            background: #dc3545;
            color: white;
        }

        /* Pending Verification Steps - Orange ring and clock indicator */
        .shipment-status-flow .sf-step.pending-verification .sf-circle {
            box-shadow: 0 0 0 4px #fff inset, 0 0 0 6px #ffc107, 0 2px 8px rgba(0,0,0,.2);
        }
        .shipment-status-flow .sf-step.pending-verification .verification-indicator {
            background: #ffc107;
            color: #333;
        }

        /* Active and Done states with verification override */
        .shipment-status-flow .sf-step.active.verified-step .sf-circle,
        .shipment-status-flow .sf-step.done.verified-step .sf-circle {
            background: #28a745;
            color: #fff;
            box-shadow: 0 0 0 4px #fff inset, 0 0 0 6px #007bff, 0 2px 8px rgba(0,0,0,.3);
        }

        .shipment-status-flow .sf-step.active.verified-step .verification-indicator,
        .shipment-status-flow .sf-step.done.verified-step .verification-indicator {
            background: #007bff;
            color: white;
        }

        .shipment-status-flow .sf-step.active.revision-step .sf-circle,
        .shipment-status-flow .sf-step.done.revision-step .sf-circle {
            background: #28a745;
            color: #fff;
            box-shadow: 0 0 0 4px #fff inset, 0 0 0 6px #dc3545, 0 2px 8px rgba(0,0,0,.3);
        }

        .shipment-status-flow .sf-step.active.pending-verification .sf-circle,
        .shipment-status-flow .sf-step.done.pending-verification .sf-circle {
            background: #28a745;
            color: #fff;
            box-shadow: 0 0 0 4px #fff inset, 0 0 0 6px #ffc107, 0 2px 8px rgba(0,0,0,.3);
        }

        /* Verification Legend */
        .verification-legend {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #007bff;
        }

        .legend-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 4px;
            border: 2px solid #fff;
            box-shadow: 0 0 0 1px rgba(0,0,0,0.1);
        }

        .legend-indicator.verified-legend {
            background: #007bff;
        }

        .legend-indicator.revision-legend {
            background: #dc3545;
        }

        .legend-indicator.pending-legend {
            background: #ffc107;
        }

        @media (max-width:640px){
            .shipment-status-flow{flex-direction:column;align-items:stretch;}
            .shipment-status-flow .sf-step{padding:0 0 1.2rem 2.7rem;text-align:left;}
            .shipment-status-flow .sf-step:not(:last-child):after{left:28px;top:56px;width:8px;height:100%;}
            .shipment-status-flow .sf-circle{margin:0 0 6px;}
            .shipment-status-flow .sf-step .verification-indicator {
                top: 4px;
                right: 4px;
            }
            .verification-legend {
                flex-direction: column;
                align-items: flex-start;
            }
            .verification-legend small {
                margin-bottom: 4px;
            }
        }

        /* Verified switches - Blue styling */
        .custom-control-input:disabled:checked ~ .custom-control-label {
            color: #007bff;
            font-weight: 600;
        }
        .custom-control-input:disabled:checked ~ .custom-control-label::before {
            background-color: #007bff;
            border-color: #007bff;
        }

        /* Unverified disabled switches - Gray/inactive styling */
        .custom-control-input:disabled:not(:checked) ~ .custom-control-label {
            color: #6c757d;
            font-weight: normal;
        }
        .custom-control-input:disabled:not(:checked) ~ .custom-control-label::before {
            background-color: #e9ecef;
            border-color: #ced4da;
        }

        /* Ensure consistent header height even when buttons are not present */
        .card-header.d-flex.align-items-center {
            min-height: 46px; /* Minimum height to accommodate button size */
        }

        /* DataTable styling to match daftar-revisi */
        #keluhanTable {
            font-size: 0.875rem;
        }

        #keluhanTable thead th {
            font-size: 11pt;
            font-weight: 600;
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }

        #keluhanTable tbody td {
            font-size: 10pt;
            vertical-align: middle;
        }

        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* File preview styling */
        .file-preview-item {
            transition: transform 0.2s;
        }

        .file-preview-item:hover {
            transform: translateY(-2px);
        }

        .required {
            font-weight: 600;
        }

        /* Modal styling adjustments */
        #addIssueModal .modal-header {
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        #addIssueModal .alert-info {
            background-color: #e3f2fd;
            border-color: #2196f3;
            color: #1565c0;
        }
    </style>
@endsection

@section('content')
    @php $d = optional($puskesmas->district); $r = optional($d->regency); $pv = optional($r->province); $peng = optional($puskesmas->pengiriman); @endphp

    <div class="card mb-3 shadow-sm">
        <div class="card-header py-2 pr-1 text-white d-flex align-items-center" style="background:#ee8c0b;">
            <span class="section-title-bar" style="color: white;">Status Tahapan</span>
        </div>
        <div class="card-body p-3">
            @php
                // Determine current progress based on Tahapan table and pengiriman status
                $showTimeline = false;
                $currentStep = 0;
                $stepMeta = [];

                // Define variables needed for verification status
                $doc = optional($puskesmas->document);
                $uji = optional($puskesmas->ujiFungsi);

                // Build step metadata from Tahapan table with verification status
                if($tahapan && $tahapan->count() > 0) {
                    foreach($tahapan as $t) {
                        // Get verification status for each step
                        $verificationStatus = 'none'; // none, verified, revision, pending
                        $hasRevision = false;
                        $isVerified = false;

                        switch($t->tahap_ke) {
                            case 3: // Penerimaan -> is_verified_bast (documents table)
                                $isVerified = $doc && $doc->is_verified_bast;
                                $hasRevision = isset($revisions['bast']) && !$revisions['bast']->is_resolved;
                                break;
                            case 4: // Instalasi -> is_verified_instalasi (uji_fungsi table)
                                $isVerified = $uji && $uji->is_verified_instalasi;
                                $hasRevision = isset($revisions['instalasi']) && !$revisions['instalasi']->is_resolved;
                                break;
                            case 5: // Uji Fungsi -> is_verified_uji_fungsi (uji_fungsi table)
                                $isVerified = $uji && $uji->is_verified_uji_fungsi;
                                $hasRevision = isset($revisions['uji_fungsi']) && !$revisions['uji_fungsi']->is_resolved;
                                break;
                            case 6: // Pelatihan Alat -> is_verified_pelatihan (uji_fungsi table)
                                $isVerified = $uji && $uji->is_verified_pelatihan;
                                $hasRevision = isset($revisions['pelatihan']) && !$revisions['pelatihan']->is_resolved;
                                break;
                            case 7: // BASTO -> is_verified_basto (documents table)
                                $isVerified = $doc && $doc->is_verified_basto;
                                $hasRevision = isset($revisions['basto']) && !$revisions['basto']->is_resolved;
                                break;
                            case 8: // ASPAK -> is_verified_aspak (documents table)
                                $isVerified = $doc && $doc->is_verified_aspak;
                                $hasRevision = isset($revisions['aspak']) && !$revisions['aspak']->is_resolved;
                                break;
                        }

                        // Determine verification status - only for steps that are active or completed
                        if ($hasRevision) {
                            $verificationStatus = 'revision';
                        } elseif ($isVerified) {
                            $verificationStatus = 'verified';
                        } elseif ($t->tahap_ke >= 3 && $t->tahap_ke <= 8) {
                            // Only show pending if the step is reached (will be determined later based on current step)
                            $verificationStatus = 'pending';
                        }

                        $stepMeta[$t->tahap_ke] = [
                            'label' => $t->tahapan,
                            'icon' => $t->tahap_ke == 1 ? 'fas fa-box-open' :
                                     ($t->tahap_ke == 2 ? 'fas fa-truck' :
                                     ($t->tahap_ke == 3 ? 'fas fa-box' :
                                     ($t->tahap_ke == 4 ? 'fas fa-tools' :
                                     ($t->tahap_ke == 5 ? 'fas fa-pen-square' :
                                     ($t->tahap_ke == 6 ? 'fas fa-chalkboard-teacher' :
                                     ($t->tahap_ke == 7 ? 'fas fa-clipboard-check' : 'fas fa-check-circle')))))),
                            'verification_status' => $verificationStatus,
                            'is_verified' => $isVerified,
                            'has_revision' => $hasRevision
                        ];
                    }
                }

                // Determine current progress based on pengiriman data
                if($peng) {
                    $showTimeline = true;

                    // Set current step based on tahapan_id or progress conditions
                    if($peng->tahapan_id) {
                        $currentStep = $peng->tahapan_id;
                    } else {
                        $currentStep = 0;
                    }
                }
            @endphp

            @if($showTimeline && $currentStep > 0 && !empty($stepMeta))
                <div class="shipment-status-flow" aria-label="Project Progress">
                    @foreach($stepMeta as $i => $meta)
                        @php
                            $cls = 'pending';
                            if($i < $currentStep) {
                                $cls = 'done';
                            } elseif($i == $currentStep) {
                                $cls = ($currentStep == count($stepMeta)) ? 'done final' : 'active';
                            }

                            // Add verification status classes - only for active or completed steps
                            $verificationCls = '';
                            $showVerificationIndicator = false;

                            // Only show verification status if step is active or completed
                            if($i <= $currentStep && $meta['verification_status'] != 'none') {
                                switch($meta['verification_status']) {
                                    case 'verified':
                                        $verificationCls = 'verified-step';
                                        $showVerificationIndicator = true;
                                        break;
                                    case 'revision':
                                        $verificationCls = 'revision-step';
                                        $showVerificationIndicator = true;
                                        break;
                                    case 'pending':
                                        $verificationCls = 'pending-verification';
                                        $showVerificationIndicator = true;
                                        break;
                                }
                            }
                        @endphp
                        <div class="sf-step {{ $cls }} {{ $verificationCls }} {{ $i == count($stepMeta) ? 'final' : '' }}"
                             data-step="{{ $i }}"
                             data-verification="{{ $meta['verification_status'] }}"
                             title="{{ $meta['label'] }}{{ $showVerificationIndicator ? ' - ' . ucfirst($meta['verification_status']) : '' }}">
                            <div class="sf-circle">
                                <i class="{{ $meta['icon'] }}"></i>
                                @if($showVerificationIndicator)
                                    <div class="verification-indicator">
                                        @if($meta['verification_status'] == 'verified')
                                            <i class="fas fa-check-circle"></i>
                                        @elseif($meta['verification_status'] == 'revision')
                                            <i class="fas fa-exclamation-triangle"></i>
                                        @elseif($meta['verification_status'] == 'pending')
                                            <i class="fas fa-clock"></i>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="sf-label">{{ $meta['label'] }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">Tahapan Saat ini: <strong>{{ $stepMeta[$currentStep]['label'] ?? 'Unknown' }}</strong></small>
                        </div>
                        <div class="col-md-6 text-right">
                            <small class="text-muted">Progress: {{ $currentStep }}/{{ count($stepMeta) }}</small>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="verification-legend">
                                <small class="text-muted mr-3">
                                    <strong>Status Verifikasi:</strong>
                                </small>
                                <small class="text-muted mr-3">
                                    <span class="legend-indicator verified-legend"></span>
                                    <i class="fas fa-check-circle text-primary"></i> Terverifikasi
                                </small>
                                <small class="text-muted mr-3">
                                    <span class="legend-indicator revision-legend"></span>
                                    <i class="fas fa-exclamation-triangle text-danger"></i> Perlu Revisi
                                </small>
                                <small class="text-muted">
                                    <span class="legend-indicator pending-legend"></span>
                                    <i class="fas fa-clock text-warning"></i> Menunggu Verifikasi
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center text-muted py-4">
                    <i class="fas fa-info-circle fa-2x mb-2"></i>
                    <p class="mb-0">No progress data available</p>
                    <small>Progress will be shown once shipping information is added</small>
                </div>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card h-100 shadow-sm">
            <div class="card-header py-2 pr-1 bg-primary text-white d-flex align-items-center">
                <span class="section-title-bar">Informasi Puskesmas</span>
                @if(auth()->user() && auth()->user()->role->role_name == 'kemenkes')
                <button class="btn btn-sm btn-primary ml-auto" data-toggle="modal" data-target="#basicInfoModal">
                    <i class="fas fa-edit"></i> Edit
                </button>
                @endif
            </div>
            <div class="card-body p-3">
                <table class="table table-sm table-borderless table-kv mb-0">
                <tr><td>Provinsi</td><td>{{ $pv->name ?? '-' }}</td></tr>
                <tr><td>Kabupaten / Kota</td><td>{{ $r->name ?? '-' }}</td></tr>
                <tr><td>Kecamatan</td><td>{{ $d->name ?? '-' }}</td></tr>
                <tr><td>Nama Puskesmas</td><td>{{ $puskesmas->name }}</td></tr>
                <tr><td>PIC Puskesmas</td><td>{{ $puskesmas->pic ?? '-' }}</td></tr>
                <tr><td>No. HP PIC Puskesmas</td><td>{{ $puskesmas->no_hp ?? '-' }}</td></tr>
                <tr><td>No. HP Alternatif PIC Puskesmas</td><td>{{ $puskesmas->no_hp_alternatif ?? '-' }}</td></tr>
                <tr><td>Kepala Puskesmas</td><td>{{ $puskesmas->kepala ?? '-' }}</td></tr>
                <tr><td>PIC Dinas Kesehatan Kabupaten/Kota</td><td>{{ $puskesmas->pic_dinkes_kab ?? '-' }}</td></tr>
                <tr><td>PIC Dinas Kesehatan Provinsi</td><td>{{ $puskesmas->pic_dinkes_prov ?? '-' }}</td></tr>
                </table>
            </div>
            </div>
        </div>

        @if(auth()->user() && auth()->user()->role->role_name === 'kemenkes')
        <!-- Modal Basic Information -->
        <div class="modal fade" id="basicInfoModal" tabindex="-1" role="dialog"
            data-provinces-url="{{ route('api-puskesmas.provinces') }}"
            data-regencies-url="{{ route('api-puskesmas.regencies') }}"
            data-districts-url="{{ route('api-puskesmas.districts') }}"
            data-current-district-id="{{ $puskesmas->district_id }}"
            data-current-regency-id="{{ $d->regency->id ?? '' }}"
            data-current-province-id="{{ $pv->id ?? '' }}">
            <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
               <div class="modal-header bg-primary text-white">
               <h5 class="modal-title">Edit Informasi Puskesmas</h5>
               <button type="button" class="close text-white" data-dismiss="modal">
                  <span>&times;</span>
               </button>
               </div>
               <form id="basicInfoForm">
               @csrf
               <div class="modal-body">
                  <div class="mb-2 pb-1 border-bottom"><strong class="text-muted small">Lokasi</strong></div>
                  <div class="form-row">
                     <div class="form-group col-md-4">
                        <label class="small mb-1">Provinsi</label>
                        <select class="form-control form-control-sm" name="province_id" id="basic-province-select">
                            <option value="">-- Pilih Provinsi --</option>
                        </select>
                     </div>
                     <div class="form-group col-md-4">
                        <label class="small mb-1">Kabupaten / Kota</label>
                        <select class="form-control form-control-sm" name="regency_id" id="basic-regency-select" disabled>
                            <option value="">-- Pilih Kabupaten/Kota --</option>
                        </select>
                     </div>
                     <div class="form-group col-md-4">
                        <label class="small mb-1">Kecamatan</label>
                        <select class="form-control form-control-sm" name="district_id" id="basic-district-select" disabled>
                            <option value="">-- Pilih Kecamatan --</option>
                        </select>
                     </div>
                  </div>
                  <div class="mb-2 pb-1 border-bottom"><strong class="text-muted small">Identitas</strong></div>
                  <div class="form-row">
                     <div class="form-group col-md-6">
                        <label class="small mb-1">Nama Puskesmas</label>
                        <input type="text" class="form-control form-control-sm" name="name" value="{{ $puskesmas->name }}">
                     </div>
                     <div class="form-group col-md-6">
                        <label class="small mb-1">PIC Puskesmas</label>
                        <input type="text" class="form-control form-control-sm" name="pic" value="{{ $puskesmas->pic }}">
                     </div>
                     <div class="form-group col-md-6">
                        <label class="small mb-1">No. HP PIC Puskesmas</label>
                        <input type="text" class="form-control form-control-sm" name="no_hp" value="{{ $puskesmas->no_hp }}">
                     </div>
                     <div class="form-group col-md-6">
                        <label class="small mb-1">No. HP Alternatif PIC Puskesmas</label>
                        <input type="text" class="form-control form-control-sm" name="no_hp_alternatif" value="{{ $puskesmas->no_hp_alternatif }}">
                     </div>
                  </div>
                  <div class="form-row">
                     <div class="form-group col-md-6">
                        <label class="small mb-1">Kepala Puskesmas</label>
                        <input type="text" class="form-control form-control-sm" name="kepala" value="{{ $puskesmas->kepala }}">
                     </div>
                     <div class="form-group col-md-6">
                        <label class="small mb-1">PIC Dinkes Kabupaten/Kota</label>
                        <input type="text" class="form-control form-control-sm" name="pic_dinkes_kab" value="{{ $puskesmas->pic_dinkes_kab }}">
                     </div>
                     <div class="form-group col-md-6">
                        <label class="small mb-1">PIC Dinkes Provinsi</label>
                        <input type="text" class="form-control form-control-sm" name="pic_dinkes_prov" value="{{ $puskesmas->pic_dinkes_prov }}">
                     </div>
                  </div>
                  <small class="text-muted d-block mt-2">*Kolom yang tidak diubah biarkan kosong atau tetap nilainya.</small>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Save Changes</button>
               </div>
               </form>
            </div>
            </div>
        </div>
        @endif

        <div class="col-lg-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header py-2 pr-1 bg-success text-white d-flex align-items-center">
                    <span class="section-title-bar">Informasi Pengiriman</span>
                    @if(auth()->user() && auth()->user()->role->role_name == 'endo')
                    <button class="btn btn-sm btn-success ml-auto" data-toggle="modal" data-target="#deliveryModal"><i class="fas fa-edit"></i> Edit</button>
                    @endif
                </div>
                <div class="card-body p-3">
                    <table class="table table-sm table-borderless table-kv mb-0">
                        <tr><td>Tanggal Pengiriman</td><td>{{ optional($peng->tgl_pengiriman)->format('d F Y') ?? '-' }}</td></tr>
                        <tr><td>ETA</td><td>{{ optional($peng->eta)->format('d F Y') ?? '-' }}</td></tr>
                        <tr><td>RESI</td><td>{{ $peng->resi ?? '-' }}</td></tr>
                        <tr><td>Link Tracking</td><td>@if($peng && $peng->tracking_link)<a class="text-decoration-none" target="_blank" href="{{ $peng->tracking_link }}">View Here</a>@else - @endif</td></tr>
                        <tr><td>Serial Number</td><td>{{ $puskesmas->equipment->serial_number ?? '-' }}</td></tr>
                        <tr><td>Tanggal Diterima</td><td>{{ optional($peng->tgl_diterima)->format('d F Y') ?? '-' }}</td></tr>
                        <tr><td>Nama Penerima</td><td>{{ $peng->nama_penerima ?? '-' }}</td></tr>
                        <tr><td>Jabatan Penerima</td><td>{{ $peng->jabatan_penerima ?? '-' }}</td></tr>
                        <tr><td>Instansi Penerima</td><td>{{ $peng->instansi_penerima ?? '-' }}</td></tr>
                        <tr><td>Nomor HP Penerima</td><td>{{ $peng->nomor_penerima ?? '-' }}</td></tr>
                        <tr><td>Status Penerimaan</td><td>{{ ($peng && $peng->tgl_diterima) ? 'Diterima' : '-' }}</td></tr>
                        <tr><td>Bukti Tanda Terima</td><td>@if($peng && $peng->link_tanda_terima)<a class="text-decoration-none" target="_blank" href="{{ asset('storage/' . $peng->link_tanda_terima) }}">View Here</a>@else - @endif</td></tr>
                        <tr><td>Catatan</td><td>{{ $peng->catatan ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3 shadow-sm">
        <div class="card-header py-2 pr-1 text-white d-flex align-items-center" style="background:#3f0fa8;">
            <span class="section-title-bar">Uji Fungsi</span>
            @if(auth()->user() && auth()->user()->role->role_name == 'endo')
            <button class="btn btn-sm ml-auto" style="background:#3f0fa8; color:white;" data-toggle="modal" data-target="#ujiFungsiModal"><i class="fas fa-edit"></i> Edit</button>
            @endif
        </div>
        @php $uji = optional($puskesmas->ujiFungsi); @endphp
        <div class="card-body p-3">
            <div class="row  border-bottom pb-2 mb-3">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless table-kv mb-0">
                        <tr><td>Tanggal Instalasi</td><td>{{ optional($uji->tgl_instalasi)->format('d F Y') ?? '-' }}</td></tr>
                        <tr><td>Berita Acara Instalasi</td><td>@if($uji && $uji->doc_instalasi)<a class="text-decoration-none" target="_blank" href="{{ asset('storage/' . $uji->doc_instalasi) }}">View Here</a>@else - @endif</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless table-kv mb-0">
                        <tr>
                            <td>Verifikasi Berita Acara Instalasi</td>
                            <td>
                                <form id="verifInstalasiForm">
                                    @csrf
                                    <div class="form-group mb-0">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" name="is_verified_instalasi" id="verifiedInstalasi"
                                                {{ (auth()->user()->role->role_name !== 'kemenkes' || ($uji && $uji->is_verified_instalasi)) ? 'disabled' : '' }}
                                                {{ ($uji && $uji->is_verified_instalasi) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="verifiedInstalasi">
                                                {{ ($uji && $uji->is_verified_instalasi) ? 'Terverifikasi ' : 'Belum Terverifikasi' }}
                                            </label>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <tr {{ $uji->verified_at_instalasi ? '' : 'hidden' }}><td>Tanggal Verifikasi Instalasi</td><td>{{ $uji->verified_at_instalasi ? $uji->verified_at_instalasi->setTimezone('Asia/Jakarta')->format('d F Y H:i') . ' WIB' : '-' }}</td></tr>
                        <tr {{ $uji->is_verified_instalasi ? 'hidden' : '' }}>
                            @if($revisions['instalasi'])
                            <td>{{ $revisions['instalasi']->is_resolved ? 'Revisi Terselesaikan' : 'Catatan Revisi' }}</td>
                            <td>
                                <div class="{{ $revisions['instalasi']->is_resolved ? 'text-success' : 'text-danger' }}">
                                    {!! nl2br(e($revisions['instalasi']->catatan)) !!}<br>
                                    <small class="text-muted">Direvisi pada {{ $revisions['instalasi']->created_at->setTimezone('Asia/Jakarta')->format('d F Y H:i') }} WIB</small>
                                    @if($revisions['instalasi']->resolved_at)
                                        <br><small class="text-muted">Dokumen revisi telah diunggah ulang pada {{ $revisions['instalasi']->resolved_at->setTimezone('Asia/Jakarta')->format('d F Y H:i') }} WIB</small>
                                    @endif
                                </div>
                                @if(!$revisions['instalasi']->is_resolved && auth()->user() && auth()->user()->role->role_name == 'kemenkes')
                                    <button class="btn btn-sm mt-2 btn-danger revisi-btn"
                                                data-toggle="modal" data-target="#revisiModal"
                                                data-doc-type="doc_instalasi"
                                                data-doc-name="Berita Acara Instalasi"
                                                data-jenis-dokumen-id="3">
                                            <i class="fas fa-edit"></i> Edit Catatan Revisi
                                    </button>
                                @endif
                            </td>
                            @else
                                @if(auth()->user() && auth()->user()->role->role_name == 'kemenkes')
                                <td>Revisi Berita Acara Instalasi</td>
                                <td>
                                    <button class="btn btn-sm ml-auto btn-danger revisi-btn"
                                                data-toggle="modal" data-target="#revisiModal"
                                                data-doc-type="doc_instalasi"
                                                data-doc-name="Berita Acara Instalasi"
                                                data-jenis-dokumen-id="3">
                                            <i class="fas fa-edit"></i> Catatan Revisi
                                    </button>
                                </td>
                                @endif
                            @endif
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row  border-bottom pb-2 mb-3">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless table-kv mb-0">
                        <tr><td>Target Tanggal Uji Fungsi</td><td>{{ optional($uji->target_tgl_uji_fungsi)->format('d F Y') ?? '-' }}</td></tr>
                        <tr><td>Tanggal Uji Fungsi</td><td>{{ optional($uji->tgl_uji_fungsi)->format('d F Y') ?? '-' }}</td></tr>
                        <tr><td>Berita Acara Uji Fungsi</td><td>@if($uji && $uji->doc_uji_fungsi)<a class="text-decoration-none" target="_blank" href="{{ asset('storage/' . $uji->doc_uji_fungsi) }}">View Here</a>@else - @endif</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless table-kv mb-0">
                        <tr>
                            <td>Verifikasi Berita Acara Uji Fungsi</td>
                            <td>
                                <form id="verifUjiFungsiForm">
                                    @csrf
                                    <div class="form-group mb-0">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" name="is_verified_uji_fungsi" id="verifiedUjiFungsi"
                                                {{ (auth()->user()->role->role_name !== 'kemenkes' || ($uji && $uji->is_verified_uji_fungsi)) ? 'disabled' : '' }}
                                                {{ ($uji && $uji->is_verified_uji_fungsi) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="verifiedUjiFungsi">
                                                {{ ($uji && $uji->is_verified_uji_fungsi) ? 'Terverifikasi ' : 'Belum Terverifikasi' }}
                                            </label>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <tr {{ $uji->verified_at_uji_fungsi ? '' : 'hidden' }}><td>Tanggal Verifikasi Uji Fungsi</td><td>{{ $uji->verified_at_uji_fungsi ? $uji->verified_at_uji_fungsi->setTimezone('Asia/Jakarta')->format('d F Y H:i') . ' WIB' : '-' }}</td></tr>
                        <tr {{ $uji->is_verified_uji_fungsi ? 'hidden' : '' }}>
                            @if($revisions['uji_fungsi'])
                            <td>{{ $revisions['uji_fungsi']->is_resolved ? 'Revisi Terselesaikan' : 'Catatan Revisi' }}</td>
                            <td>
                                <div class="{{ $revisions['uji_fungsi']->is_resolved ? 'text-success' : 'text-danger' }}">
                                    {!! nl2br(e($revisions['uji_fungsi']->catatan)) !!}<br>
                                    <small class="text-muted">Direvisi pada {{ $revisions['uji_fungsi']->created_at->setTimezone('Asia/Jakarta')->format('d F Y H:i') }} WIB</small>
                                    @if($revisions['uji_fungsi']->resolved_at)
                                        <br><small class="text-muted">Dokumen revisi telah diunggah ulang pada {{ $revisions['uji_fungsi']->resolved_at->setTimezone('Asia/Jakarta')->format('d F Y H:i') }} WIB</small>
                                    @endif
                                </div>
                                @if(!$revisions['uji_fungsi']->is_resolved && auth()->user() && auth()->user()->role->role_name == 'kemenkes')
                                    <button class="btn btn-sm mt-2 btn-danger revisi-btn"
                                                data-toggle="modal" data-target="#revisiModal"
                                                data-doc-type="doc_uji_fungsi"
                                                data-doc-name="Berita Acara Uji Fungsi"
                                                data-jenis-dokumen-id="4">
                                            <i class="fas fa-edit"></i> Edit Catatan Revisi
                                    </button>
                                @endif
                            </td>
                            @else
                                @if(auth()->user() && auth()->user()->role->role_name == 'kemenkes')
                                <td>Revisi Berita Acara Uji Fungsi</td>
                                <td>
                                    <button class="btn btn-sm ml-auto btn-danger revisi-btn"
                                                data-toggle="modal" data-target="#revisiModal"
                                                data-doc-type="doc_uji_fungsi"
                                                data-doc-name="Berita Acara Uji Fungsi"
                                                data-jenis-dokumen-id="4">
                                            <i class="fas fa-edit"></i> Catatan Revisi
                                    </button>
                                </td>
                                @endif
                            @endif
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless table-kv mb-0">
                        <tr><td>Tanggal Pelatihan Alat</td><td>{{ optional($uji->tgl_pelatihan)->format('d F Y') ?? '-' }}</td></tr>
                        <tr><td>Berita Acara Pelatihan Alat</td><td>@if($uji && $uji->doc_pelatihan)<a class="text-decoration-none" target="_blank" href="{{ asset('storage/' . $uji->doc_pelatihan) }}">View Here</a>@else - @endif</td></tr>
                        <tr><td>Catatan</td><td>{{ $uji->catatan ?? '-' }}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless table-kv mb-0">
                        <tr>
                            <td>Verifikasi Berita Acara Pelatihan Alat</td>
                            <td>
                                <form id="verifPelatihanForm">
                                    @csrf
                                    <div class="form-group mb-0">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" name="is_verified_pelatihan" id="verifiedPelatihan"
                                                {{ (auth()->user()->role->role_name !== 'kemenkes' || ($uji && $uji->is_verified_pelatihan)) ? 'disabled' : '' }}
                                                {{ ($uji && $uji->is_verified_pelatihan) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="verifiedPelatihan">
                                                {{ ($uji && $uji->is_verified_pelatihan) ? 'Terverifikasi ' : 'Belum Terverifikasi' }}
                                            </label>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <tr {{ $uji->verified_at_pelatihan ? '' : 'hidden' }}><td>Tanggal Verifikasi Pelatihan Alat</td><td>{{ $uji->verified_at_pelatihan ? $uji->verified_at_pelatihan->setTimezone('Asia/Jakarta')->format('d F Y H:i') . ' WIB' : '-' }}</td></tr>
                        <tr {{ $uji->is_verified_pelatihan ? 'hidden' : '' }}>
                            @if($revisions['pelatihan'])
                            <td>{{ $revisions['pelatihan']->is_resolved ? 'Revisi Terselesaikan' : 'Catatan Revisi' }}</td>
                            <td>
                                <div class="{{ $revisions['pelatihan']->is_resolved ? 'text-success' : 'text-danger' }}">
                                    {!! nl2br(e($revisions['pelatihan']->catatan)) !!}<br>
                                    <small class="text-muted">Direvisi pada {{ $revisions['pelatihan']->created_at->setTimezone('Asia/Jakarta')->format('d F Y H:i') }} WIB</small>
                                    @if($revisions['pelatihan']->resolved_at)
                                        <br><small class="text-muted">Dokumen revisi telah diunggah ulang pada {{ $revisions['pelatihan']->resolved_at->setTimezone('Asia/Jakarta')->format('d F Y H:i') }} WIB</small>
                                    @endif
                                </div>
                                @if(!$revisions['pelatihan']->is_resolved && auth()->user() && auth()->user()->role->role_name == 'kemenkes')
                                    <button class="btn btn-sm mt-2 btn-danger revisi-btn"
                                                data-toggle="modal" data-target="#revisiModal"
                                                data-doc-type="doc_pelatihan"
                                                data-doc-name="Berita Acara Pelatihan Alat"
                                                data-jenis-dokumen-id="5">
                                            <i class="fas fa-edit"></i> Edit Catatan Revisi
                                    </button>
                                @endif
                            </td>
                            @else
                                @if(auth()->user() && auth()->user()->role->role_name == 'kemenkes')
                                <td>Revisi Berita Acara Pelatihan Alat</td>
                                <td>
                                    <button class="btn btn-sm ml-auto btn-danger revisi-btn"
                                                data-toggle="modal" data-target="#revisiModal"
                                                data-doc-type="doc_pelatihan"
                                                data-doc-name="Berita Acara Pelatihan Alat"
                                                data-jenis-dokumen-id="5">
                                            <i class="fas fa-edit"></i> Catatan Revisi
                                    </button>
                                </td>
                                @endif
                            @endif
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @php $doc = optional($puskesmas->document); @endphp
    <div class="card shadow-sm mb-4">
        <div class="card-header py-2 pr-1 bg-secondary text-white d-flex align-items-center">
            <span class="section-title-bar">Dokumen</span>
            @if(auth()->user() && auth()->user()->role->role_name == 'endo')
            <button class="btn btn-sm btn-secondary ml-auto" data-toggle="modal" data-target="#documentsModal"><i class="fas fa-edit"></i> Edit</button>
            @endif
        </div>
        <div class="card-body p-3">
            <div class="row border-bottom pb-2 mb-3">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless table-kv mb-0">
                        <tr><td>Berita Acara Kalibrasi</td><td>@if($doc && $doc->kalibrasi)<a class="text-decoration-none" target="_blank" href="{{ asset('storage/' . $doc->kalibrasi) }}">View Here</a>@else - @endif</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless table-kv mb-0">
                        <tr>
                            <td>Verifikasi Berita Acara Kalibrasi</td>
                            <td>
                                <form id="verifKalibrasiForm">
                                    @csrf
                                    <div class="form-group mb-0">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" name="is_verified_kalibrasi" id="verifiedKalibrasi"
                                                {{ (auth()->user()->role->role_name !== 'kemenkes' || ($doc && $doc->is_verified_kalibrasi)) ? 'disabled' : '' }}
                                                {{ ($doc && $doc->is_verified_kalibrasi) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="verifiedKalibrasi">
                                                {{ ($doc && $doc->is_verified_kalibrasi) ? 'Terverifikasi ' : 'Belum Terverifikasi' }}
                                            </label>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <tr {{ ($doc && $doc->verified_at_kalibrasi) ? '' : 'hidden' }}><td>Tanggal Verifikasi Kalibrasi</td><td>{{ ($doc && $doc->verified_at_kalibrasi) ? $doc->verified_at_kalibrasi->setTimezone('Asia/Jakarta')->format('d F Y H:i') . ' WIB' : '-' }}</td></tr>
                        <tr {{ ($doc && $doc->is_verified_kalibrasi) ? 'hidden' : '' }}>
                            @if($revisions['kalibrasi'])
                            <td>{{ $revisions['kalibrasi']->is_resolved ? 'Revisi Terselesaikan' : 'Catatan Revisi' }}</td>
                            <td>
                                <div class="{{ $revisions['kalibrasi']->is_resolved ? 'text-success' : 'text-danger' }}">
                                    {!! nl2br(e($revisions['kalibrasi']->catatan)) !!}<br>
                                    <small class="text-muted">Direvisi pada {{ $revisions['kalibrasi']->created_at->setTimezone('Asia/Jakarta')->format('d F Y H:i') }} WIB</small>
                                    @if($revisions['kalibrasi']->resolved_at)
                                        <br><small class="text-muted">Dokumen revisi telah diunggah ulang pada {{ $revisions['kalibrasi']->resolved_at->setTimezone('Asia/Jakarta')->format('d F Y H:i') }} WIB</small>
                                    @endif
                                </div>
                                @if(!$revisions['kalibrasi']->is_resolved && auth()->user() && auth()->user()->role->role_name == 'kemenkes')
                                    <button class="btn btn-sm mt-2 btn-danger revisi-btn"
                                                data-toggle="modal" data-target="#revisiModal"
                                                data-doc-type="kalibrasi"
                                                data-doc-name="Berita Acara Kalibrasi"
                                                data-jenis-dokumen-id="1">
                                            <i class="fas fa-edit"></i> Edit Catatan Revisi
                                    </button>
                                @endif
                            </td>
                            @else
                                @if(auth()->user() && auth()->user()->role->role_name == 'kemenkes')
                                <td>Revisi Berita Acara Kalibrasi</td>
                                <td>
                                    <button class="btn btn-sm ml-auto btn-danger revisi-btn"
                                                data-toggle="modal" data-target="#revisiModal"
                                                data-doc-type="kalibrasi"
                                                data-doc-name="Berita Acara Kalibrasi"
                                                data-jenis-dokumen-id="1">
                                            <i class="fas fa-edit"></i> Catatan Revisi
                                    </button>
                                </td>
                                @endif
                            @endif
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row border-bottom pb-2 mb-3">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless table-kv mb-0">
                        <tr><td>Berita Acara BAST</td><td>@if($doc && $doc->bast)<a class="text-decoration-none" target="_blank" href="{{ asset('storage/' . $doc->bast) }}">View Here</a>@else - @endif</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless table-kv mb-0">
                        <tr>
                            <td>Verifikasi Berita Acara BAST</td>
                            <td>
                                <form id="verifBastForm">
                                    @csrf
                                    <div class="form-group mb-0">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" name="is_verified_bast" id="verifiedBast"
                                                {{ (auth()->user()->role->role_name !== 'kemenkes' || ($doc && $doc->is_verified_bast)) ? 'disabled' : '' }}
                                                {{ ($doc && $doc->is_verified_bast) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="verifiedBast">
                                                {{ ($doc && $doc->is_verified_bast) ? 'Terverifikasi ' : 'Belum Terverifikasi' }}
                                            </label>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <tr {{ ($doc && $doc->verified_at_bast) ? '' : 'hidden' }}><td>Tanggal Verifikasi BAST</td><td>{{ ($doc && $doc->verified_at_bast) ? $doc->verified_at_bast->setTimezone('Asia/Jakarta')->format('d F Y H:i') . ' WIB' : '-' }}</td></tr>
                        <tr {{ ($doc && $doc->is_verified_bast) ? 'hidden' : '' }}>
                            @if($revisions['bast'])
                            <td>{{ $revisions['bast']->is_resolved ? 'Revisi Terselesaikan' : 'Catatan Revisi' }}</td>
                            <td>
                                <div class="{{ $revisions['bast']->is_resolved ? 'text-success' : 'text-danger' }}">
                                    {!! nl2br(e($revisions['bast']->catatan)) !!}<br>
                                    <small class="text-muted">Direvisi pada {{ $revisions['bast']->created_at->setTimezone('Asia/Jakarta')->format('d F Y H:i') }} WIB</small>
                                    @if($revisions['bast']->resolved_at)
                                        <br><small class="text-muted">Dokumen revisi telah diunggah ulang pada {{ $revisions['bast']->resolved_at->setTimezone('Asia/Jakarta')->format('d F Y H:i') }} WIB</small>
                                    @endif
                                </div>
                                @if(!$revisions['bast']->is_resolved && auth()->user() && auth()->user()->role->role_name == 'kemenkes')
                                    <button class="btn btn-sm mt-2 btn-danger revisi-btn"
                                                data-toggle="modal" data-target="#revisiModal"
                                                data-doc-type="bast"
                                                data-doc-name="Berita Acara BAST"
                                                data-jenis-dokumen-id="2">
                                            <i class="fas fa-edit"></i> Edit Catatan Revisi
                                    </button>
                                @endif
                            </td>
                            @else
                                @if(auth()->user() && auth()->user()->role->role_name == 'kemenkes')
                                <td>Revisi Berita Acara BAST</td>
                                <td>
                                    <button class="btn btn-sm ml-auto btn-danger revisi-btn"
                                                data-toggle="modal" data-target="#revisiModal"
                                                data-doc-type="bast"
                                                data-doc-name="Berita Acara BAST"
                                                data-jenis-dokumen-id="2">
                                            <i class="fas fa-edit"></i> Catatan Revisi
                                    </button>
                                </td>
                                @endif
                            @endif
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row border-bottom pb-2 mb-3">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless table-kv mb-0">
                        <tr><td>Berita Acara BASTO</td><td>@if($doc && $doc->basto)<a class="text-decoration-none" target="_blank" href="{{ asset('storage/' . $doc->basto) }}">View Here</a>@else - @endif</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless table-kv mb-0">
                        <tr>
                            <td>Verifikasi Berita Acara BASTO</td>
                            <td>
                                <form id="verifBastoForm">
                                    @csrf
                                    <div class="form-group mb-0">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" name="is_verified_basto" id="verifiedBasto"
                                                {{ (auth()->user()->role->role_name !== 'kemenkes' || ($doc && $doc->is_verified_basto)) ? 'disabled' : '' }}
                                                {{ ($doc && $doc->is_verified_basto) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="verifiedBasto">
                                                {{ ($doc && $doc->is_verified_basto) ? 'Terverifikasi ' : 'Belum Terverifikasi' }}
                                            </label>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <tr {{ ($doc && $doc->verified_at_basto) ? '' : 'hidden' }}><td>Tanggal Verifikasi BASTO</td><td>{{ ($doc && $doc->verified_at_basto) ? $doc->verified_at_basto->setTimezone('Asia/Jakarta')->format('d F Y H:i') . ' WIB' : '-' }}</td></tr>
                        <tr {{ ($doc && $doc->is_verified_basto) ? 'hidden' : '' }}>
                            @if($revisions['basto'])
                            <td>{{ $revisions['basto']->is_resolved ? 'Revisi Terselesaikan' : 'Catatan Revisi' }}</td>
                            <td>
                                <div class="{{ $revisions['basto']->is_resolved ? 'text-success' : 'text-danger' }}">
                                    {!! nl2br(e($revisions['basto']->catatan)) !!}<br>
                                    <small class="text-muted">Direvisi pada {{ $revisions['basto']->created_at->setTimezone('Asia/Jakarta')->format('d F Y H:i') }} WIB</small>
                                    @if($revisions['basto']->resolved_at)
                                        <br><small class="text-muted">Dokumen revisi telah diunggah ulang pada {{ $revisions['basto']->resolved_at->setTimezone('Asia/Jakarta')->format('d F Y H:i') }} WIB</small>
                                    @endif
                                </div>
                                @if(!$revisions['basto']->is_resolved && auth()->user() && auth()->user()->role->role_name == 'kemenkes')
                                    <button class="btn btn-sm mt-2 btn-danger revisi-btn"
                                                data-toggle="modal" data-target="#revisiModal"
                                                data-doc-type="basto"
                                                data-doc-name="Berita Acara BASTO"
                                                data-jenis-dokumen-id="6">
                                            <i class="fas fa-edit"></i> Edit Catatan Revisi
                                    </button>
                                @endif
                            </td>
                            @else
                                @if(auth()->user() && auth()->user()->role->role_name == 'kemenkes')
                                <td>Revisi Berita Acara BASTO</td>
                                <td>
                                    <button class="btn btn-sm ml-auto btn-danger revisi-btn"
                                                data-toggle="modal" data-target="#revisiModal"
                                                data-doc-type="basto"
                                                data-doc-name="Berita Acara BASTO"
                                                data-jenis-dokumen-id="6">
                                            <i class="fas fa-edit"></i> Catatan Revisi
                                    </button>
                                </td>
                                @endif
                            @endif
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless table-kv mb-0">
                        <tr><td>Berita Acara ASPAK</td><td>@if($doc && $doc->aspak)<a class="text-decoration-none" target="_blank" href="{{ asset('storage/' . $doc->aspak) }}">View Here</a>@else - @endif</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless table-kv mb-0">
                        <tr>
                            <td>Verifikasi Berita Acara ASPAK</td>
                            <td>
                                <form id="verifAspakForm">
                                    @csrf
                                    <div class="form-group mb-0">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" name="is_verified_aspak" id="verifiedAspak"
                                                {{ (auth()->user()->role->role_name !== 'kemenkes' || ($doc && $doc->is_verified_aspak)) ? 'disabled' : '' }}
                                                {{ ($doc && $doc->is_verified_aspak) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="verifiedAspak">
                                                {{ ($doc && $doc->is_verified_aspak) ? 'Terverifikasi ' : 'Belum Terverifikasi' }}
                                            </label>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <tr {{ ($doc && $doc->verified_at_aspak) ? '' : 'hidden' }}><td>Tanggal Verifikasi ASPAK</td><td>{{ ($doc && $doc->verified_at_aspak) ? $doc->verified_at_aspak->setTimezone('Asia/Jakarta')->format('d F Y H:i') . ' WIB' : '-' }}</td></tr>
                        <tr {{ ($doc && $doc->is_verified_aspak) ? 'hidden' : '' }}>
                            @if($revisions['aspak'])
                            <td>{{ $revisions['aspak']->is_resolved ? 'Revisi Terselesaikan' : 'Catatan Revisi' }}</td>
                            <td>
                                <div class="{{ $revisions['aspak']->is_resolved ? 'text-success' : 'text-danger' }}">
                                    {!! nl2br(e($revisions['aspak']->catatan)) !!}<br>
                                    <small class="text-muted">Direvisi pada {{ $revisions['aspak']->created_at->setTimezone('Asia/Jakarta')->format('d F Y H:i') }} WIB</small>
                                    @if($revisions['aspak']->resolved_at)
                                        <br><small class="text-muted">Dokumen revisi telah diunggah ulang pada {{ $revisions['aspak']->resolved_at->setTimezone('Asia/Jakarta')->format('d F Y H:i') }} WIB</small>
                                    @endif
                                </div>
                                @if(!$revisions['aspak']->is_resolved && auth()->user() && auth()->user()->role->role_name == 'kemenkes')
                                    <button class="btn btn-sm mt-2 btn-danger revisi-btn"
                                                data-toggle="modal" data-target="#revisiModal"
                                                data-doc-type="aspak"
                                                data-doc-name="Berita Acara ASPAK"
                                                data-jenis-dokumen-id="7">
                                            <i class="fas fa-edit"></i> Edit Catatan Revisi
                                    </button>
                                @endif
                            </td>
                            @else
                                @if(auth()->user() && auth()->user()->role->role_name == 'kemenkes')
                                <td>Revisi Berita Acara ASPAK</td>
                                <td>
                                    <button class="btn btn-sm ml-auto btn-danger revisi-btn"
                                                data-toggle="modal" data-target="#revisiModal"
                                                data-doc-type="aspak"
                                                data-doc-name="Berita Acara ASPAK"
                                                data-jenis-dokumen-id="7">
                                            <i class="fas fa-edit"></i> Catatan Revisi
                                    </button>
                                </td>
                                @endif
                            @endif
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tracking Delivery Orders -->
    @if(!empty($peng->resi))
    <div class="card shadow-sm mb-4" id="trackingCard" data-initial-do="{{ $peng->resi }}">
        <div class="card-header py-2 pr-1 bg-info text-white d-flex align-items-center">
            <span class="section-title-bar">Pelacakan Pengiriman</span>
            <button class="btn btn-sm btn-info ml-auto" id="btn-refresh-tracking"><i class="fas fa-sync"></i> Refresh</button>
        </div>
        <div class="card-body p-3">
            <form id="trackingForm" class="mb-3">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-4">
                        <label class="small mb-1">DO Number</label>
                        <input type="text" class="form-control form-control-sm" name="do_number" id="tracking-do-number" placeholder="CPK-HO1-..." value="{{ $peng->resi ?? '' }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small mb-1 d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i> Track</button>
                    </div>
                </div>
            </form>
            <div id="trackingStatus" class="small text-muted mb-2">Masukkan DO Number lalu klik Track.</div>
            <div class="table-responsive d-none" id="trackingMetaWrapper">
                <table class="table table-sm table-bordered mb-3">
                    <tbody id="trackingMetaBody"></tbody>
                </table>
            </div>
            <div class="table-responsive d-none" id="trackingLogsWrapper">
                <table class="table table-sm table-striped table-bordered mb-0" id="trackingLogsTable">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:70px">Flag</th>
                            <th>Log Name</th>
                            <th>Description</th>
                            <th style="width:160px">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Equipment Issue - Only show if tahapan_id is greater than 2 -->
    @if($peng && $peng->tahapan_id && $peng->tahapan_id > 2)
    <div class="card shadow-sm mb-4">
        <div class="card-header py-2 pr-1 bg-danger text-white d-flex align-items-center">
            <span class="section-title-bar">Pelaporan Keluhan</span>
            @if(auth()->user() && auth()->user()->role->role_name == 'puskesmas')
            <button class="btn btn-sm btn-danger ml-auto" data-toggle="modal" data-target="#addIssueModal"><i class="fas fa-edit"></i> Laporkan Keluhan Baru</button>
            @endif
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm" id="keluhanTable">
                    <thead>
                        <tr>
                            <th style="font-size: 11pt;">No.</th>
                            <th style="font-size: 11pt;">Tanggal Dilaporkan</th>
                            <th style="font-size: 11pt;">Keluhan</th>
                            <th style="font-size: 11pt;">Kategori Keluhan</th>
                            <th style="font-size: 11pt;">Jumlah Downtime</th>
                            <th style="font-size: 11pt;">Tanggal Selesai</th>
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

     <!-- Modal Tambah Keluhan -->
    <div class="modal fade" id="addIssueModal" tabindex="-1" role="dialog" aria-labelledby="addIssueModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="addIssueModalLabel">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Tambah Keluhan Baru
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addIssueForm" method="POST" action="{{ route('raised-issue.store') }}" enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf

                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Petunjuk:</strong> Lengkapi form di bawah untuk melaporkan keluhan terkait alat kesehatan T-Piece yang diterima.
                        </div>
                        <div class="row">
                            <!-- Subject Keluhan -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="issue_subject" class="required">Judul Keluhan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="issue_subject" name="issue_subject"
                                           placeholder="Masukkan judul / ringkasan keluhan"
                                           maxlength="255" required>
                                    <small class="form-text text-muted">Maksimal 255 karakter</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Deskripsi Keluhan -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="issue_description" class="required">Deskripsi Detail Keluhan <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="issue_description" name="issue_description"
                                              rows="5" placeholder="Jelaskan keluhan secara detail" maxlength="1000" required></textarea>
                                    <small class="form-text text-muted">
                                        <span id="char-count">0</span>/1000 karakter
                                    </small>
                                </div>
                            </div>
                        </div>



                        <div class="row">
                            <!-- Bukti Dokumentasi -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Bukti Dokumentasi</label>

                                    <!-- File input for multiple selection -->
                                    <input type="file" id="file-input-multiple" class="form-control-file mb-2"
                                           accept="image/jpeg,image/jpg,image/png" multiple>
                                    <small class="form-text text-muted">
                                        Maksimal 5 file, masing-masing 5MB (JPG, PNG)
                                    </small>

                                    <!-- Selected files list with previews -->
                                    <div id="selected-files-container" style="display: none;">
                                        <h6 class="mb-2">File Terpilih:</h6>
                                        <div id="selected-files-list" class="row">
                                            <!-- Files will be displayed here with previews -->
                                        </div>
                                    </div>

                                    <!-- Hidden inputs to store file data for form submission -->
                                    <div id="hidden-file-inputs"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-danger" id="submitBtn">
                            <i class="fas fa-paper-plane mr-1"></i> Kirim Keluhan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Incident Management -->
    <div class="card shadow-sm mb-4">
        <div class="card-header py-2 pr-1 d-flex align-items-center" style="background:#e226d2;">
            <span class="section-title-bar text-white">Pelaporan Insiden</span>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-sm table-striped table-bordered mb-0">
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
                            <th rowspan="2" class="align-middle text-center" style="width:120px">RENCANA TINDAKAN KOREKSI</th>
                            <th rowspan="2" class="align-middle text-center" style="width:100px">PELAKSANA TINDAKAN</th>
                            <th rowspan="2" class="align-middle text-center" style="width:100px">TANGGAL SELESAI</th>
                            <th colspan="3" class="text-center">VERIFIKASI</th>
                            <!-- Tindakan Korektif columns -->
                            <th rowspan="2" class="align-middle text-center" style="width:120px">RENCANA TINDAKAN KOREKTIF</th>
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
        </div>
    </div>

    <!-- Modal Delivery Information -->
    @if(auth()->user() && auth()->user()->role->role_name == 'endo')
    <div class="modal fade" id="deliveryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Edit Informasi Pengiriman</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="deliveryForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-2 pb-1 border-bottom"><strong class="text-muted small">Pengiriman</strong></div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label class="small mb-1">Tanggal Pengiriman</label>
                                <input type="date" class="form-control form-control-sm" name="tgl_pengiriman" value="{{ optional($peng->tgl_pengiriman)->format('Y-m-d') }}">
                            </div>
                            <div class="form-group col-md-2">
                                <label class="small mb-1">ETA</label>
                                <input type="date" class="form-control form-control-sm" name="eta" value="{{ optional($peng->eta)->format('Y-m-d') }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="small mb-1">RESI</label>
                                <input type="text" class="form-control form-control-sm" name="resi" value="{{ $peng->resi }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="small mb-1">Link Tracking</label>
                                <input type="text" class="form-control form-control-sm" name="tracking_link" value="{{ $peng->tracking_link }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="small mb-1">Serial Number</label>
                                <input type="text" class="form-control form-control-sm" name="serial_number" value="{{ $puskesmas->equipment->serial_number ?? '' }}" placeholder="Enter serial number">
                                <small class="form-text text-muted">System will create equipment record if new</small>
                            </div>
                        </div>
                        <div class="mb-2 mt-3 pb-1 border-bottom"><strong class="text-muted small">Penerimaan</strong></div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label class="small mb-1">Tanggal Diterima</label>
                                <input type="date" class="form-control form-control-sm" name="tgl_diterima" value="{{ optional($peng->tgl_diterima)->format('Y-m-d') }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="small mb-1">Nama Penerima</label>
                                <input type="text" class="form-control form-control-sm" name="nama_penerima" value="{{ $peng->nama_penerima }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="small mb-1">Jabatan Penerima</label>
                                <input type="text" class="form-control form-control-sm" name="jabatan_penerima" value="{{ $peng->jabatan_penerima }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="small mb-1">Instansi Penerima</label>
                                <input type="text" class="form-control form-control-sm" name="instansi_penerima" value="{{ $peng->instansi_penerima }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="small mb-1">Nomor HP Penerima</label>
                                <input type="text" class="form-control form-control-sm" name="nomor_penerima" value="{{ $peng->nomor_penerima }}">
                            </div>
                            <div class="form-group col-md-5">
                                <label class="small mb-1 d-flex align-items-center">Upload Tanda Terima <span class="ml-1 badge badge-light border">pdf/jpg/png</span></label>
                                <input type="file" class="form-control-file" name="link_tanda_terima" accept="application/pdf,image/jpeg,image/png">
                                @if($peng && $peng->link_tanda_terima)
                                    <small class="d-block mt-1"><a target="_blank" href="{{ asset('storage/' . $peng->link_tanda_terima) }}">File saat ini</a></small>
                                @endif
                            </div>
                        </div>
                        <div class="mb-2 mt-3 pb-1 border-bottom"><strong class="text-muted small">Status & Catatan</strong></div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label class="small mb-1">Catatan</label>
                                <textarea class="form-control form-control-sm" rows="2" name="catatan">{{ $peng->catatan }}</textarea>
                            </div>
                        </div>
                        <small class="text-muted">*Kolom yang tidak diubah biarkan kosong atau tetap nilainya.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Uji Fungsi -->
    <div class="modal fade" id="ujiFungsiModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background:#3f0fa8;" >
                    <h5 class="modal-title text-white">Edit Uji Fungsi</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="ujiFungsiForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-2 pb-1 border-bottom"><strong class="text-muted small">Jadwal</strong></div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label class="small mb-1">Tanggal Instalasi</label>
                                <input type="date" name="tgl_instalasi" class="form-control form-control-sm" value="{{ optional($uji->tgl_instalasi)->format('Y-m-d') }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="small mb-1">Target Tanggal Uji Fungsi</label>
                                <input type="date" name="target_tgl_uji_fungsi" class="form-control form-control-sm" value="{{ optional($uji->target_tgl_uji_fungsi)->format('Y-m-d') }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="small mb-1">Tanggal Uji Fungsi</label>
                                <input type="date" name="tgl_uji_fungsi" class="form-control form-control-sm" value="{{ optional($uji->tgl_uji_fungsi)->format('Y-m-d') }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="small mb-1">Tanggal Pelatihan Alat</label>
                                <input type="date" name="tgl_pelatihan" class="form-control form-control-sm" value="{{ optional($uji->tgl_pelatihan)->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="mb-2 mt-3 pb-1 border-bottom"><strong class="text-muted small">Dokumen</strong></div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="small mb-1 d-flex align-items-center">Berita Acara Instalasi <span class="ml-1 badge badge-light border">pdf</span></label>
                                <input type="file" name="doc_instalasi" class="form-control-file" accept="application/pdf">
                                @if($uji && $uji->doc_instalasi)<small class="d-block mt-1"><a target="_blank" href="{{ asset('storage/' . $uji->doc_instalasi) }}">Lihat Saat Ini</a></small>@endif
                            </div>
                            <div class="form-group col-md-4">
                                <label class="small mb-1 d-flex align-items-center">Berita Acara Uji Fungsi <span class="ml-1 badge badge-light border">pdf</span></label>
                                <input type="file" name="doc_uji_fungsi" class="form-control-file" accept="application/pdf">
                                @if($uji && $uji->doc_uji_fungsi)<small class="d-block mt-1"><a target="_blank" href="{{ asset('storage/' . $uji->doc_uji_fungsi) }}">Lihat Saat Ini</a></small>@endif
                            </div>
                            <div class="form-group col-md-4">
                                <label class="small mb-1 d-flex align-items-center">Berita Acara Pelatihan Alat<span class="ml-1 badge badge-light border">pdf</span></label>
                                <input type="file" name="doc_pelatihan" class="form-control-file" accept="application/pdf">
                                @if($uji && $uji->doc_pelatihan)<small class="d-block mt-1"><a target="_blank" href="{{ asset('storage/' . $uji->doc_pelatihan) }}">Lihat Saat Ini</a></small>@endif
                            </div>
                        </div>
                        <div class="mb-2 mt-3 pb-1 border-bottom"><strong class="text-muted small">Catatan</strong></div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <textarea class="form-control form-control-sm" name="catatan" rows="2">{{ $uji->catatan }}</textarea>
                            </div>
                        </div>
                        <small class="text-muted">*Kolom yang tidak diubah biarkan kosong atau tetap nilainya.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background:#3f0fa8;border-color:#3f0fa8;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Documents -->
    <div class="modal fade" id="documentsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title">Edit Dokumen</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="documentsForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-2 pb-1 border-bottom"><strong class="text-muted small">Dokumen</strong></div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="small mb-1 d-flex align-items-center">Berita Acara Kalibrasi<span class="ml-1 badge badge-light border">pdf</span></label>
                                <input type="file" name="kalibrasi" class="form-control-file" accept="application/pdf">
                                @if($doc && $doc->kalibrasi)<small class="d-block mt-1"><a target="_blank" href="{{ asset('storage/' . $doc->kalibrasi) }}">File Saat Ini</a></small>@endif
                            </div>
                            <div class="form-group col-md-6">
                                <label class="small mb-1 d-flex align-items-center">Berita Acara BAST <span class="ml-1 badge badge-light border">pdf</span></label>
                                <input type="file" name="bast" class="form-control-file" accept="application/pdf">
                                @if($doc && $doc->bast)<small class="d-block mt-1"><a target="_blank" href="{{ asset('storage/' . $doc->bast) }}">File Saat Ini</a></small>@endif
                            </div>
                            <div class="form-group col-md-6">
                                <label class="small mb-1 d-flex align-items-center">Berita Acara BASTO <span class="ml-1 badge badge-light border">pdf</span></label>
                                <input type="file" name="basto" class="form-control-file" accept="application/pdf">
                                @if($doc && $doc->basto)<small class="d-block mt-1"><a target="_blank" href="{{ asset('storage/' . $doc->basto) }}">File Saat Ini</a></small>@endif
                            </div>
                            <div class="form-group col-md-6">
                                <label class="small mb-1 d-flex align-items-center">Berita Acara ASPAK <span class="ml-1 badge badge-light border">pdf</span></label>
                                <input type="file" name="aspak" class="form-control-file" accept="application/pdf">
                                @if($doc && $doc->aspak)<small class="d-block mt-1"><a target="_blank" href="{{ asset('storage/' . $doc->aspak) }}">File Saat Ini</a></small>@endif
                            </div>
                        </div>
                        <small class="text-muted">*Kosongkan jika tidak mengubah dokumen.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-secondary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal for Document Revision -->
    @if(auth()->user() && auth()->user()->role->role_name == 'kemenkes')
    <div class="modal fade" id="revisiModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Catatan Revisi Dokumen</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="revisiForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="small mb-1"><strong>Jenis Dokumen</strong></label>
                            <input type="text" class="form-control" id="revisiDocumentName" readonly>
                            <input type="hidden" name="document_type" id="revisiDocumentType">
                            <input type="hidden" name="jenis_dokumen_id" id="revisiJenisDokumenId">
                            <input type="hidden" name="puskesmas_id" value="{{ $puskesmas->id }}">
                        </div>
                        <div class="form-group">
                            <label class="small mb-1"><strong>Catatan Revisi</strong></label>
                            <textarea class="form-control" name="catatan" rows="4" placeholder="Jelaskan apa yang perlu diperbaiki atau direvisi dari dokumen ini..." required></textarea>
                            <small class="form-text text-muted">Berikan catatan yang spesifik tentang apa yang perlu diperbaiki.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Simpan Catatan Revisi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('js')
<!-- Toastr JS for toast notifications -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
</script>
<script>
// Basic Information form submission handler
$(function(){
    const $modal = $('#basicInfoModal');
    const $form = $('#basicInfoForm');
    if(!$modal.length || !$form.length) return;

    const updateUrl = '{{ route('api-verification-request.basic-information', ['id' => $puskesmas->id]) }}';

    function notifySuccess(msg){
        if(window.toastr){ toastr.success(msg); return; }
        if(window.Swal){ Swal.fire({icon:'success',title:'Berhasil',text:msg,timer:1400,showConfirmButton:false}); return; }
        alert(msg);
    }
    function notifyError(msg){
        if(window.toastr){ toastr.error(msg); return; }
        if(window.Swal){ Swal.fire({icon:'error',title:'Gagal',text:msg}); return; }
        alert(msg);
    }

    $form.on('submit', function(e){
        e.preventDefault();
        const $submitBtn = $form.find('button[type="submit"]');
        const originalHtml = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span>Simpan...');

        const formData = new FormData(this);

        // Clear previous error states
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();

        $.ajax({
            url: updateUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 15000,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        }).done(function(res){
            if(res && res.success){
                // Update visible table values without reload
                const data = res.data || {};
                const $kv = $('table.table-kv').first();
                // Update location fields if they were changed
                if(data.province_name !== undefined) $kv.find('tr:contains("Provinsi") td:last').text(data.province_name||'-');
                if(data.regency_name !== undefined) $kv.find('tr:contains("Kabupaten / Kota") td:last').text(data.regency_name||'-');
                if(data.district_name !== undefined) $kv.find('tr:contains("Kecamatan") td:last').text(data.district_name||'-');
                // Update identity fields
                if(data.name !== undefined) $kv.find('tr:contains("Nama Puskesmas") td:last').text(data.name||'-');
                if(data.pic !== undefined) $kv.find('tr:contains("PIC Puskesmas") td:last').text(data.pic||'-');
                if(data.no_hp !== undefined) $kv.find('tr:contains("No. HP PIC Puskesmas") td:last').text(data.no_hp||'-');
                if(data.no_hp_alternatif !== undefined) $kv.find('tr:contains("No. HP Alternatif PIC Puskesmas") td:last').text(data.no_hp_alternatif||'-');
                if(data.kepala !== undefined) $kv.find('tr:contains("Kepala Puskesmas") td:last').text(data.kepala||'-');
                if(data.pic_dinkes_prov !== undefined) $kv.find('tr:contains("PIC Dinas Kesehatan Provinsi") td:last').text(data.pic_dinkes_prov||'-');
                if(data.pic_dinkes_kab !== undefined) $kv.find('tr:contains("PIC DINKES") td:last').text(data.pic_dinkes_kab||'-');



                notifySuccess(res.message || 'Berhasil memperbarui data');
                $modal.modal('hide');
            } else {
                notifyError((res && res.message) || 'Gagal memperbarui data');
            }
        }).fail(function(xhr, status){
            if(status === 'timeout'){
                notifyError('Permintaan timeout, periksa koneksi Anda');
                return;
            }
            if(xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors){
                const errs = xhr.responseJSON.errors;
                Object.keys(errs).forEach(f => {
                    const input = $form.find(`[name='${f}']`);
                    input.addClass('is-invalid');
                    if(input.next('.invalid-feedback').length===0){
                        input.after(`<div class="invalid-feedback">${errs[f][0]}</div>`);
                    } else {
                        input.next('.invalid-feedback').text(errs[f][0]);
                    }
                });
                notifyError('Periksa kembali input Anda');
            } else if(xhr.status === 404){
                notifyError('Data tidak ditemukan');
            } else {
                notifyError('Terjadi kesalahan server');
            }
        }).always(function(){
            $submitBtn.prop('disabled', false).html(originalHtml);
        });
    });
});

// Cascading selects for Basic Information modal
$(function(){
    const $modal = $('#basicInfoModal');
    if(!$modal.length) return;

    const provinceUrl = $modal.data('provinces-url');
    const regencyUrl  = $modal.data('regencies-url');
    const districtUrl = $modal.data('districts-url');

    const $prov = $('#basic-province-select');
    const $reg  = $('#basic-regency-select');
    const $dist = $('#basic-district-select');

    // Get current IDs from data attributes
    const currentDistrictId = $modal.data('current-district-id');
    const currentRegencyId = $modal.data('current-regency-id');
    const currentProvinceId = $modal.data('current-province-id');

    let isInitialLoad = true;

    function resetSelect($el, placeholder){
        $el.empty().append(`<option value="">${placeholder}</option>`).prop('disabled', true);
    }

    function loadProvinces(selectedId){
        return $.get(provinceUrl).done(resp => {
            if(!resp.success) return;
            $prov.empty().append(`<option value="">-- Pilih Provinsi --</option>`);
            resp.data.forEach(p => {
                $prov.append(`<option value='${p.id}'>${p.name}</option>`);
            });
            if(selectedId){
                $prov.val(selectedId);
                if(isInitialLoad) {
                    loadRegencies(selectedId, currentRegencyId);
                }
            }
        }).fail(()=>{
            console.warn('Failed loading provinces');
            toastr.warning('Gagal memuat data provinsi');
        });
    }

    function loadRegencies(provinceId, selectedId){
        resetSelect($reg, '-- Pilih Kabupaten/Kota --');
        resetSelect($dist, '-- Pilih Kecamatan --');
        if(!provinceId) return Promise.resolve();

        return $.get(regencyUrl, { province_id: provinceId }).done(resp => {
            if(!resp.success) return;
            $reg.prop('disabled', false);
            $reg.empty().append(`<option value="">-- Pilih Kabupaten/Kota --</option>`);
            resp.data.forEach(r => {
                $reg.append(`<option value='${r.id}'>${r.name}</option>`);
            });
            if(selectedId){
                $reg.val(selectedId);
                if(isInitialLoad) {
                    loadDistricts(selectedId, currentDistrictId);
                }
            }
        }).fail(()=>{
            console.warn('Failed loading regencies');
            toastr.warning('Gagal memuat data kabupaten/kota');
        });
    }

    function loadDistricts(regencyId, selectedId){
        resetSelect($dist, '-- Pilih Kecamatan --');
        if(!regencyId) return Promise.resolve();

        return $.get(districtUrl, { regency_id: regencyId }).done(resp => {
            if(!resp.success) return;
            $dist.prop('disabled', false);
            $dist.empty().append(`<option value="">-- Pilih Kecamatan --</option>`);
            resp.data.forEach(d => {
                $dist.append(`<option value='${d.id}'>${d.name}</option>`);
            });
            if(selectedId){
                $dist.val(selectedId);
            }
        }).fail(()=>{
            console.warn('Failed loading districts');
            toastr.warning('Gagal memuat data kecamatan');
        });
    }

    // Event handlers for manual changes
    $prov.on('change', function(){
        const pid = $(this).val();
        isInitialLoad = false; // Prevent auto-chaining on manual changes
        if(pid) {
            loadRegencies(pid, null);
        } else {
            resetSelect($reg, '-- Pilih Kabupaten/Kota --');
            resetSelect($dist, '-- Pilih Kecamatan --');
        }
    });

    $reg.on('change', function(){
        const rid = $(this).val();
        if(rid) {
            loadDistricts(rid, null);
        } else {
            resetSelect($dist, '-- Pilih Kecamatan --');
        }
    });

    // Initialize on modal show
    $modal.on('shown.bs.modal', function(){
        // Only load if not already populated
        if($prov.children('option').length <= 1){
            isInitialLoad = true;
            // Start the cascade with current province
            if(currentProvinceId) {
                loadProvinces(currentProvinceId);
            } else {
                loadProvinces(null);
            }
        }
    });

    // Reset initial load flag when modal closes
    $modal.on('hidden.bs.modal', function(){
        isInitialLoad = true;
    });
});

// Tracking DO logs
$(function(){
    const $trackingCard = $('#trackingCard');
    if(!$trackingCard.length) return; // tidak ada resi, tidak inisialisasi
    const apiBaseTemplate = 'https://api.cpk-log.id/v1.0.0/delivery-orders/{DO}/get-logs';
    const $form = $('#trackingForm');
    const $doInput = $('#tracking-do-number');
    const $status = $('#trackingStatus');
    const $metaWrapper = $('#trackingMetaWrapper');
    const $metaBody = $('#trackingMetaBody');
    const $logsWrapper = $('#trackingLogsWrapper');
    const $logsTbody = $('#trackingLogsTable tbody');
    const $refreshBtn = $('#btn-refresh-tracking');
    let lastDoNumber = null;
    const initialDo = $trackingCard.data('initial-do');

    function setLoading(on){
        if(on){
            $status.removeClass('text-danger').addClass('text-info').text('Memuat data tracking...');
            $refreshBtn.prop('disabled', true).append('<span class="spinner-border spinner-border-sm ml-1"></span>');
        } else {
            $refreshBtn.prop('disabled', false).find('.spinner-border').remove();
        }
    }

    function renderMeta(data){
        const created = data.createdAt ? new Date(data.createdAt).toLocaleString() : '-';
        const updated = data.updatedAt ? new Date(data.updatedAt).toLocaleString() : '-';
        let html = '';
        html += `<tr><th class='bg-light' style='width:170px'>DO Number</th><td>${data.doNumber||'-'}</td></tr>`;
        html += `<tr><th class='bg-light'>Sender</th><td>${(data.sender?.honorific||'')+' '+(data.sender?.fullName||'-')}</td></tr>`;
        html += `<tr><th class='bg-light'>Recipient</th><td>${(data.recipient?.honorific||'')+' '+(data.recipient?.fullName||'-')}</td></tr>`;
        html += `<tr><th class='bg-light'>Destination</th><td>${data.destination||'-'}</td></tr>`;
        if(data.itemTotal){
            html += `<tr><th class='bg-light'>Items</th><td>${data.itemTotal.totalItem} item(s), Qty ${data.itemTotal.totalQtyItem}, Netto ${data.itemTotal.totalWeightNetto}kg, Bruto ${data.itemTotal.totalWeightBruto}kg</td></tr>`;
        }
        if(data.driver){
            html += `<tr><th class='bg-light'>Driver</th><td>${data.driver.fullName||'-'} (${data.driver.phoneNumber||'-'})</td></tr>`;
        }
        if(data.vehicle){
            html += `<tr><th class='bg-light'>Vehicle</th><td>${data.vehicle.vehicleNumber||'-'}</td></tr>`;
        }
        html += `<tr><th class='bg-light'>Created At</th><td>${created}</td></tr>`;
        html += `<tr><th class='bg-light'>Updated At</th><td>${updated}</td></tr>`;
        $metaBody.html(html);
        $metaWrapper.removeClass('d-none');
    }

    function renderLogs(logs){
        if(!Array.isArray(logs)) logs = [];
        if(!logs.length){
            $logsTbody.html(`<tr><td colspan='4' class='text-center text-muted small'>Belum ada log.</td></tr>`);
            $logsWrapper.removeClass('d-none');
            return;
        }
        let rows='';
        logs.forEach(l => {
            const time = l.logAt ? new Date(l.logAt).toLocaleString() : '-';
            rows += `<tr>
                <td><span class='badge badge-info'>${l.flag?.code||'-'}</span></td>
                <td>${l.logName||'-'}</td>
                <td>${l.logDescription||'-'}</td>
                <td class='text-nowrap'>${time}</td>
            </tr>`;
        });
        $logsTbody.html(rows);
        $logsWrapper.removeClass('d-none');
    }

    function fetchLogs(doNumber){
        if(!doNumber){
            $status.addClass('text-danger').text('DO Number tidak boleh kosong.');
            return;
        }
        lastDoNumber = doNumber;
        const url = apiBaseTemplate.replace('{DO}', encodeURIComponent(doNumber));
        setLoading(true);
        $logsWrapper.addClass('d-none');
        $metaWrapper.addClass('d-none');
        $.ajax({
            url: url,
            method: 'GET',
            timeout: 15000,
        }).done(resp => {
            if(resp && resp.statusCode === 200 && resp.data){
                renderMeta(resp.data);
                renderLogs(resp.data.logs);
                $status.removeClass('text-danger').addClass('text-success').text('Tracking data berhasil dimuat.');
            } else {
                $status.addClass('text-danger').text('Response tidak valid.');
            }
        }).fail((xhr, textStatus) => {
            if(textStatus === 'timeout'){
                $status.addClass('text-danger').text('Permintaan timeout, coba lagi.');
            } else {
                $status.addClass('text-danger').text('Gagal memuat data tracking.');
            }
        }).always(()=> setLoading(false));
    }

    $form.on('submit', function(e){
        e.preventDefault();
        const val = $doInput.val().trim();
        fetchLogs(val);
    });

    $refreshBtn.on('click', function(){
        if(lastDoNumber){
            fetchLogs(lastDoNumber);
        } else {
            $status.text('Tidak ada DO yang terakhir dilacak. Masukkan DO Number.');
        }
    });

    // Auto fetch if initial DO present
    if(initialDo){
        $doInput.val(initialDo);
        fetchLogs(initialDo);
    }
});

// Delivery Information form submission handler
$(function(){
    const $modal = $('#deliveryModal');
    const $form = $('#deliveryForm');
    if(!$modal.length || !$form.length) return;

    const updateUrl = '{{ route('api-verification-request.delivery-information', ['id' => $puskesmas->id]) }}';

    function notifySuccess(msg){
        if(window.toastr){ toastr.success(msg); return; }
        if(window.Swal){ Swal.fire({icon:'success',title:'Berhasil',text:msg,timer:1400,showConfirmButton:false}); return; }
        alert(msg);
    }
    function notifyError(msg){
        if(window.toastr){ toastr.error(msg); return; }
        if(window.Swal){ Swal.fire({icon:'error',title:'Gagal',text:msg}); return; }
        alert(msg);
    }

    $form.on('submit', function(e){
        e.preventDefault();
        const $submitBtn = $form.find('button[type="submit"]');
        const originalHtml = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span>Menyimpan...');

        const formData = new FormData(this);

        // Clear previous error states
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();

        $.ajax({
            url: updateUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 30000, // 30 seconds for file upload
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        }).done(function(res){
            if(res && res.success){
                // Update visible table values without reload
                const data = res.data || {};
                const $deliveryTable = $('.card-header:contains("Informasi Pengiriman")').next('.card-body').find('table.table-kv');

                // Update delivery information fields
                if(data.tgl_pengiriman !== undefined) $deliveryTable.find('tr:contains("Tanggal Pengiriman") td:last').text(data.tgl_pengiriman || '-');
                if(data.eta !== undefined) $deliveryTable.find('tr:contains("ETA") td:last').text(data.eta || '-');
                if(data.resi !== undefined) $deliveryTable.find('tr:contains("RESI") td:last').text(data.resi || '-');
                if(data.tracking_link !== undefined) {
                    const trackingCell = $deliveryTable.find('tr:contains("Link Tracking") td:last');
                    if(data.tracking_link) {
                        trackingCell.html(`<a class="text-decoration-none" target="_blank" href="${data.tracking_link}">View Here</a>`);
                    } else {
                        trackingCell.text('-');
                    }
                }
                if(data.serial_number !== undefined) $deliveryTable.find('tr:contains("Serial Number") td:last').text(data.serial_number || '-');
                if(data.tgl_diterima !== undefined) $deliveryTable.find('tr:contains("Tanggal Diterima") td:last').text(data.tgl_diterima || '-');
                if(data.nama_penerima !== undefined) $deliveryTable.find('tr:contains("Nama Penerima") td:last').text(data.nama_penerima || '-');
                if(data.jabatan_penerima !== undefined) $deliveryTable.find('tr:contains("Jabatan Penerima") td:last').text(data.jabatan_penerima || '-');
                if(data.instansi_penerima !== undefined) $deliveryTable.find('tr:contains("Instansi Penerima") td:last').text(data.instansi_penerima || '-');
                if(data.nomor_penerima !== undefined) $deliveryTable.find('tr:contains("Nomor HP Penerima") td:last').text(data.nomor_penerima || '-');
                if(data.catatan !== undefined) $deliveryTable.find('tr:contains("Catatan") td:last').text(data.catatan || '-');

                // Update tanda terima link
                if(data.link_tanda_terima !== undefined) {
                    const tandaTerimaCell = $deliveryTable.find('tr:contains("Bukti Tanda Terima") td:last');
                    if(data.link_tanda_terima) {
                        const storageUrl = '{{ asset("storage/") }}/' + data.link_tanda_terima;
                        tandaTerimaCell.html(`<a class="text-decoration-none" target="_blank" href="${storageUrl}">View Here</a>`);
                    } else {
                        tandaTerimaCell.text('-');
                    }
                }

                // Update status penerimaan
                const statusText = data.tgl_diterima ? 'Diterima' : '-';
                $deliveryTable.find('tr:contains("Status Penerimaan") td:last').text(statusText);

                notifySuccess(res.message || 'Data pengiriman berhasil diperbarui');
                $modal.modal('hide');

                // Refresh page after 1 second to update progress timeline
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                notifyError((res && res.message) || 'Gagal memperbarui data pengiriman');
            }
        }).fail(function(xhr, status){
            if(status === 'timeout'){
                notifyError('Permintaan timeout, periksa koneksi Anda');
                return;
            }
            if(xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors){
                const errs = xhr.responseJSON.errors;
                Object.keys(errs).forEach(f => {
                    const input = $form.find(`[name='${f}']`);
                    input.addClass('is-invalid');
                    if(input.next('.invalid-feedback').length===0){
                        input.after(`<div class="invalid-feedback">${errs[f][0]}</div>`);
                    } else {
                        input.next('.invalid-feedback').text(errs[f][0]);
                    }
                });
                notifyError('Periksa kembali input Anda');
            } else if(xhr.status === 404){
                notifyError('Data tidak ditemukan');
            } else {
                notifyError('Terjadi kesalahan server');
            }
        }).always(function(){
            $submitBtn.prop('disabled', false).html(originalHtml);
        });
    });
});

// Uji Fungsi form submission handler
$(function(){
    const $modal = $('#ujiFungsiModal');
    const $form = $('#ujiFungsiForm');
    if(!$modal.length || !$form.length) return;

    const updateUrl = '{{ route('api-verification-request.uji-fungsi-information', ['id' => $puskesmas->id]) }}';

    function notifySuccess(msg){
        if(window.toastr){ toastr.success(msg); return; }
        if(window.Swal){ Swal.fire({icon:'success',title:'Berhasil',text:msg,timer:1400,showConfirmButton:false}); return; }
        alert(msg);
    }
    function notifyError(msg){
        if(window.toastr){ toastr.error(msg); return; }
        if(window.Swal){ Swal.fire({icon:'error',title:'Gagal',text:msg}); return; }
        alert(msg);
    }

    $form.on('submit', function(e){
        e.preventDefault();
        const $submitBtn = $form.find('button[type="submit"]');
        const originalHtml = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span>Menyimpan...');

        const formData = new FormData(this);

        // Clear previous error states
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();

        $.ajax({
            url: updateUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 30000, // 30 seconds for file upload
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        }).done(function(res){
            if(res && res.success){
                // Update visible table values without reload
                const data = res.data || {};
                const $ujiFungsiTable = $('.card-header:contains("Uji Fungsi")').next('.card-body').find('table.table-kv');

                // Update uji fungsi information fields
                if(data.tgl_instalasi !== undefined) $ujiFungsiTable.find('tr:contains("Tanggal Instalasi") td:last').text(data.tgl_instalasi || '-');
                if(data.target_tgl_uji_fungsi !== undefined) $ujiFungsiTable.find('tr:contains("Target Tanggal Uji Fungsi") td:last').text(data.target_tgl_uji_fungsi || '-');
                if(data.tgl_uji_fungsi !== undefined) $ujiFungsiTable.find('tr:contains("Tanggal Uji Fungsi") td:last').text(data.tgl_uji_fungsi || '-');
                if(data.tgl_pelatihan !== undefined) $ujiFungsiTable.find('tr:contains("Tanggal Pelatihan Alat") td:last').text(data.tgl_pelatihan || '-');
                if(data.catatan !== undefined) $ujiFungsiTable.find('tr:contains("Catatan") td:last').text(data.catatan || '-');

                // Update document links
                if(data.doc_instalasi !== undefined) {
                    const docInstalasiCell = $ujiFungsiTable.find('tr:contains("Berita Acara Instalasi") td:last');
                    if(data.doc_instalasi) {
                        const storageUrl = '{{ asset("storage/") }}/' + data.doc_instalasi;
                        docInstalasiCell.html(`<a class="text-decoration-none" target="_blank" href="${storageUrl}">View Here</a>`);
                    } else {
                        docInstalasiCell.text('-');
                    }
                }

                if(data.doc_uji_fungsi !== undefined) {
                    const docUjiFungsiCell = $ujiFungsiTable.find('tr:contains("Berita Acara Uji Fungsi") td:last');
                    if(data.doc_uji_fungsi) {
                        const storageUrl = '{{ asset("storage/") }}/' + data.doc_uji_fungsi;
                        docUjiFungsiCell.html(`<a class="text-decoration-none" target="_blank" href="${storageUrl}">View Here</a>`);
                    } else {
                        docUjiFungsiCell.text('-');
                    }
                }

                if(data.doc_pelatihan !== undefined) {
                    const docPelatihanCell = $ujiFungsiTable.find('tr:contains("Berita Acara Pelatihan Alat") td:last');
                    if(data.doc_pelatihan) {
                        const storageUrl = '{{ asset("storage/") }}/' + data.doc_pelatihan;
                        docPelatihanCell.html(`<a class="text-decoration-none" target="_blank" href="${storageUrl}">View Here</a>`);
                    } else {
                        docPelatihanCell.text('-');
                    }
                }

                notifySuccess(res.message || 'Data uji fungsi berhasil diperbarui');
                $modal.modal('hide');

                // Refresh page after 1 second to update progress timeline
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                notifyError((res && res.message) || 'Gagal memperbarui data uji fungsi');
            }
        }).fail(function(xhr, status){
            if(status === 'timeout'){
                notifyError('Permintaan timeout, periksa koneksi Anda');
                return;
            }
            if(xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors){
                const errs = xhr.responseJSON.errors;
                Object.keys(errs).forEach(f => {
                    const input = $form.find(`[name='${f}']`);
                    input.addClass('is-invalid');
                    if(input.next('.invalid-feedback').length===0){
                        input.after(`<div class="invalid-feedback">${errs[f][0]}</div>`);
                    } else {
                        input.next('.invalid-feedback').text(errs[f][0]);
                    }
                });
                notifyError('Periksa kembali input Anda');
            } else if(xhr.status === 404){
                notifyError('Data tidak ditemukan');
            } else {
                notifyError('Terjadi kesalahan server');
            }
        }).always(function(){
            $submitBtn.prop('disabled', false).html(originalHtml);
        });
    });
});

// Documents form submission handler
$(function(){
    const $modal = $('#documentsModal');
    const $form = $('#documentsForm');
    if(!$modal.length || !$form.length) return;

    const updateUrl = '{{ route('api-verification-request.document-information', ['id' => $puskesmas->id]) }}';

    function notifySuccess(msg){
        if(window.toastr){ toastr.success(msg); return; }
        if(window.Swal){ Swal.fire({icon:'success',title:'Berhasil',text:msg,timer:1400,showConfirmButton:false}); return; }
        alert(msg);
    }
    function notifyError(msg){
        if(window.toastr){ toastr.error(msg); return; }
        if(window.Swal){ Swal.fire({icon:'error',title:'Gagal',text:msg}); return; }
        alert(msg);
    }

    $form.on('submit', function(e){
        e.preventDefault();
        const $submitBtn = $form.find('button[type="submit"]');
        const originalHtml = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span>Menyimpan...');

        const formData = new FormData(this);

        // Clear previous error states
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();

        $.ajax({
            url: updateUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 30000, // 30 seconds for file upload
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        }).done(function(res){
            if(res && res.success){
                // Update visible table values without reload
                const data = res.data || {};
                const $documentsTable = $('.card-header:contains("Dokumen")').next('.card-body').find('table.table-kv');

                // Update document links
                const documentFields = ['basto', 'kalibrasi', 'bast', 'aspak', 'update_aspak'];
                const documentLabels = {
                    'basto': 'Berita Acara BASTO',
                    'kalibrasi': 'Berita Acara Kalibrasi',
                    'bast': 'Berita Acara BAST',
                    'aspak': 'Berita Acara ASPAK',
                    'update_aspak': 'Update ASPAK'
                };

                documentFields.forEach(field => {
                    if(data[field] !== undefined) {
                        const docCell = $documentsTable.find(`tr:contains("${documentLabels[field]}") td:last`);
                        if(data[field]) {
                            const storageUrl = '{{ asset("storage/") }}/' + data[field];
                            docCell.html(`<a class="text-decoration-none" target="_blank" href="${storageUrl}">View Here</a>`);
                        } else {
                            docCell.text('-');
                        }
                    }
                });

                notifySuccess(res.message || 'Data dokumen berhasil diperbarui');
                $modal.modal('hide');

                // Refresh page after 1 second to update any related status
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                notifyError((res && res.message) || 'Gagal memperbarui data dokumen');
            }
        }).fail(function(xhr, status){
            if(status === 'timeout'){
                notifyError('Permintaan timeout, periksa koneksi Anda');
                return;
            }
            if(xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors){
                const errs = xhr.responseJSON.errors;
                Object.keys(errs).forEach(f => {
                    const input = $form.find(`[name='${f}']`);
                    input.addClass('is-invalid');
                    if(input.next('.invalid-feedback').length===0){
                        input.after(`<div class="invalid-feedback">${errs[f][0]}</div>`);
                    } else {
                        input.next('.invalid-feedback').text(errs[f][0]);
                    }
                });
                notifyError('Periksa kembali input Anda');
            } else if(xhr.status === 404){
                notifyError('Data tidak ditemukan');
            } else {
                notifyError('Terjadi kesalahan server');
            }
        }).always(function(){
            $submitBtn.prop('disabled', false).html(originalHtml);
        });
    });
});



// Verification Function
function createVerificationHandler(formId, switchId, routeName, documentName, parameterName) {
    const $form = $(formId);
    const $verificationSwitch = $(switchId);

    if (!$form.length || !$verificationSwitch.length) return;

    // Check if user is kemenkes
    const userRole = '{{ auth()->user()->role->role_name }}';
    if (userRole !== 'kemenkes') {
        return; // Don't attach handlers if not kemenkes
    }

    let isUpdating = false;

    $verificationSwitch.on('change', function(e) {
        if (isUpdating) return;

        const $this = $(this);

        // Check if the switch is disabled (already verified)
        if ($this.prop('disabled')) {
            e.preventDefault();
            return false;
        }

        const isChecked = $this.is(':checked');
        const currentState = !isChecked; // Previous state

        // If trying to uncheck (undo verification), prevent it
        if (currentState === true && isChecked === false) {
            $this.prop('checked', true);
            Swal.fire({
                title: 'Tidak Dapat Dibatalkan',
                text: 'Verifikasi yang sudah dilakukan tidak dapat dibatalkan.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return false;
        }



        // Prevent the change temporarily for confirmation
        $this.prop('checked', currentState);

        const title = isChecked ? `Verifikasi ${documentName}` : 'Batalkan Verifikasi';
        const text = isChecked ?
            `Apakah Anda yakin ingin memverifikasi ${documentName}? Tindakan ini tidak dapat dibatalkan.` :
            'Apakah Anda yakin ingin membatalkan verifikasi ini?';
        const confirmButtonText = isChecked ? 'Ya, Verifikasi' : 'Ya, Batalkan';
        const icon = isChecked ? 'question' : 'warning';

        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: isChecked ? '#28a745' : '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: confirmButtonText,
            cancelButtonText: 'Batal',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            if (result.isConfirmed) {
                // User confirmed, proceed with the verification
                $this.prop('checked', isChecked);
                updateVerificationStatus(isChecked, routeName, documentName);
            }
            // If cancelled, the switch will remain in its previous state
        });
    });

    function updateVerificationStatus(verified, routeName, documentName) {
        isUpdating = true;

        // Show loading state
        Swal.fire({
            title: 'Memproses...',
            text: `${verified ? 'Memverifikasi' : 'Membatalkan verifikasi'} ${documentName}, mohon tunggu...`,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const requestData = {};
        requestData[parameterName] = verified;

        $.ajax({
            url: routeName,
            method: 'POST',
            data: requestData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            timeout: 10000
        }).done(function(res) {
            if (res && res.success) {
                // Update the switch state
                $verificationSwitch.prop('checked', verified);

                // Update label text
                const label = $verificationSwitch.next('label');
                if (verified) {
                    label.text('Terverifikasi');
                    $verificationSwitch.prop('disabled', true);
                } else {
                    label.text('Belum Terverifikasi');
                }

                // Update verification date if provided
                if (res.data && res.data.verified_at !== undefined) {
                    const dateRow = $verificationSwitch.closest('table').find('tr[hidden]');
                    if (res.data.verified_at) {
                        dateRow.removeAttr('hidden');
                        dateRow.find('td:last').text(res.data.verified_at || '-');
                    } else {
                        dateRow.attr('hidden', true);
                    }
                }

                // Show success message
                Swal.fire({
                    title: 'Berhasil!',
                    text: res.message || `${documentName} berhasil ${verified ? 'diverifikasi' : 'dibatalkan verifikasinya'}`,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });

                // Reload page after a short delay to update progress timeline
                setTimeout(() => {
                    location.reload();
                }, 2500);

            } else {
                // Reset switch state
                $verificationSwitch.prop('checked', !verified);

                Swal.fire({
                    title: 'Gagal!',
                    text: res.message || `Gagal ${verified ? 'memverifikasi' : 'membatalkan verifikasi'} ${documentName}`,
                    icon: 'error'
                });
            }
        }).fail(function(xhr, status) {
            // Reset switch state
            $verificationSwitch.prop('checked', !verified);

            let errorMessage = `Gagal ${verified ? 'memverifikasi' : 'membatalkan verifikasi'} ${documentName}`;
            if (status === 'timeout') {
                errorMessage = 'Koneksi timeout, silakan coba lagi';
            } else if (xhr.status === 404) {
                errorMessage = 'Data tidak ditemukan';
            } else if (xhr.status === 422) {
                // Try to get the specific error message from the response
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else {
                    errorMessage = 'Data tidak valid';
                }
            }

            Swal.fire({
                title: 'Gagal!',
                text: errorMessage,
                icon: 'error'
            });
        }).always(function() {
            isUpdating = false;
        });
    }
}

// Initialize all verification handlers
$(function() {
    // Document verification handlers using the reusable function
    createVerificationHandler(
        '#verifInstalasiForm',
        '#verifiedInstalasi',
        '{{ route("api-verification-request.instalasi-verification", ["id" => $puskesmas->id]) }}',
        'Berita Acara Instalasi',
        'is_verified_instalasi'
    );

    createVerificationHandler(
        '#verifUjiFungsiForm',
        '#verifiedUjiFungsi',
        '{{ route("api-verification-request.ujifungsi-verification", ["id" => $puskesmas->id]) }}',
        'Berita Acara Uji Fungsi',
        'is_verified_uji_fungsi'
    );

    createVerificationHandler(
        '#verifPelatihanForm',
        '#verifiedPelatihan',
        '{{ route("api-verification-request.pelatihan-verification", ["id" => $puskesmas->id]) }}',
        'Berita Acara Pelatihan Alat',
        'is_verified_pelatihan'
    );

    // Documents table verification handlers
    createVerificationHandler(
        '#verifKalibrasiForm',
        '#verifiedKalibrasi',
        '{{ route("api-verification-request.kalibrasi-verification", ["id" => $puskesmas->id]) }}',
        'Berita Acara Kalibrasi',
        'is_verified_kalibrasi'
    );

    createVerificationHandler(
        '#verifBastForm',
        '#verifiedBast',
        '{{ route("api-verification-request.bast-verification", ["id" => $puskesmas->id]) }}',
        'Berita Acara BAST',
        'is_verified_bast'
    );

    createVerificationHandler(
        '#verifBastoForm',
        '#verifiedBasto',
        '{{ route("api-verification-request.basto-verification", ["id" => $puskesmas->id]) }}',
        'Berita Acara BASTO',
        'is_verified_basto'
    );

    createVerificationHandler(
        '#verifAspakForm',
        '#verifiedAspak',
        '{{ route("api-verification-request.aspak-verification", ["id" => $puskesmas->id]) }}',
        'Berita Acara ASPAK',
        'is_verified_aspak'
    );
});

// Revision Modal Handler
$(function() {
    // Handle revision button clicks
    $('.revisi-btn').on('click', function() {
        const docType = $(this).data('doc-type');
        const docName = $(this).data('doc-name');
        const jenisDokumenId = $(this).data('jenis-dokumen-id');

        // Set the document information in the modal
        $('#revisiDocumentType').val(docType);
        $('#revisiDocumentName').val(docName);
        $('#revisiJenisDokumenId').val(jenisDokumenId);
    });

    // Handle revision form submission
    $('#revisiForm').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const originalHtml = $submitBtn.html();

        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span>Menyimpan...');

        const formData = new FormData(this);

        $.ajax({
            url: '{{ route("api-verification-request.add-revision", ["id" => $puskesmas->id]) }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 15000,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        }).done(function(res) {
            if (res && res.success) {
                toastr.success('Catatan revisi berhasil disimpan');
                $('#revisiModal').modal('hide');
                // Optionally reload page to show revision status
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                toastr.error(res.message || 'Gagal menyimpan catatan revisi');
            }
        }).fail(function(xhr, status) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                let errorMsg = 'Kesalahan validasi:\n';
                Object.keys(xhr.responseJSON.errors).forEach(key => {
                    errorMsg += `• ${xhr.responseJSON.errors[key][0]}\n`;
                });
                toastr.error(errorMsg);
            } else {
                toastr.error('Gagal menyimpan catatan revisi');
            }
        }).always(function() {
            $submitBtn.prop('disabled', false).html(originalHtml);
        });
    });

    // Reset form when modal is closed
    $('#revisiModal').on('hidden.bs.modal', function() {
        $('#revisiForm')[0].reset();
    });
});
</script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Keluhan DataTable only if the table exists (tahapan_id > 2)
    if ($('#keluhanTable').length > 0) {
        $('#keluhanTable').DataTable({
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
                emptyTable: "Tidak ada data keluhan yang tersedia",
                zeroRecords: "Tidak ada data yang cocok"
            },
            columns: [
                { data: null, orderable: false, searchable: false, width: '5%' },
                { data: 'tanggal_dilaporkan', name: 'tanggal_dilaporkan', width: '12%' },
                { data: 'keluhan', name: 'keluhan', width: '30%' },
                { data: 'kategori_keluhan', name: 'kategori_keluhan', width: '15%' },
                { data: 'jumlah_downtime', name: 'jumlah_downtime', width: '12%' },
                { data: 'tanggal_selesai', name: 'tanggal_selesai', width: '12%' },
                { data: 'status', name: 'status', width: '10%' },
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
                    targets: 2, // Keluhan column
                    render: function (data, type, row) {
                        if (data && data.length > 100) {
                            return `<div style="max-width:300px; white-space:normal;">${data.substring(0, 100)}...</div>`;
                        }
                        return `<div style="max-width:300px; white-space:normal;">${data || '-'}</div>`;
                    }
                },
                {
                    targets: 3, // Status column
                    render: function (data, type, row) {
                        let badgeClass = 'badge-secondary';
                        const statusLower = (data || '').toLowerCase();

                        switch (statusLower) {
                            case 'rendah':
                                badgeClass = 'badge-warning';
                                break;
                            case 'sedang':
                                badgeClass = 'badge-info';
                                break;
                            case 'kritis':
                                badgeClass = 'badge-danger';
                                break;
                        }

                        return `<span class="badge ${badgeClass}">${data || '-'}</span>`;
                    }
                },
                {
                    targets: 6, // Status column
                    render: function (data, type, row) {
                        let badgeClass = 'badge-secondary';
                        const statusLower = (data || '').toLowerCase();

                        switch (statusLower) {
                            case 'baru':
                                badgeClass = 'badge-warning';
                                break;
                            case 'proses':
                                badgeClass = 'badge-info';
                                break;
                            case 'selesai':
                                badgeClass = 'badge-success';
                                break;
                        }

                        return `<span class="badge ${badgeClass}">${data || '-'}</span>`;
                    }
                },
                {
                    targets: 7, // Actions
                    render: function (data, type, row) {
                        const detailUrl = '{{ route("raised-issue.detail", ":id") }}'.replace(':id', row.id);
                        return `<div class="d-flex justify-content-center align-items-center">
                                    <a href="${detailUrl}" class="text-secondary" title="Lihat Detail">
                                        <i class="fas fa-search"></i>
                                    </a>
                                </div>`;
                    }
                }
            ],
            ajax: {
                url: '{{ route('keluhan.fetch-data') }}',
                type: 'GET',
                data: {
                    puskesmas_id: '{{ $puskesmas->id }}'
                },
                dataSrc: function(json) {
                    if (json.success) {
                        return json.data;
                    } else {
                        console.error('Error loading keluhan data:', json.message);
                        return [];
                    }
                },
                error: function(xhr, error, code) {
                    console.error('AJAX Error:', error);
                    toastr.error('Gagal memuat data keluhan');
                }
            }
        });
    }
});



// Global variables for file management
let selectedFiles = [];

// Keluhan Form Management
$(document).ready(function() {
    // Character counter for description
    $('#issue_description').on('input', function() {
        const current = $(this).val().length;
        $('#char-count').text(current);

        if (current > 1000) {
            $('#char-count').addClass('text-danger');
        } else {
            $('#char-count').removeClass('text-danger');
        }
    });

    // File input change handler
    $('#file-input-multiple').on('change', function() {
        const files = Array.from(this.files);

        files.forEach(file => {
            // Check if file already exists
            if (selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                toastr.warning(`File ${file.name} sudah dipilih`);
                return;
            }

            // Check file size
            if (file.size > 5120 * 1024) { // 5MB
                toastr.warning(`File ${file.name} terlalu besar (maksimal 5MB)`);
                return;
            }

            // Check total files
            if (selectedFiles.length >= 5) {
                toastr.warning('Maksimal 5 file yang dapat diunggah');
                return;
            }

            // Add file to selected list
            selectedFiles.push(file);
        });

        // Update display
        updateFileDisplay();

        // Don't clear the input so user can see what they selected
    });

    // Form submission
    $('#addIssueForm').on('submit', function(e) {
        e.preventDefault();
        submitKeluhanForm();
    });

    // Reset form when modal closes
    $('#addIssueModal').on('hidden.bs.modal', function() {
        resetKeluhanForm();
    });

    // Function to update file display with image previews
    function updateFileDisplay() {
        const $container = $('#selected-files-container');
        const $list = $('#selected-files-list');

        if (selectedFiles.length === 0) {
            $container.hide();
            return;
        }

        $container.show();
        $list.empty();

        selectedFiles.forEach((file, index) => {
            const $fileItem = $(`
                <div class="col-md-3 col-sm-4 col-6 mb-3">
                    <div class="card">
                        <div class="card-img-top position-relative" style="height: 150px; overflow: hidden;">
                            <div id="preview-${index}" class="w-100 h-100 d-flex align-items-center justify-content-center bg-light">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger position-absolute remove-file-btn"
                                    style="top: 5px; right: 5px; z-index: 10;" data-index="${index}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="card-body p-2">
                            <h6 class="card-title text-truncate mb-1" title="${file.name}">${file.name}</h6>
                            <small class="text-muted">${formatFileSize(file.size)}</small>
                        </div>
                    </div>
                </div>
            `);
            $list.append($fileItem);

            // Create image preview
            createImagePreview(file, index);
        });

        // Update hidden inputs
        updateHiddenInputs();
    }

    // Function to create image preview
    function createImagePreview(file, index) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const $preview = $(`#preview-${index}`);
            $preview.html(`
                <img src="${e.target.result}" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;" alt="Preview">
            `);
        };
        reader.readAsDataURL(file);
    }

    // Function to update hidden inputs for form submission
    function updateHiddenInputs() {
        const $container = $('#hidden-file-inputs');
        $container.empty();

        selectedFiles.forEach((file, index) => {
            // Create a hidden file input for each selected file
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.name = 'documentation[]';
            fileInput.style.display = 'none';

            // Create a new FileList with just this file
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;

            $container.append(fileInput);
        });
    }

    // Remove file event handler
    $(document).on('click', '.remove-file-btn', function() {
        const index = parseInt($(this).data('index'));
        selectedFiles.splice(index, 1);
        updateFileDisplay();

        if (selectedFiles.length === 0) {
            toastr.info('Semua file telah dihapus');
        }
    });
});

// Handle file preview
function handleFilePreview(files) {
    const $preview = $('#documentation-preview');
    const $container = $('#preview-container');

    // Always clear previous previews first
    $container.empty();

    if (files.length === 0) {
        $preview.hide();
        return;
    }

    if (files.length > 5) {
        toastr.warning('Maksimal 5 file yang dapat diunggah');
        $('#issue_documentation').val('');
        $preview.hide();
        return;
    }

    $preview.show();

    let validFileCount = 0;

    Array.from(files).forEach(function(file, index) {
        if (file.size > 5120 * 1024) { // 5MB
            toastr.warning(`File ${file.name} terlalu besar (maksimal 5MB)`);
            return;
        }

        validFileCount++;

        const $fileItem = $(`
            <div class="file-preview-item mr-3 mb-2" style="max-width: 120px;">
                <div class="border rounded p-2 text-center">
                    <i class="fas ${getFileIcon(file.type)} fa-2x mb-1"></i>
                    <small class="d-block text-truncate" title="${file.name}">${file.name}</small>
                    <small class="text-muted">${formatFileSize(file.size)}</small>
                </div>
            </div>
        `);

        $container.append($fileItem);
    });

    // Show count of valid files
    if (validFileCount > 0) {
        const $countBadge = $(`<small class="text-muted ml-2">(${validFileCount} file${validFileCount > 1 ? 's' : ''})</small>`);
        $container.append($countBadge);
    }
}

// Get file icon based on type
function getFileIcon(type) {
    if (type.startsWith('image/')) return 'fa-image text-success';
    if (type === 'application/pdf') return 'fa-file-pdf text-danger';
    return 'fa-file text-secondary';
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Submit keluhan form
function submitKeluhanForm() {
    const $btn = $('#submitBtn');
    const $form = $('#addIssueForm');

    // Disable submit button
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengirim...');

    // Create FormData manually
    const formData = new FormData();

    // Add form fields
    formData.append('_token', $('input[name="_token"]').val());
    formData.append('issue_subject', $('#issue_subject').val());
    formData.append('issue_description', $('#issue_description').val());

    // Add selected files
    selectedFiles.forEach((file, index) => {
        formData.append('documentation[]', file);
    });

    $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#addIssueModal').modal('hide');

                // Refresh the keluhan table if it exists
                if ($('#keluhanTable').length && $.fn.DataTable.isDataTable('#keluhanTable')) {
                    $('#keluhanTable').DataTable().ajax.reload();
                }
            } else {
                toastr.error(response.message || 'Terjadi kesalahan');
            }
        },
        error: function(xhr) {
            let message = 'Terjadi kesalahan saat mengirim keluhan';

            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                if (errors) {
                    message = Object.values(errors)[0][0];
                }
            }

            toastr.error(message);
        },
        complete: function() {
            // Re-enable submit button
            $btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Kirim Keluhan');
        }
    });
}

// Reset keluhan form
function resetKeluhanForm() {
    $('#addIssueForm')[0].reset();
    $('#char-count').text('0').removeClass('text-danger');

    // Clear selected files
    selectedFiles = [];
    $('#selected-files-container').hide();
    $('#hidden-file-inputs').empty();

    // Clear file input
    $('#file-input-multiple').val('');

    // Clear old preview system
    $('#documentation-preview').hide();
    $('#preview-container').empty();
}
</script>
@endsection
