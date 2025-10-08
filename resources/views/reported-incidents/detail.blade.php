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
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-file-alt"></i> Ringkasan Insiden
                    </h3>
                    <button type="button" class="btn btn-sm btn-light" onclick="toggleEditMode('basic-info')">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
                <div class="card-body">
                    <!-- Display Mode -->
                    <div id="basic-info-display" class="row">
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

                    <!-- Edit Mode -->
                    <div id="basic-info-edit" class="row" style="display: none;">
                        <form id="basic-info-form" class="w-100">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-muted text-uppercase small">Tanggal Kejadian</label>
                                        <input type="date" class="form-control form-control-sm" name="tgl_kejadian" value="{{ $incident->tgl_kejadian ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label class="text-muted text-uppercase small">Nama Korban</label>
                                        <input type="text" class="form-control form-control-sm" name="nama_korban" value="{{ $incident->nama_korban ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label class="text-muted text-uppercase small">Bagian/Unit</label>
                                        <input type="text" class="form-control form-control-sm" name="bagian" value="{{ $incident->bagian ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-muted text-uppercase small">Tahapan</label>
                                        <select class="form-control form-control-sm" name="tahapan_id">
                                            <option value="">Pilih Tahapan</option>
                                            <!-- Options will be populated via JavaScript -->
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="text-muted text-uppercase small">Kategori Insiden</label>
                                        <select class="form-control form-control-sm" name="kategori_id">
                                            <option value="">Pilih Kategori</option>
                                            <!-- Options will be populated via JavaScript -->
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="text-muted text-uppercase small">Status</label>
                                        <select class="form-control form-control-sm" name="status_id">
                                            <option value="">Pilih Status</option>
                                            <!-- Options will be populated via JavaScript -->
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success btn-sm" onclick="saveSection('basic-info')">
                                            <i class="fas fa-save"></i> Simpan
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="cancelEdit('basic-info')">
                                            <i class="fas fa-times"></i> Batal
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Rincian Insiden
                    </h3>
                    <button type="button" class="btn btn-sm btn-light" onclick="toggleEditMode('incident-details')">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
                <div class="card-body">
                    <!-- Display Mode -->
                    <div id="incident-details-display">
                        <div class="mb-4">
                            <h5 class="text-uppercase text-muted small mb-2">Judul Insiden</h5>
                            <p class="lead mb-0">{{ $incident->insiden ?? 'NULL' }}</p>
                        </div>

                        <div>
                            <h5 class="text-uppercase text-muted small mb-2">Kronologis</h5>
                            <p class="mb-0 text-justify">{{ $incident->kronologis ?? 'Belum ada kronologis yang diinput.' }}</p>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div id="incident-details-edit" style="display: none;">
                        <form id="incident-details-form">
                            @csrf
                            <div class="form-group">
                                <label class="text-uppercase text-muted small">Judul Insiden</label>
                                <input type="text" class="form-control" name="insiden" value="{{ $incident->insiden ?? '' }}" placeholder="Masukkan judul insiden">
                            </div>
                            <div class="form-group">
                                <label class="text-uppercase text-muted small">Kronologis</label>
                                <textarea class="form-control" name="kronologis" rows="5" placeholder="Deskripsikan kronologis kejadian secara detail...">{{ $incident->kronologis ?? '' }}</textarea>
                            </div>
                            <div class="form-group">
                                <label class="text-uppercase text-muted small">Dokumentasi</label>
                                <input type="file" class="form-control-file" name="dokumentasi[]" multiple accept="image/*,application/pdf">
                                <small class="form-text text-muted">Upload foto atau dokumen pendukung (JPG, PNG, PDF)</small>
                                @if (!empty($incident->dokumentasi))
                                    <div class="mt-2">
                                        <small class="text-muted">Dokumentasi saat ini:</small>
                                        @php
                                            $documents = is_array($incident->dokumentasi) ? $incident->dokumentasi : explode(',', (string) $incident->dokumentasi);
                                        @endphp
                                        @foreach ($documents as $index => $doc)
                                            @php $cleanDoc = trim($doc); @endphp
                                            @if (!empty($cleanDoc))
                                                <br><small><i class="fas fa-paperclip"></i> <a href="{{ $cleanDoc }}" target="_blank">Lampiran {{ $index + 1 }}</a></small>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-success btn-sm" onclick="saveSection('incident-details')">
                                    <i class="fas fa-save"></i> Simpan
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="cancelEdit('incident-details')">
                                    <i class="fas fa-times"></i> Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-tools"></i> Tindakan Koreksi
                    </h3>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEditMode('tindakan-koreksi')">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
                <div class="card-body">
                    <!-- Display Mode -->
                    <div id="tindakan-koreksi-display" class="row">
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

                    <!-- Edit Mode -->
                    <div id="tindakan-koreksi-edit" class="row" style="display: none;">
                        <form id="tindakan-koreksi-form" class="w-100">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-muted text-uppercase small">Rencana Tindakan</label>
                                        <textarea class="form-control form-control-sm" name="rencana_tindakan_koreksi" rows="3" placeholder="Deskripsikan rencana tindakan koreksi">{{ $incident->rencana_tindakan_koreksi ?? '' }}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label class="text-muted text-uppercase small">Pelaksana</label>
                                        <input type="text" class="form-control form-control-sm" name="pelaksana_tindakan_koreksi" value="{{ $incident->pelaksana_tindakan_koreksi ?? '' }}" placeholder="Nama pelaksana">
                                    </div>
                                    <div class="form-group">
                                        <label class="text-muted text-uppercase small">Tanggal Selesai</label>
                                        <input type="date" class="form-control form-control-sm" name="tgl_selesai_koreksi" value="{{ $incident->tgl_selesai_koreksi ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-muted text-uppercase small">Hasil Verifikasi</label>
                                        <textarea class="form-control form-control-sm" name="verifikasi_hasil_koreksi" rows="3" placeholder="Hasil verifikasi tindakan koreksi">{{ $incident->verifikasi_hasil_koreksi ?? '' }}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label class="text-muted text-uppercase small">Tanggal Verifikasi</label>
                                        <input type="date" class="form-control form-control-sm" name="verifikasi_tgl_koreksi" value="{{ $incident->verifikasi_tgl_koreksi ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label class="text-muted text-uppercase small">Diverifikasi Oleh</label>
                                        <input type="text" class="form-control form-control-sm" name="verifikasi_pelaksana_koreksi" value="{{ $incident->verifikasi_pelaksana_koreksi ?? '' }}" placeholder="Nama verifikator">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success btn-sm" onclick="saveSection('tindakan-koreksi')">
                                            <i class="fas fa-save"></i> Simpan
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="cancelEdit('tindakan-koreksi')">
                                            <i class="fas fa-times"></i> Batal
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-clipboard-check"></i> Tindakan Korektif
                    </h3>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEditMode('tindakan-korektif')">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
                <div class="card-body">
                    <!-- Display Mode -->
                    <div id="tindakan-korektif-display" class="row">
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

                    <!-- Edit Mode -->
                    <div id="tindakan-korektif-edit" class="row" style="display: none;">
                        <form id="tindakan-korektif-form" class="w-100">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-muted text-uppercase small">Rencana Tindakan</label>
                                        <textarea class="form-control form-control-sm" name="rencana_tindakan_korektif" rows="3" placeholder="Deskripsikan rencana tindakan korektif">{{ $incident->rencana_tindakan_korektif ?? '' }}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label class="text-muted text-uppercase small">Pelaksana</label>
                                        <input type="text" class="form-control form-control-sm" name="pelaksana_tindakan_korektif" value="{{ $incident->pelaksana_tindakan_korektif ?? '' }}" placeholder="Nama pelaksana">
                                    </div>
                                    <div class="form-group">
                                        <label class="text-muted text-uppercase small">Tanggal Selesai</label>
                                        <input type="date" class="form-control form-control-sm" name="tgl_selesai_korektif" value="{{ $incident->tgl_selesai_korektif ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-muted text-uppercase small">Hasil Verifikasi</label>
                                        <textarea class="form-control form-control-sm" name="verifikasi_hasil_korektif" rows="3" placeholder="Hasil verifikasi tindakan korektif">{{ $incident->verifikasi_hasil_korektif ?? '' }}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label class="text-muted text-uppercase small">Tanggal Verifikasi</label>
                                        <input type="date" class="form-control form-control-sm" name="verifikasi_tgl_korektif" value="{{ $incident->verifikasi_tgl_korektif ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label class="text-muted text-uppercase small">Diverifikasi Oleh</label>
                                        <input type="text" class="form-control form-control-sm" name="verifikasi_pelaksana_korektif" value="{{ $incident->verifikasi_pelaksana_korektif ?? '' }}" placeholder="Nama verifikator">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success btn-sm" onclick="saveSection('tindakan-korektif')">
                                            <i class="fas fa-save"></i> Simpan
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="cancelEdit('tindakan-korektif')">
                                            <i class="fas fa-times"></i> Batal
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .text-sm {
            font-size: 0.8125rem;
        }

        .gap-1 > * + * {
            margin-top: 0.25rem;
        }

        .edit-mode {
            background-color: #f8f9fa;
            border: 1px dashed #007bff;
            border-radius: 0.25rem;
            padding: 1rem;
        }

        .btn-group .btn {
            margin-right: 0.25rem;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentIncidentId = {{ $incident->id ?? 'null' }};

        // Toggle edit mode for sections
        function toggleEditMode(sectionId) {
            const displaySection = document.getElementById(sectionId + '-display');
            const editSection = document.getElementById(sectionId + '-edit');
            
            if (displaySection && editSection) {
                if (editSection.style.display === 'none') {
                    displaySection.style.display = 'none';
                    editSection.style.display = 'block';
                    editSection.classList.add('edit-mode');
                    
                    // Load dropdown data if needed
                    if (sectionId === 'basic-info') {
                        loadDropdownData();
                    }
                } else {
                    cancelEdit(sectionId);
                }
            }
        }

        // Cancel edit mode
        function cancelEdit(sectionId) {
            const displaySection = document.getElementById(sectionId + '-display');
            const editSection = document.getElementById(sectionId + '-edit');
            
            if (displaySection && editSection) {
                displaySection.style.display = 'block';
                editSection.style.display = 'none';
                editSection.classList.remove('edit-mode');
                
                // Reset form
                const form = document.getElementById(sectionId + '-form');
                if (form) {
                    form.reset();
                }
            }
        }

        // Save section changes
        function saveSection(sectionId) {
            const form = document.getElementById(sectionId + '-form');
            if (!form) return;

            const formData = new FormData(form);
            
            // Show loading
            Swal.fire({
                title: 'Menyimpan...',
                text: 'Sedang memperbarui data insiden',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`{{ route('reported-incidents.update', '') }}/${currentIncidentId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-HTTP-Method-Override': 'PATCH'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message || 'Data berhasil diperbarui',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Reload page to show updated data
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Gagal memperbarui data');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: error.message || 'Terjadi kesalahan saat memperbarui data'
                });
            });
        }

        // Load dropdown data for select fields
        function loadDropdownData() {
            // Load Kategori Insiden
            fetch('{{ route("api.kategori-insiden") }}')
                .then(response => response.json())
                .then(data => {
                    const select = document.querySelector('select[name="kategori_id"]');
                    if (select) {
                        let options = '<option value="">Pilih Kategori</option>';
                        data.forEach(item => {
                            const selected = item.id == {{ $incident->kategori_id ?? 'null' }} ? 'selected' : '';
                            options += `<option value="${item.id}" ${selected}>${item.name}</option>`;
                        });
                        select.innerHTML = options;
                    }
                })
                .catch(error => console.log('Failed to load kategori insiden'));

            // Load Tahapan
            fetch('{{ route("api.tahapan") }}')
                .then(response => response.json())
                .then(data => {
                    const select = document.querySelector('select[name="tahapan_id"]');
                    if (select) {
                        let options = '<option value="">Pilih Tahapan</option>';
                        data.forEach(item => {
                            const selected = item.id == {{ $incident->tahapan_id ?? 'null' }} ? 'selected' : '';
                            options += `<option value="${item.id}" ${selected}>${item.tahapan}</option>`;
                        });
                        select.innerHTML = options;
                    }
                })
                .catch(error => console.log('Failed to load tahapan'));

            // Load Status Insiden
            fetch('{{ route("api.status-insiden") }}')
                .then(response => response.json())
                .then(data => {
                    const select = document.querySelector('select[name="status_id"]');
                    if (select) {
                        let options = '<option value="">Pilih Status</option>';
                        data.forEach(item => {
                            const selected = item.id == {{ $incident->status_id ?? 'null' }} ? 'selected' : '';
                            options += `<option value="${item.id}" ${selected}>${item.name}</option>`;
                        });
                        select.innerHTML = options;
                    }
                })
                .catch(error => console.log('Failed to load status insiden'));
        }
    </script>
@stop
