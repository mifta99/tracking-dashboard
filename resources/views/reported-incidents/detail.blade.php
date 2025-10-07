@extends('adminlte::page')

@section('title', 'Detail Insiden')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="mb-0">Detail Insiden</h1>
        <a href="{{ route('reported-incidents.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>
@stop

@section('content')
    @php
        $formatDate = function ($value, $fallback = 'NULL') {
            if (empty($value)) {
                return $fallback;
            }

            try {
                return \Illuminate\Support\Carbon::parse($value)->translatedFormat('d F Y');
            } catch (\Throwable $th) {
                return $fallback;
            }
        };

        $incident = $incident instanceof \Illuminate\Support\Collection ? $incident->first() : $incident;

        if (is_null($incident)) {
            $incident = new \Illuminate\Support\Fluent();
        }

        $badgeClass = [
            'open' => 'danger',
            'opened' => 'danger',
            'baru' => 'secondary',
            'in_progress' => 'warning',
            'proses' => 'warning',
            'closed' => 'success',
            'selesai' => 'success',
        ];

        $statusRelation = data_get($incident, 'status');
        $statusSlug = data_get($statusRelation, 'slug');
        $statusName = data_get($statusRelation, 'name');

        $statusKey = $statusSlug
            ?? ($statusName ? \Illuminate\Support\Str::slug($statusName, '_') : null)
            ?? data_get($incident, 'status_id');

        $statusLabel = $statusName
            ?? ($statusKey ? \Illuminate\Support\Str::of($statusKey)->replace('_', ' ')->title() : 'NULL');
    @endphp

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-file-alt"></i> Ringkasan Insiden
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="mb-0">
                                <dt class="text-muted text-uppercase small">Tanggal Kejadian</dt>
                                <dd class="h6">{{ $formatDate($incident->tgl_kejadian) }}</dd>

                                <dt class="text-muted text-uppercase small">Nama Korban</dt>
                                <dd>{{ $incident->nama_korban ?? 'NULL' }}</dd>

                                <dt class="text-muted text-uppercase small">Bagian/Unit</dt>
                                <dd>{{ $incident->bagian ?? 'NULL' }}</dd>

                                <dt class="text-muted text-uppercase small">Dilaporkan Oleh</dt>
                                <dd>{{ optional($incident->reporter)->name ?? 'NULL' }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="mb-0">
                                <dt class="text-muted text-uppercase small">Puskesmas</dt>
                                <dd>{{ optional($incident->puskesmas)->name ?? 'NULL' }}</dd>

                                <dt class="text-muted text-uppercase small">Tahapan</dt>
                                <dd>{{ optional($incident->tahapan)->name ?? 'NULL' }}</dd>

                                <dt class="text-muted text-uppercase small">Kategori Insiden</dt>
                                <dd>{{ optional($incident->kategoriInsiden)->name ?? 'NULL' }}</dd>

                                <dt class="text-muted text-uppercase small">Status</dt>
                                <dd>
                                    <span
                                        class="badge badge-pill badge-{{ $badgeClass[strtolower($statusKey)] ?? 'secondary' }} px-3 py-2 text-sm">
                                        {{ $statusLabel }}
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Rincian Insiden
                    </h3>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="text-uppercase text-muted small mb-2">Judul Insiden</h5>
                        <p class="lead mb-0">{{ $incident->insiden ?? 'NULL' }}</p>
                    </div>

                    <div>
                        <h5 class="text-uppercase text-muted small mb-2">Kronologis</h5>
                        <p class="mb-0 text-justify">{{ $incident->kronologis ?? 'Belum ada kronologis yang diinput.' }}</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-tools"></i> Tindakan Koreksi
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="mb-0">
                                <dt class="text-muted text-uppercase small">Rencana Tindakan</dt>
                                <dd>{{ $incident->rencana_tindakan_koreksi ?? 'NULL' }}</dd>

                                <dt class="text-muted text-uppercase small">Pelaksana</dt>
                                <dd>{{ $incident->pelaksana_tindakan_koreksi ?? 'NULL' }}</dd>

                                <dt class="text-muted text-uppercase small">Tanggal Selesai</dt>
                                <dd>{{ $formatDate($incident->tgl_selesai_koreksi) }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="mb-0">
                                <dt class="text-muted text-uppercase small">Hasil Verifikasi</dt>
                                <dd>{{ $incident->verifikasi_hasil_koreksi ?? 'NULL' }}</dd>

                                <dt class="text-muted text-uppercase small">Tanggal Verifikasi</dt>
                                <dd>{{ $formatDate($incident->verifikasi_tgl_koreksi) }}</dd>

                                <dt class="text-muted text-uppercase small">Diverifikasi Oleh</dt>
                                <dd>{{ $incident->verifikasi_pelaksana_koreksi ?? 'NULL' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-clipboard-check"></i> Tindakan Korektif
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="mb-0">
                                <dt class="text-muted text-uppercase small">Rencana Tindakan</dt>
                                <dd>{{ $incident->rencana_tindakan_korektif ?? 'NULL' }}</dd>

                                <dt class="text-muted text-uppercase small">Pelaksana</dt>
                                <dd>{{ $incident->pelaksana_tindakan_korektif ?? 'NULL' }}</dd>

                                <dt class="text-muted text-uppercase small">Tanggal Selesai</dt>
                                <dd>{{ $formatDate($incident->tgl_selesai_korektif) }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="mb-0">
                                <dt class="text-muted text-uppercase small">Hasil Verifikasi</dt>
                                <dd>{{ $incident->verifikasi_hasil_korektif ?? 'NULL' }}</dd>

                                <dt class="text-muted text-uppercase small">Tanggal Verifikasi</dt>
                                <dd>{{ $formatDate($incident->verifikasi_tgl_korektif) }}</dd>

                                <dt class="text-muted text-uppercase small">Diverifikasi Oleh</dt>
                                <dd>{{ $incident->verifikasi_pelaksana_korektif ?? 'NULL' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Informasi Tambahan
                    </h3>
                </div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt class="text-muted text-uppercase small">Tanggal Dibuat</dt>
                        <dd>{{ $formatDate($incident->created_at) }}</dd>

                        <dt class="text-muted text-uppercase small">Terakhir Diperbarui</dt>
                        <dd>{{ $formatDate($incident->updated_at) }}</dd>

                        <dt class="text-muted text-uppercase small">Dokumentasi</dt>
                        <dd>
                            @if (!empty($incident->dokumentasi))
                                <div class="d-flex flex-column gap-1">
                                    @php
                                        $documents = is_array($incident->dokumentasi)
                                            ? $incident->dokumentasi
                                            : explode(',', (string) $incident->dokumentasi);
                                    @endphp
                                    @foreach ($documents as $index => $doc)
                                        @php
                                            $cleanDoc = trim($doc);
                                        @endphp
                                        @if (!empty($cleanDoc))
                                            <a href="{{ $cleanDoc }}" target="_blank" class="text-primary">
                                                <i class="fas fa-paperclip"></i> Lampiran {{ $index + 1 }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted">Belum ada dokumentasi.</span>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-history"></i> Riwayat Status
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">
                        Riwayat status detail belum tersedia. Catat perubahan status melalui modul administrasi untuk
                        menampilkan riwayat di sini.
                    </p>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .text-sm {
            font-size: 0.8125rem;
        }

        .gap-1 > * + * {
            margin-top: 0.25rem;
        }
    </style>
@stop
