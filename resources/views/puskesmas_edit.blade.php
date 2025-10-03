@extends('adminlte::page')

@section('title', 'Profile Puskesmas')

@section('content_header')
    <h1>Profile Puskesmas</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Regular profile display -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informasi Profile</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#profileModal">
                            <i class="fas fa-edit"></i> Edit Profile
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong><i class="fas fa-user mr-1"></i> Nama</strong>
                            <p class="text-muted">{{ $user->name ?? 'Belum diisi' }}</p>
                            <hr>

                            <strong><i class="fas fa-briefcase mr-1"></i> Jabatan</strong>
                            <p class="text-muted">{{ $user->jabatan ?? 'Belum diisi' }}</p>
                            <hr>

                            <strong><i class="fas fa-building mr-1"></i> Instansi</strong>
                            <p class="text-muted">{{ $user->instansi ?? 'Belum diisi' }}</p>
                            <hr>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-phone mr-1"></i> No. HP</strong>
                            <p class="text-muted">{{ $user->no_hp ?? 'Belum diisi' }}</p>
                            <hr>

                            <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                            <p class="text-muted">{{ $user->email }}</p>
                            <hr>

                            <strong><i class="fas fa-hospital mr-1"></i> Puskesmas</strong>
                            <p class="text-muted">Puskesmas {{ $user->puskesmas->name ?? 'Tidak terdaftar' }}</p>
                            <hr>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Profile Edit Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="profileModalLabel" 
     data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title" id="profileModalLabel">
                    @if($user->must_change_password)
                        <i class="fas fa-exclamation-triangle"></i> Lengkapi Profile Anda
                    @else
                        <i class="fas fa-edit"></i> Edit Profile
                    @endif
                </h4>
                @if(!$user->must_change_password)
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                @endif
            </div>
            <div class="modal-body">
                @if($user->must_change_password)
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        <strong>Perhatian!</strong> Anda harus melengkapi profile dan mengganti password sebelum dapat menggunakan sistem.
                    </div>
                @endif

                <form id="profileForm" method="POST" action="{{ route('puskesmas.profile.update') }}">
                    @csrf
                    
                    <div class="row">
                        <!-- Nama -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name"><i class="fas fa-user"></i> Nama Lengkap *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="{{ old('name', $user->name) }}" required>
                                <small class="form-text text-muted">Masukkan nama lengkap Anda</small>
                            </div>
                        </div>

                        <!-- Jabatan -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="jabatan"><i class="fas fa-briefcase"></i> Jabatan *</label>
                                <input type="text" class="form-control" id="jabatan" name="jabatan" 
                                       value="{{ old('jabatan', $user->jabatan) }}" required>
                                <small class="form-text text-muted">Contoh: Kepala Puskesmas, Perawat, dll</small>
                            </div>
                        </div>

                        <!-- Instansi -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="instansi"><i class="fas fa-building"></i> Instansi *</label>
                                <input type="text" class="form-control" id="instansi" name="instansi" 
                                       value="{{ old('instansi', $user->instansi) }}" required>
                                <small class="form-text text-muted">Nama instansi/organisasi tempat bekerja</small>
                            </div>
                        </div>

                        <!-- No HP -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="no_hp"><i class="fas fa-phone"></i> No. HP *</label>
                                <input type="text" class="form-control" id="no_hp" name="no_hp" 
                                       value="{{ old('no_hp', $user->no_hp) }}" required>
                                <small class="form-text text-muted">Format: 08xxxxxxxxxx</small>
                            </div>
                        </div>

                        <!-- Email (readonly) -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="{{ $user->email }}" readonly>
                                <small class="form-text text-muted">Email tidak dapat diubah</small>
                            </div>
                        </div>

                        <!-- Password Section -->
                        <div class="col-md-12">
                            <hr>
                            <h5><i class="fas fa-lock"></i> 
                                @if($user->must_change_password)
                                    Ganti Password (Wajib)
                                @else
                                    Ganti Password (Opsional)
                                @endif
                            </h5>
                        </div>

                        <!-- New Password -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password"><i class="fas fa-key"></i> Password Baru
                                    @if($user->must_change_password) * @endif
                                </label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       @if($user->must_change_password) required @endif>
                                <small class="form-text text-muted">Minimal 8 karakter</small>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password_confirmation"><i class="fas fa-key"></i> Konfirmasi Password
                                    @if($user->must_change_password) * @endif
                                </label>
                                <input type="password" class="form-control" id="password_confirmation" 
                                       name="password_confirmation" @if($user->must_change_password) required @endif>
                                <small class="form-text text-muted">Ulangi password baru</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                @if(!$user->must_change_password)
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                @endif
                <button type="submit" form="profileForm" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> 
                    @if($user->must_change_password)
                        Simpan & Lanjutkan
                    @else
                        Simpan Profile
                    @endif
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <style>
        .form-group label {
            font-weight: 600;
        }
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Check if user needs to complete profile
            @if(Auth::user()->must_change_password)
                // Show modal automatically for users who need to complete profile
                $('#profileModal').modal('show');
            @endif
        });

        // Prevent modal close and navigation until profile is completed
        $('#profileModal').on('hide.bs.modal', function (e) {
            @if(Auth::user()->must_change_password)
                e.preventDefault();
                return false;
            @endif
        });

        // Handle form submission
        $('#profileForm').on('submit', function(e) {
            e.preventDefault();
            
            let formData = new FormData(this);
            
            // Show loading state
            $('#submitBtn').prop('disabled', true).text('Menyimpan...');
            
            $.ajax({
                url: '{{ route("puskesmas.profile.update") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Redirect to home
                            window.location.href = response.redirect || '{{ route("home") }}';
                        });
                    }
                },
                error: function(xhr) {
                    $('#submitBtn').prop('disabled', false).text('Simpan Profile');
                    
                    if (xhr.status === 422) {
                        // Validation errors
                        let errors = xhr.responseJSON.messages;
                        let errorText = '';
                        
                        Object.keys(errors).forEach(function(key) {
                            errorText += errors[key][0] + '\n';
                        });
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: errorText
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON.error || 'Terjadi kesalahan'
                        });
                    }
                }
            });
        });

        // Prevent navigation away from page if profile is incomplete
        @if(Auth::user()->must_change_password)
            window.addEventListener('beforeunload', function (e) {
                e.preventDefault();
                e.returnValue = '';
            });
        @endif
    </script>
@stop