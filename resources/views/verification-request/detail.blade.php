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
        @media (max-width:640px){
            .shipment-status-flow{flex-direction:column;align-items:stretch;}
            .shipment-status-flow .sf-step{padding:0 0 1.2rem 2.7rem;text-align:left;}
            .shipment-status-flow .sf-step:not(:last-child):after{left:28px;top:56px;width:8px;height:100%;}
            .shipment-status-flow .sf-circle{margin:0 0 6px;}
        }

        /* Disabled verification switch styling */
        .custom-control-input:disabled ~ .custom-control-label {
            color: #007bff;
            font-weight: 600;
        }
        .custom-control-input:disabled ~ .custom-control-label::before {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
@endsection

@section('content')
    @php $d = optional($puskesmas->district); $r = optional($d->regency); $pv = optional($r->province); $peng = optional($puskesmas->pengiriman); @endphp

    <div class="card mb-3 shadow-sm">
        <div class="card-header py-2 pr-1 text-white d-flex align-items-center bg-warning">
            <span class="section-title-bar" style="color: white;">Current Progress</span>
        </div>
        <div class="card-body p-3">
            @php
                // Determine current progress based on Tahapan table and pengiriman status
                $showTimeline = false;
                $currentStep = 0;
                $stepMeta = [];

                // Build step metadata from Tahapan table
                if($tahapan && $tahapan->count() > 0) {
                    foreach($tahapan as $t) {
                        $stepMeta[$t->tahap_ke] = [
                            'label' => $t->tahapan,
                            'icon' => $t->tahap_ke == 1 ? 'fas fa-box-open' :
                                     ($t->tahap_ke == 2 ? 'fas fa-truck' :
                                     ($t->tahap_ke == 3 ? 'fas fa-box' :
                                     ($t->tahap_ke == 4 ? 'fas fa-tools' :
                                     ($t->tahap_ke == 5 ? 'fas fa-pen-square' :
                                     ($t->tahap_ke == 6 ? 'fas fa-chalkboard-teacher' :
                                     ($t->tahap_ke == 7 ? 'fas fa-clipboard-check' : 'fas fa-check-circle'))))))
                        ];
                    }
                }

                // Determine current progress based on pengiriman data
                if($peng) {
                    $showTimeline = true;

                    // Check conditions for each stage
                    $hasShipping = !empty($peng->tgl_pengiriman);
                    $hasResi = !empty($peng->resi);
                    $hasReceipt = !empty($peng->tgl_diterima) || !empty($peng->link_tanda_terima);
                    $hasInstallation = $puskesmas->ujiFungsi && !empty($puskesmas->ujiFungsi->tgl_instalasi);
                    $hasTest = $puskesmas->ujiFungsi && !empty($puskesmas->ujiFungsi->tgl_uji_fungsi);
                    $hasVerification = !empty($peng->verif_kemenkes);

                    // Set current step based on tahapan_id or progress conditions
                    if($peng->tahapan_id) {
                        $currentStep = $peng->tahapan_id;
                    } else {
                        // Fallback logic if tahapan_id is not set
                        if(!$hasShipping) {
                            $currentStep = 0; // Not started
                        } elseif($hasShipping && !$hasResi) {
                            $currentStep = 1; // Preparation/Shipping
                        } elseif($hasResi && !$hasReceipt) {
                            $currentStep = 2; // Delivery
                        } elseif($hasReceipt && !$hasTest) {
                            $currentStep = 3; // Installation/Testing
                        } elseif($hasTest && !$hasVerification) {
                            $currentStep = 4; // Documentation
                        } else {
                            $currentStep = count($stepMeta); // Completed
                        }
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
                        @endphp
                        <div class="sf-step {{ $cls }} {{ $i == count($stepMeta) ? 'final' : '' }}" data-step="{{ $i }}">
                            <div class="sf-circle"><i class="{{ $meta['icon'] }}"></i></div>
                            <div class="sf-label">{{ $meta['label'] }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">Current Stage: <strong>{{ $stepMeta[$currentStep]['label'] ?? 'Unknown' }}</strong></small>
                        </div>
                        <div class="col-md-6 text-right">
                            <small class="text-muted">Progress: {{ $currentStep }}/{{ count($stepMeta) }}</small>
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
                <span class="section-title-bar">Basic Information</span>
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
                <tr><td>PIC Puskesmas (Petugas ASPAK)</td><td>{{ $puskesmas->pic ?? '-' }}</td></tr>
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
               <h5 class="modal-title">Edit Basic Information</h5>
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
                        <label class="small mb-1">PIC Puskesmas (Petugas ASPAK)</label>
                        <input type="text" class="form-control form-control-sm" name="pic" value="{{ $puskesmas->pic }}">
                     </div>
                  </div>
                  <div class="mb-2 pb-1 border-bottom"><strong class="text-muted small">Kontak & Penanggung Jawab</strong></div>
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
                    <span class="section-title-bar">Delivery Information</span>
                    @if(auth()->user() && auth()->user()->role->role_name == 'endo')
                    <button class="btn btn-sm btn-success ml-auto" data-toggle="modal" data-target="#deliveryModal"><i class="fas fa-edit"></i> Edit</button>
                    @endif
                </div>
                <div class="card-body p-3">
                    <table class="table table-sm table-borderless table-kv mb-0">
                        <tr><td>Tanggal Pengiriman</td><td>{{ optional($peng->tgl_pengiriman)->format('d F Y') ?? '-' }}</td></tr>
                        <tr><td>ETA</td><td>{{ $peng->eta ? $peng->eta . ' Days' : '-' }}</td></tr>
                        <tr><td>RESI</td><td>{{ $peng->resi ?? '-' }}</td></tr>
                        <tr><td>Link Tracking</td><td>@if($peng && $peng->tracking_link)<a class="text-decoration-none" target="_blank" href="{{ $peng->tracking_link }}">View Here</a>@else - @endif</td></tr>
                        <tr><td>Serial Number</td><td>{{ $peng->equipment->serial_number ?? '-' }}</td></tr>
                        <tr><td>Target Alat Diterima</td><td>{{ optional($peng->target_tgl)->format('d F Y') ?? '-' }}</td></tr>
                        <tr><td>Tanggal Diterima</td><td>{{ optional($peng->tgl_diterima)->format('d F Y') ?? '-' }}</td></tr>
                        <tr><td>Nama Penerima</td><td>{{ $peng->nama_penerima ?? '-' }}</td></tr>
                        <tr><td>Jabatan Penerima</td><td>{{ $peng->jabatan_penerima ?? '-' }}</td></tr>
                        <tr><td>Instansi Penerima</td><td>{{ $peng->instansi_penerima ?? '-' }}</td></tr>
                        <tr><td>Nomor HP Penerima</td><td>{{ $peng->nomor_penerima ?? '-' }}</td></tr>
                        <tr><td>Status Penerimaan</td><td>{{ ($peng && $peng->tgl_diterima) ? 'Diterima' : '-' }}</td></tr>
                        <tr><td>Bukti Tanda Terima</td><td>@if($peng && $peng->link_tanda_terima)<a class="text-decoration-none" target="_blank" href="{{ asset('storage/' . $peng->link_tanda_terima) }}">View Here</a>@else - @endif</td></tr>
                        <tr><td>Catatan</td><td>{{ $peng->catatan ?? '-' }}</td></tr>
                        <tr>
                            <td>Verifikasi Kemenkes</td>
                            <td>
                                <form id="verifDeliveryForm">
                                    @csrf
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="verifiedDelivery" name="verif_kemenkes"
                                                {{ ($peng && $peng->verif_kemenkes) ? 'checked disabled' : '' }}>
                                            <label class="custom-control-label" for="verifiedDelivery">
                                                {{ ($peng && $peng->verif_kemenkes) ? 'Verified' : 'Verified' }}
                                            </label>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <tr><td>Tanggal Verifikasi</td><td>{{ $peng->tgl_verif_kemenkes ? $peng->tgl_verif_kemenkes->setTimezone('Asia/Jakarta')->format('d F Y H:i') . ' WIB' : '-' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-3 shadow-sm">
        <div class="card-header py-2 pr-1 text-white d-flex align-items-center" style="background:#3f0fa8;">
            <span class="section-title-bar">Uji Fungsi</span>
            <button class="btn btn-sm ml-auto" style="background:#3f0fa8; color:white;" data-toggle="modal" data-target="#ujiFungsiModal"><i class="fas fa-edit"></i> Edit</button>
        </div>
        @php $uji = optional($puskesmas->ujiFungsi); @endphp
        <div class="card-body p-3">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless table-kv mb-0">
                        <tr><td>Tanggal Instalasi</td><td>{{ optional($uji->tgl_instalasi)->format('d F Y') ?? '-' }}</td></tr>
                        <tr><td>Berita Acara Instalasi</td><td>@if($uji && $uji->doc_instalasi)<a class="text-decoration-none" target="_blank" href="{{ asset('storage/' . $uji->doc_instalasi) }}">View Here</a>@else - @endif</td></tr>
                        <tr><td>Target Tanggal Uji Fungsi</td><td>{{ optional($uji->target_tgl_uji_fungsi)->format('d F Y') ?? '-' }}</td></tr>
                        <tr><td>Tanggal Uji Fungsi</td><td>{{ optional($uji->tgl_uji_fungsi)->format('d F Y') ?? '-' }}</td></tr>
                        <tr><td>Berita Acara Uji Fungsi</td><td>@if($uji && $uji->doc_uji_fungsi)<a class="text-decoration-none" target="_blank" href="{{ asset('storage/' . $uji->doc_uji_fungsi) }}">View Here</a>@else - @endif</td></tr>
                        <tr><td>Tanggal Pelatihan Alat</td><td>{{ optional($uji->tgl_pelatihan)->format('d F Y') ?? '-' }}</td></tr>
                        <tr><td>Berita Acara Pelatihan Alat</td><td>@if($uji && $uji->doc_pelatihan)<a class="text-decoration-none" target="_blank" href="{{ asset('storage/' . $uji->doc_pelatihan) }}">View Here</a>@else - @endif</td></tr>
                        <tr><td>Catatan</td><td>{{ $uji->catatan ?? '-' }}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless table-kv mb-0">
                        <tr>
                            <td>Verifikasi Kemenkes</td>
                            <td>
                                <form id="verifUjiFungsiForm">
                                    @csrf
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" name="verif_kemenkes" id="verifiedUjiFungsi"
                                                {{ ($uji && $uji->verif_kemenkes) ? 'checked disabled' : '' }}>
                                            <label class="custom-control-label" for="verifiedUjiFungsi">
                                                {{ ($uji && $uji->verif_kemenkes) ? 'Verified ' : 'Verified' }}
                                            </label>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <tr><td>Tanggal Verifikasi</td><td>{{ $uji->tgl_verif_kemenkes ? $uji->tgl_verif_kemenkes->setTimezone('Asia/Jakarta')->format('d F Y H:i') . ' WIB' : '-' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @php $doc = optional($puskesmas->document); @endphp
    <div class="card shadow-sm mb-4">
        <div class="card-header py-2 pr-1 bg-secondary text-white d-flex align-items-center">
            <span class="section-title-bar">Dokumen</span>
            <button class="btn btn-sm btn-secondary ml-auto" data-toggle="modal" data-target="#documentsModal"><i class="fas fa-edit"></i> Edit</button>
        </div>
        <div class="card-body p-3">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless table-kv mb-0">
                        <tr><td>Berita Acara BASTO</td><td>@if($doc && $doc->basto)<a class="text-decoration-none" target="_blank" href="{{ asset('storage/' . $doc->basto) }}">View Here</a>@else - @endif</td></tr>
                        <tr><td>Berita Acara Kalibrasi</td><td>@if($doc && $doc->kalibrasi)<a class="text-decoration-none" target="_blank" href="{{ asset('storage/' . $doc->kalibrasi) }}">View Here</a>@else - @endif</td></tr>
                        <tr><td>Berita Acara BAST</td><td>@if($doc && $doc->bast)<a class="text-decoration-none" target="_blank" href="{{ asset('storage/' . $doc->bast) }}">View Here</a>@else - @endif</td></tr>
                        <tr><td>Berita Acara ASPAK</td><td>@if($doc && $doc->aspak)<a class="text-decoration-none" target="_blank" href="{{ asset('storage/' . $doc->aspak) }}">View Here</a>@else - @endif</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless table-kv mb-0">
                        <tr>
                            <td>Verifikasi Kemenkes</td>
                            <td>
                                <form id="verifDocumentForm">
                                    @csrf
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" name="verif_kemenkes" class="custom-control-input" id="verifiedDocument"
                                                {{ ($doc && $doc->verif_kemenkes) ? 'checked disabled' : '' }}>
                                            <label class="custom-control-label" for="verifiedDocument">
                                                {{ ($doc && $doc->verif_kemenkes) ? 'Verified' : 'Verified' }}
                                            </label>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <tr><td>Tanggal Verifikasi</td><td>{{ $doc->tgl_verif_kemenkes ? $doc->tgl_verif_kemenkes->setTimezone('Asia/Jakarta')->format('d F Y H:i') . ' WIB' : '-' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tracking Delivery Orders -->
    @if(!empty($peng->resi))
    <div class="card shadow-sm mb-4" id="trackingCard" data-initial-do="{{ $peng->resi }}">
        <div class="card-header py-2 pr-1 bg-info text-white d-flex align-items-center">
            <span class="section-title-bar">Delivery Progress Tracking</span>
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

    <!-- Modal Delivery Information -->
    @if(auth()->user() && auth()->user()->role->role_name == 'endo')
    <div class="modal fade" id="deliveryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Edit Delivery Information</h5>
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
                                <label class="small mb-1">ETA (Hari)</label>
                                <input type="number" class="form-control form-control-sm" name="eta" value="{{ $peng->eta }}" min="0" max="365">
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
                                <input type="text" class="form-control form-control-sm" name="serial_number" value="{{ $peng->equipment->serial_number ?? '' }}" placeholder="Enter serial number">
                                <small class="form-text text-muted">System will create equipment record if new</small>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="small mb-1">Target Alat Diterima</label>
                                <input type="date" class="form-control form-control-sm" name="target_tgl" value="{{ optional($peng->target_tgl)->format('Y-m-d') }}">
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
                                <label class="small mb-1 d-flex align-items-center">Berita Acara BASTO <span class="ml-1 badge badge-light border">pdf</span></label>
                                <input type="file" name="basto" class="form-control-file" accept="application/pdf">
                                @if($doc && $doc->basto)<small class="d-block mt-1"><a target="_blank" href="{{ asset('storage/' . $doc->basto) }}">File Saat Ini</a></small>@endif
                            </div>
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
                const $deliveryTable = $('.card-header:contains("Delivery Information")').next('.card-body').find('table.table-kv');

                // Update delivery information fields
                if(data.tgl_pengiriman !== undefined) $deliveryTable.find('tr:contains("Tanggal Pengiriman") td:last').text(data.tgl_pengiriman || '-');
                if(data.eta !== undefined) $deliveryTable.find('tr:contains("ETA") td:last').text(data.eta ? data.eta + ' Days' : '-');
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
                if(data.target_tgl !== undefined) $deliveryTable.find('tr:contains("Target Alat Diterima") td:last').text(data.target_tgl || '-');
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

// Delivery Verification handler with SweetAlert
$(function(){
    const $form = $('#verifDeliveryForm');
    const $verificationSwitch = $('#verifiedDelivery');
    if(!$form.length || !$verificationSwitch.length) return;

    const updateUrl = '{{ route('api-verification-request.delivery-verification', ['id' => $puskesmas->id]) }}';
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

        const title = isChecked ? 'Verifikasi Data Pengiriman' : 'Batalkan Verifikasi';
        const text = isChecked ?
            'Apakah Anda yakin ingin memverifikasi data pengiriman ini?' :
            'Apakah Anda yakin ingin membatalkan verifikasi data pengiriman ini?';
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
                updateVerificationStatus(isChecked);
            }
            // If cancelled, the switch will remain in its previous state
        });
    });

    function updateVerificationStatus(verified) {
        isUpdating = true;

        // Show loading state
        Swal.fire({
            title: 'Memproses...',
            text: 'Sedang memperbarui status verifikasi',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const requestData = {
            verif_kemenkes: verified
        };
        console.log('Sending verification data:', requestData);

        $.ajax({
            url: updateUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: requestData,
            timeout: 10000
        }).done(function(res) {
            if (res && res.success) {
                // Update the switch state
                $verificationSwitch.prop('checked', verified);

                // If verified, disable the switch and update label
                if (verified) {
                    $verificationSwitch.prop('disabled', true);
                    $verificationSwitch.next('label').text('Verified ');
                }

                // Update the verification date in the table if provided
                if (res.data && res.data.tgl_verif_kemenkes !== undefined) {
                    const $verifDateCell = $('tr:contains("Tanggal Verifikasi") td:last');
                    $verifDateCell.text(res.data.tgl_verif_kemenkes || '-');
                }

                // Show success message
                Swal.fire({
                    title: 'Berhasil!',
                    text: res.message,
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
                    text: res.message || 'Gagal memperbarui status verifikasi',
                    icon: 'error'
                });
            }
        }).fail(function(xhr, status) {
            // Reset switch state
            $verificationSwitch.prop('checked', !verified);

            let errorMessage = 'Terjadi kesalahan sistem';

            if (status === 'timeout') {
                errorMessage = 'Permintaan timeout, periksa koneksi internet Anda';
            } else if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.status === 404) {
                errorMessage = 'Data tidak ditemukan';
            } else if (xhr.status === 403) {
                errorMessage = 'Anda tidak memiliki akses untuk melakukan tindakan ini';
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
});

// Uji Fungsi Verification handler with SweetAlert
$(function(){
    const $form = $('#verifUjiFungsiForm');
    const $verificationSwitch = $('#verifiedUjiFungsi');
    if(!$form.length || !$verificationSwitch.length) return;

    const updateUrl = '{{ route('api-verification-request.ujifungsi-verification', ['id' => $puskesmas->id]) }}';
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

        const title = isChecked ? 'Verifikasi Data Uji Fungsi' : 'Batalkan Verifikasi';
        const text = isChecked ?
            'Apakah Anda yakin ingin memverifikasi data uji fungsi ini?' :
            'Apakah Anda yakin ingin membatalkan verifikasi data uji fungsi ini?';
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
                updateUjiFungsiVerificationStatus(isChecked);
            }
            // If cancelled, the switch will remain in its previous state
        });
    });

    function updateUjiFungsiVerificationStatus(verified) {
        isUpdating = true;

        // Show loading state
        Swal.fire({
            title: 'Memproses...',
            text: 'Sedang memperbarui status verifikasi',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const requestData = {
            verif_kemenkes: verified
        };
        console.log('Sending uji fungsi verification data:', requestData);

        $.ajax({
            url: updateUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: requestData,
            timeout: 10000
        }).done(function(res) {
            if (res && res.success) {
                // Update the switch state
                $verificationSwitch.prop('checked', verified);

                // If verified, disable the switch and update label
                if (verified) {
                    $verificationSwitch.prop('disabled', true);
                    $verificationSwitch.next('label').text('Verified ');
                }

                // Update the verification date in the table if provided
                if (res.data && res.data.tgl_verif_kemenkes !== undefined) {
                    const $verifDateCell = $('tr:contains("Tanggal Verifikasi") td:last');
                    $verifDateCell.text(res.data.tgl_verif_kemenkes || '-');
                }

                // Show success message
                Swal.fire({
                    title: 'Berhasil!',
                    text: res.message,
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
                    text: res.message || 'Gagal memperbarui status verifikasi',
                    icon: 'error'
                });
            }
        }).fail(function(xhr, status) {
            // Reset switch state
            $verificationSwitch.prop('checked', !verified);

            let errorMessage = 'Terjadi kesalahan sistem';

            if (status === 'timeout') {
                errorMessage = 'Permintaan timeout, periksa koneksi internet Anda';
            } else if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.status === 404) {
                errorMessage = 'Data tidak ditemukan';
            } else if (xhr.status === 403) {
                errorMessage = 'Anda tidak memiliki akses untuk melakukan tindakan ini';
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
});

// Document Verification handler with SweetAlert
$(function(){
    const $form = $('#verifDocumentForm');
    const $verificationSwitch = $('#verifiedDocument');
    if(!$form.length || !$verificationSwitch.length) return;

    const updateUrl = '{{ route('api-verification-request.document-verification', ['id' => $puskesmas->id]) }}';
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

        const title = isChecked ? 'Verifikasi Data Dokumen' : 'Batalkan Verifikasi';
        const text = isChecked ?
            'Apakah Anda yakin ingin memverifikasi data dokumen ini?' :
            'Apakah Anda yakin ingin membatalkan verifikasi data dokumen ini?';
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
                updateDocumentVerificationStatus(isChecked);
            }
            // If cancelled, the switch will remain in its previous state
        });
    });

    function updateDocumentVerificationStatus(verified) {
        isUpdating = true;

        // Show loading state
        Swal.fire({
            title: 'Memproses...',
            text: 'Sedang memperbarui status verifikasi',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const requestData = {
            verif_kemenkes: verified
        };
        console.log('Sending document verification data:', requestData);

        $.ajax({
            url: updateUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: requestData,
            timeout: 10000
        }).done(function(res) {
            if (res && res.success) {
                // Update the switch state
                $verificationSwitch.prop('checked', verified);

                // If verified, disable the switch and update label
                if (verified) {
                    $verificationSwitch.prop('disabled', true);
                    $verificationSwitch.next('label').text('Verified');
                }

                // Update the verification date in the table if provided
                if (res.data && res.data.tgl_verif_kemenkes !== undefined) {
                    const $verifDateCell = $('tr:contains("Tanggal Verifikasi") td:last');
                    $verifDateCell.text(res.data.tgl_verif_kemenkes || '-');
                }

                // Show success message
                Swal.fire({
                    title: 'Berhasil!',
                    text: res.message,
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
                    text: res.message || 'Gagal memperbarui status verifikasi',
                    icon: 'error'
                });
            }
        }).fail(function(xhr, status) {
            // Reset switch state
            $verificationSwitch.prop('checked', !verified);

            let errorMessage = 'Terjadi kesalahan sistem';

            if (status === 'timeout') {
                errorMessage = 'Permintaan timeout, periksa koneksi internet Anda';
            } else if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.status === 404) {
                errorMessage = 'Data tidak ditemukan';
            } else if (xhr.status === 403) {
                errorMessage = 'Anda tidak memiliki akses untuk melakukan tindakan ini';
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
});
</script>
@endsection
