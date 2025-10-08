@extends('adminlte::page')

@section('title', 'Impor Data')

@section('content_header')
    <h1 style="font-size: 24px;">Impor Data</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 mb-2">
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded" role="alert">
                <div class="d-flex align-items-center justify-content-between">
                    
                    <!-- Bagian kiri: icon + teks -->
                    <div class="d-flex align-items-center">
                        <i class="fas fa-file-excel fa-2x mr-3 text-white"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Format Template Diperlukan</h6>
                            <small class="text-white-50">
                                Pastikan file <strong>XLSX</strong> mengikuti format template yang disediakan 
                                agar impor data berjalan dengan benar.
                            </small>
                        </div>
                    </div>

                    <!-- Bagian kanan: tombol -->
                                        <div class="ms-3">
                        <a href="{{ route('import-data.download.template') }}" id="download-template-btn" class="btn btn-light btn-sm shadow-sm px-3 py-2 text-decoration-none d-flex align-items-center text-secondary" onmouseover="this.classList.add('text-dark')" onmouseout="this.classList.remove('text-dark')">
                            <i class="fas fa-download me-2 text-danger"></i>
                            <span class="fw-semibold px-2">Unduh Template</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Unggah File Excel</h3>
                </div>
                {{-- {{ route('import.data') }} --}}
                <form action="{{ route('import-data.import.puskesmas') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="excel_file">Pilih File Excel</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv">
                                    <label class="custom-file-label" for="excel_file">Pilih file</label>
                                </div>
                            </div>
                            <small class="form-text text-muted">Format yang didukung: .xlsx, .xls, .csv</small>
                        </div>

                        @if(auth()->user() && auth()->user()->role && auth()->user()->role->role_name === 'endo')
                        <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="font-weight-bold mb-0">Kolom Tambahan untuk Template Ekspor <span class="text-muted">(Opsional untuk Endo)</span></label>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="toggle-additional-columns">
                                    <i class="fas fa-eye"></i> Tampilkan Opsi Tambahan
                                </button>
                            </div>
                            <div class="card" id="additional-columns-card" style="display: none;">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <!-- Informasi Puskesmas / PIC -->
                                        <div class="col-md-4">
                                            <h6 class="text-primary mb-2"><i class="fas fa-user-friends"></i> Informasi Puskesmas</h6>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="include_pic" name="additional_columns[]" value="pic" >
                                                <label class="form-check-label small" for="include_pic">PIC Puskesmas</label>
                                            </div>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="include_kepala" name="additional_columns[]" value="kepala" >
                                                <label class="form-check-label small" for="include_kepala">Kepala Puskesmas</label>
                                            </div>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="include_pic_dinkes_prov" name="additional_columns[]" value="pic_dinkes_prov" >
                                                <label class="form-check-label small" for="include_pic_dinkes_prov">PIC Dinkes Provinsi</label>
                                            </div>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="include_pic_dinkes_kab" name="additional_columns[]" value="pic_dinkes_kab" >
                                                <label class="form-check-label small" for="include_pic_dinkes_kab">PIC Dinkes Kabupaten/Kota</label>
                                            </div>
                                        </div>
                                        <!-- Delivery Information -->
                                        <div class="col-md-4">
                                            <h6 class="text-success mb-2"><i class="fas fa-truck"></i> Pengiriman</h6>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="include_tgl_pengiriman" name="additional_columns[]" value="tgl_pengiriman" checked>
                                                <label class="form-check-label small" for="include_tgl_pengiriman">Tanggal Pengiriman</label>
                                            </div>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="include_eta" name="additional_columns[]" value="eta" checked>
                                                <label class="form-check-label small" for="include_eta">ETA (Hari)</label>
                                            </div>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="include_resi" name="additional_columns[]" value="resi" checked>
                                                <label class="form-check-label small" for="include_resi">Nomor Resi</label>
                                            </div>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="include_serial_number" name="additional_columns[]" value="serial_number" checked>
                                                <label class="form-check-label small" for="include_serial_number">Serial Number</label>
                                            </div>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="include_catatan" name="additional_columns[]" value="catatan" checked>
                                                <label class="form-check-label small" for="include_catatan">Catatan</label>
                                            </div>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="include_tgl_diterima" name="additional_columns[]" value="tgl_diterima" checked>
                                                <label class="form-check-label small" for="include_tgl_diterima">Tanggal Diterima</label>
                                            </div>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="include_nama_penerima" name="additional_columns[]" value="nama_penerima" checked>
                                                <label class="form-check-label small" for="include_nama_penerima">Nama Penerima</label>
                                            </div>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="include_jabatan_penerima" name="additional_columns[]" value="jabatan_penerima" checked>
                                                <label class="form-check-label small" for="include_jabatan_penerima">Jabatan Penerima</label>
                                            </div>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="include_instansi_penerima" name="additional_columns[]" value="instansi_penerima" checked>
                                                <label class="form-check-label small" for="include_instansi_penerima">Instansi Penerima</label>
                                            </div>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="include_nomor_penerima" name="additional_columns[]" value="nomor_penerima" checked>
                                                <label class="form-check-label small" for="include_nomor_penerima">Nomor Penerima</label>
                                            </div>
                                        </div>
                                        <!-- Testing & Installation -->
                                        <div class="col-md-4">
                                            <h6 class="text-warning mb-2"><i class="fas fa-cog"></i> Pengujian & Instalasi</h6>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="include_tgl_instalasi" name="additional_columns[]" value="tgl_instalasi" checked>
                                                <label class="form-check-label small" for="include_tgl_instalasi">Tanggal Instalasi</label>
                                            </div>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="include_target_tgl_uji_fungsi" name="additional_columns[]" value="target_tgl_uji_fungsi" checked>
                                                <label class="form-check-label small" for="include_target_tgl_uji_fungsi">Target Tanggal Uji Fungsi</label>
                                            </div>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="include_tgl_uji_fungsi" name="additional_columns[]" value="tgl_uji_fungsi" checked>
                                                <label class="form-check-label small" for="include_tgl_uji_fungsi">Tanggal Uji Fungsi</label>
                                            </div>
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="include_tgl_pelatihan" name="additional_columns[]" value="tgl_pelatihan" checked>
                                                <label class="form-check-label small" for="include_tgl_pelatihan">Tanggal Pelatihan</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle"></i>
                                                    Kolom yang dipilih akan ditambahkan ke template ekspor Excel.
                                                </small>
                                                <div>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="select-all-columns">
                                                        <i class="fas fa-check-square"></i> Pilih Semua
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm ml-1" id="deselect-all-columns">
                                                        <i class="fas fa-square"></i> Batal Pilih Semua
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Unggah & Impor
                        </button>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .custom-file-label::after {
        content: "Telusuri";
    }
    
    .form-check {
        margin-bottom: 0.5rem;
    }
    
    .form-check-label.small {
        font-size: 0.875rem;
        font-weight: normal;
    }
    
    .card .card-body h6 {
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .form-check-input:checked + .form-check-label {
        font-weight: 500;
        color: #495057;
    }
    
    .btn-sm {
        font-size: 0.8rem;
        padding: 0.25rem 0.75rem;
    }
    
    .card {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Update file input label with selected filename
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).siblings('.custom-file-label').addClass("selected").html(fileName);
        });

        // Select all additional columns
        $('#select-all-columns').on('click', function() {
            $('input[name="additional_columns[]"]').prop('checked', true);
            $(this).removeClass('btn-outline-secondary').addClass('btn-primary');
            $('#deselect-all-columns').removeClass('btn-primary').addClass('btn-outline-secondary');
        });

        // Deselect all additional columns
        $('#deselect-all-columns').on('click', function() {
            $('input[name="additional_columns[]"]').prop('checked', false);
            $(this).removeClass('btn-outline-secondary').addClass('btn-primary');
            $('#select-all-columns').removeClass('btn-primary').addClass('btn-outline-secondary');
        });

        // Toggle additional columns section
        $('#toggle-additional-columns').on('click', function() {
            const $card = $('#additional-columns-card');
            const $button = $(this);
            
            if ($card.is(':visible')) {
                $card.slideUp();
                $button.html('<i class="fas fa-eye"></i> Tampilkan Opsi Tambahan');
                $button.removeClass('btn-primary').addClass('btn-outline-primary');
            } else {
                $card.slideDown();
                $button.html('<i class="fas fa-eye-slash"></i> Sembunyikan Opsi Tambahan');
                $button.removeClass('btn-outline-primary').addClass('btn-primary');
            }
        });

        // Update button states when individual checkboxes change
        $('input[name="additional_columns[]"]').on('change', function() {
            const totalCheckboxes = $('input[name="additional_columns[]"]').length;
            const checkedCheckboxes = $('input[name="additional_columns[]"]:checked').length;
            
            if (checkedCheckboxes === 0) {
                $('#select-all-columns').removeClass('btn-primary').addClass('btn-outline-secondary');
                $('#deselect-all-columns').removeClass('btn-outline-secondary').addClass('btn-primary');
            } else if (checkedCheckboxes === totalCheckboxes) {
                $('#select-all-columns').removeClass('btn-outline-secondary').addClass('btn-primary');
                $('#deselect-all-columns').removeClass('btn-primary').addClass('btn-outline-secondary');
            } else {
                $('#select-all-columns').removeClass('btn-primary').addClass('btn-outline-secondary');
                $('#deselect-all-columns').removeClass('btn-primary').addClass('btn-outline-secondary');
            }
        });

        // Initialize button states on page load
        $('input[name="additional_columns[]"]').trigger('change');

        // Update download template link when columns change
        function updateDownloadLink() {
            const baseUrl = '{{ route("import-data.download.template") }}';
            const checkedColumns = $('input[name="additional_columns[]"]:checked').map(function() {
                return this.value;
            }).get();
            
            let url = baseUrl;
            if (checkedColumns.length > 0) {
                url += '?' + $.param({
                    'additional_columns': checkedColumns
                });
            }
            
            $('#download-template-btn').attr('href', url);
        }

        // Update link when checkboxes change
        $('input[name="additional_columns[]"]').on('change', updateDownloadLink);
        
        // Update link when select all/deselect all buttons are clicked
        $('#select-all-columns, #deselect-all-columns').on('click', function() {
            setTimeout(updateDownloadLink, 100); // Delay to ensure checkboxes are updated first
        });

        // Initialize download link
        updateDownloadLink();
    });
</script>
@stop