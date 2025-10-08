@extends('adminlte::page')

@section('title', 'Profile Puskesmas')

@section('content_header')
    <h1>Profile Puskesmas</h1>
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
                            <p class="text-muted">
                                @if($user->puskesmas)
                                    Puskesmas {{ $user->puskesmas->name }}
                                @else
                                    <span class="text-warning">Tidak terdaftar ke puskesmas</span>
                                @endif
                            </p>
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


                        @php
                            $isVerified = !is_null($user->email_verified_at);
                        @endphp
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i> Email
                                    @if($isVerified)
                                        <span id="emailVerificationBadge" class="badge badge-success ml-1">
                                            <i class="fas fa-check"></i> Terverifikasi
                                        </span>
                                    @else
                                        <span id="emailVerificationBadge" class="badge badge-warning ml-1">
                                            <i class="fas fa-exclamation-triangle"></i> Belum Terverifikasi
                                        </span>
                                    @endif
                                </label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="{{ $user->email }}" required>
                                <div id="emailValidationMessage" class="mt-1" style="display: none;">
                                    <small class="text-muted"><i class="fas fa-spinner fa-spin"></i> Mengecek ketersediaan email...</small>
                                </div>
                                
                                <!-- Email Update Request Section -->
                                <div id="emailUpdateSection" class="mt-2" style="display: none;">
                                    <div class="alert alert-info alert-sm">
                                        <i class="fas fa-info-circle"></i> Email baru telah divalidasi. Klik tombol di bawah untuk mengirim kode verifikasi dan langsung menyimpan perubahan profile.
                                    </div>
                                    <button type="button" class="btn btn-sm btn-warning" id="requestEmailUpdateBtn">
                                        <i class="fas fa-envelope"></i> Permintaan Update Email & Simpan Profile
                                    </button>
                                </div>
                                
                                <!-- OTP Verification for Email Update -->
                                <div id="emailUpdateOtpSection" class="mt-3" style="display: none;">
                                    <div class="alert alert-success alert-sm">
                                        <i class="fas fa-check-circle"></i> Kode OTP telah dikirim ke email baru Anda! Masukkan kode untuk langsung menyimpan seluruh profile.
                                    </div>
                                    <div class="input-group input-group-sm">
                                        <input type="text"
                                               class="form-control"
                                               id="emailUpdateOtpCode"
                                               maxlength="6"
                                               placeholder="Masukkan 6 digit kode OTP"
                                               inputmode="numeric"
                                               autocomplete="one-time-code">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success" id="verifyAndSaveBtn">
                                                <i class="fas fa-save"></i> Verifikasi & Simpan Semua
                                            </button>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        Verifikasi OTP akan langsung menyimpan seluruh perubahan profile Anda.
                                    </small>
                                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="resendEmailUpdateOtpBtn">
                                        <i class="fas fa-paper-plane"></i> Kirim Ulang OTP
                                    </button>
                                </div>
                                
                                @unless($isVerified)
                                    <div id="verificationActions" class="mt-2">
                                        <small class="form-text text-muted">
                                            Masukkan email dan pastikan tersedia sebelum mengirim kode verifikasi.
                                        </small>
                                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="sendVerificationBtn" disabled>
                                            <i class="fas fa-paper-plane"></i> <span id="sendBtnText">Validasi Email Dulu</span>
                                        </button>
                                        
                                        <div id="codeVerificationSection" class="mt-3" style="display: none;">
                                            <div class="input-group input-group-sm">
                                                <input type="text"
                                                       class="form-control"
                                                       id="verificationCode"
                                                       maxlength="6"
                                                       placeholder="Masukkan 6 digit kode verifikasi"
                                                       inputmode="numeric"
                                                       autocomplete="one-time-code">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-outline-success" id="verifyEmailBtn">
                                                        <i class="fas fa-check-circle"></i> Verifikasi
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">
                                                Masukkan kode 6 digit yang dikirim ke email Anda lalu tekan verifikasi.
                                            </small>
                                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="resendVerificationBtn">
                                                <i class="fas fa-paper-plane"></i> Kirim Ulang
                                            </button>
                                        </div>
                                    </div>
                                @endunless
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
        .alert-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            margin-bottom: 0.75rem;
        }
        #emailUpdateSection, #emailUpdateOtpSection {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 1rem;
            background-color: #f8f9fa;
        }
        #emailUpdateOtpSection {
            background-color: #e8f5e8;
            border-color: #c3e6cb;
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

            // Handle email validation
            let emailValidationTimeout;
            let originalEmail = '{{ $user->email }}';
            let isEmailValidated = false;
            let isEmailAvailable = false;
            
            // Initially disable verification button if email is not verified
            @unless($isVerified)
                $('#sendVerificationBtn').prop('disabled', true);
            @endunless
            
            $('#email').on('input', function() {
                const currentEmail = $(this).val().trim();
                const $validationMessage = $('#emailValidationMessage');
                
                // Clear previous timeout
                clearTimeout(emailValidationTimeout);
                
                // Reset validation state
                isEmailValidated = false;
                isEmailAvailable = false;
                $('#sendVerificationBtn').prop('disabled', true);
                $('#sendBtnText').text('Validasi Email Dulu');
                
                // Hide email update sections when email changes
                $('#emailUpdateSection').hide();
                $('#emailUpdateOtpSection').hide();
                
                // If email is empty or same as original, hide validation
                if (!currentEmail || currentEmail === originalEmail) {
                    $validationMessage.hide();
                    $('#emailUpdateSection').hide();
                    if (currentEmail === originalEmail) {
                        isEmailValidated = true;
                        isEmailAvailable = true;
                        $('#sendVerificationBtn').prop('disabled', false);
                        $('#sendBtnText').text('Kirim Kode Verifikasi');
                    }
                    return;
                }
                
                // Basic email format validation
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(currentEmail)) {
                    $validationMessage.show().html('<small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Format email tidak valid</small>');
                    isEmailValidated = true;
                    isEmailAvailable = false;
                    $('#sendVerificationBtn').prop('disabled', true);
                    $('#emailUpdateSection').hide();
                    return;
                }
                
                // Show checking message
                $validationMessage.show().html('<small class="text-muted"><i class="fas fa-spinner fa-spin"></i> Mengecek ketersediaan email...</small>');
                
                // Debounce API call
                emailValidationTimeout = setTimeout(function() {
                    $.ajax({
                        url: '{{ route("api.check-email") }}',
                        method: 'POST',
                        data: { 
                            email: currentEmail,
                            current_user_id: {{ Auth::id() }}
                        },
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function(response) {
                            isEmailValidated = true;
                            isEmailAvailable = response.available;
                            
                            if (response.available) {
                                $validationMessage.html('<small class="text-success"><i class="fas fa-check"></i> Email tersedia</small>');
                                $('#sendVerificationBtn').prop('disabled', false);
                                $('#sendBtnText').text('Kirim Kode Verifikasi');
                                
                                // Show email update request section for verified users
                                @if($isVerified)
                                    $('#emailUpdateSection').show();
                                @endif
                            } else {
                                $validationMessage.html('<small class="text-danger"><i class="fas fa-times"></i> Email sudah digunakan</small>');
                                $('#sendVerificationBtn').prop('disabled', true);
                                $('#sendBtnText').text('Email Tidak Tersedia');
                                $('#emailUpdateSection').hide();
                            }
                        },
                        error: function() {
                            isEmailValidated = false;
                            isEmailAvailable = false;
                            $validationMessage.html('<small class="text-muted"><i class="fas fa-question-circle"></i> Tidak dapat mengecek email</small>');
                            $('#sendVerificationBtn').prop('disabled', true);
                            $('#sendBtnText').text('Tidak Dapat Validasi');
                            $('#emailUpdateSection').hide();
                        }
                    });
                }, 800); // Wait 800ms after user stops typing
            });

            // Handle initial email verification send
            $(document).on('click', '#sendVerificationBtn', function() {
                var $btn = $(this);
                
                // Check if email is validated and available
                if (!isEmailValidated || !isEmailAvailable) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Email Belum Divalidasi',
                        text: 'Pastikan email telah divalidasi dan tersedia sebelum mengirim kode verifikasi.'
                    });
                    return;
                }
                
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengirim...');
                $.ajax({
                    url: '{{ route("verification.send") }}',
                    data: { email: $('#email').val() },
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terkirim',
                            text: 'Kode verifikasi telah dikirim ke email Anda.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        // Show verification code section
                        $('#codeVerificationSection').show();
                        $('#sendVerificationBtn').hide();
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Tidak dapat mengirim email verifikasi. Coba lagi nanti.'
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Kirim Kode Verifikasi');
                    }
                });
            });

            // Handle email verification resend button
            $(document).on('click', '#resendVerificationBtn', function() {
                var $btn = $(this);
                
                // Check if email is validated and available
                if (!isEmailValidated || !isEmailAvailable) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Email Belum Divalidasi',
                        text: 'Pastikan email telah divalidasi dan tersedia sebelum mengirim ulang kode verifikasi.'
                    });
                    return;
                }
                
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengirim...');
                $.ajax({
                    url: '{{ route("verification.send") }}',
                    method: 'POST',
                    data: { email: $('#email').val() },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terkirim',
                            text: 'Kode verifikasi telah dikirim ulang ke email Anda.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Tidak dapat mengirim email verifikasi. Coba lagi nanti.'
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Kirim Ulang');
                    }
                });
            });

            // Handle verification code submission
            $(document).on('click', '#verifyEmailBtn', function() {
                const $btn = $(this);
                const originalHtml = $btn.html();
                const code = $('#verificationCode').val().trim();

                if (!/^\d{6}$/.test(code)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Kode tidak valid',
                        text: 'Masukkan kode 6 digit yang dikirim ke email Anda.'
                    });
                    return;
                }

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memverifikasi...');
                $('#verificationCode').prop('disabled', true);

                $.ajax({
                    url: '{{ route("verification.verify") }}',
                    method: 'POST',
                    data: { code: code },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message || 'Email berhasil diverifikasi.',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        $('#emailVerificationBadge')
                            .removeClass('badge-warning')
                            .addClass('badge-success')
                            .html('<i class="fas fa-check"></i> Terverifikasi');

                        $('#verificationActions').remove();
                    },
                    error: function(xhr) {
                        let message = 'Kode verifikasi tidak valid. Silakan coba lagi.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: message
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                        $('#verificationCode').prop('disabled', false);
                    }
                });
            });

            // Handle email update request
            $(document).on('click', '#requestEmailUpdateBtn', function() {
                const $btn = $(this);
                const newEmail = $('#email').val().trim();
                
                if (!isEmailValidated || !isEmailAvailable) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Email Belum Divalidasi',
                        text: 'Pastikan email telah divalidasi dan tersedia sebelum meminta update.'
                    });
                    return;
                }
                
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengirim OTP...');
                
                $.ajax({
                    url: '{{ route("verification.send") }}',
                    method: 'POST',
                    data: { 
                        email: newEmail,
                        request_type: 'email_update'
                    },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'OTP Dikirim',
                            text: 'Kode OTP telah dikirim ke email baru Anda. Masukkan kode untuk langsung menyimpan seluruh profile.',
                            timer: 3000,
                            showConfirmButton: false
                        });
                        
                        // Hide email update section and show OTP section
                        $('#emailUpdateSection').hide();
                        $('#emailUpdateOtpSection').show();
                        $('#emailUpdateOtpCode').focus();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Mengirim OTP',
                            text: xhr.responseJSON?.message || 'Tidak dapat mengirim kode OTP. Coba lagi nanti.'
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-envelope"></i> Permintaan Update Email & Simpan Profile');
                    }
                });
            });

            // Handle OTP verification and profile save
            $(document).on('click', '#verifyAndSaveBtn', function() {
                const $btn = $(this);
                const originalHtml = $btn.html();
                const otpCode = $('#emailUpdateOtpCode').val().trim();

                if (!/^\d{6}$/.test(otpCode)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Kode OTP tidak valid',
                        text: 'Masukkan kode OTP 6 digit yang dikirim ke email baru Anda.'
                    });
                    return;
                }

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memverifikasi & Menyimpan...');
                $('#emailUpdateOtpCode').prop('disabled', true);

                // Get all form data
                let formData = new FormData($('#profileForm')[0]);
                formData.append('otp_code', otpCode);
                formData.append('verify_and_save', 'true');

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
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message || 'Profile dan email berhasil diperbarui!',
                                timer: 3000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = response.redirect || '{{ route("home") }}';
                            });
                        }
                    },
                    error: function(xhr) {
                        let message = 'Gagal memverifikasi OTP atau menyimpan profile.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: message
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                        $('#emailUpdateOtpCode').prop('disabled', false);
                    }
                });
            });

            // Handle resend OTP for email update
            $(document).on('click', '#resendEmailUpdateOtpBtn', function() {
                const $btn = $(this);
                const newEmail = $('#email').val().trim();
                
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengirim...');
                
                $.ajax({
                    url: '{{ route("verification.send") }}',
                    method: 'POST',
                    data: { 
                        email: newEmail,
                        request_type: 'email_update'
                    },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'OTP Dikirim Ulang',
                            text: 'Kode OTP baru telah dikirim ke email Anda.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: xhr.responseJSON?.message || 'Tidak dapat mengirim ulang kode OTP.'
                        });
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Kirim Ulang OTP');
                    }
                });
            });
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
                            timer: response.email_changed ? 3000 : 2000,
                            showConfirmButton: response.email_changed
                        }).then(() => {
                            if (response.email_changed) {
                                // Email changed - reload page to show new verification state
                                window.location.reload();
                            } else {
                                // No email change - redirect to home
                                window.location.href = response.redirect || '{{ route("home") }}';
                            }
                        });
                    }
                },
                error: function(xhr) {
                    $('#submitBtn').prop('disabled', false).text('Simpan Profile');
                    
                    if (xhr.status === 422) {
                        // Check if email verification is required
                        if (xhr.responseJSON && xhr.responseJSON.require_email_verification) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Verifikasi Email Diperlukan',
                                text: xhr.responseJSON.message || 'Anda harus memverifikasi email terlebih dahulu sebelum dapat melengkapi profile.',
                                confirmButtonText: 'OK, Saya Mengerti',
                                allowOutsideClick: false
                            }).then(() => {
                                // Focus on email verification section
                                $('#email').focus();
                                // Scroll to verification section if exists
                                if ($("#verificationActions").length) {
                                    $('html, body').animate({
                                        scrollTop: $("#verificationActions").offset().top - 100
                                    }, 500);
                                }
                            });
                        } else {
                            // Regular validation errors
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
                        }
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
