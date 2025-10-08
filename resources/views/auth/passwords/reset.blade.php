<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Reset Password - Tracking Dashboard</title>

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    
    <!-- Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    
    <style>
        .recaptcha-container {
            display: flex;
            justify-content: center;
            margin: 15px 0;
        }
        
        .g-recaptcha {
            transform: scale(0.9);
            transform-origin: center;
        }
        
        @media (max-width: 576px) {
            .g-recaptcha {
                transform: scale(0.8);
            }
        }
        
        .login-box {
            width: 420px;
            max-width: 90%;
        }
        
        .alert {
            margin-bottom: 15px;
        }
        
        .login-box-msg {
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            color: #495057;
        }
        
        #otp_code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #dee2e6;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 5px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .step.active {
            background: #007bff;
            color: white;
        }
        
        .step.completed {
            background: #28a745;
            color: white;
        }
        
        .step-connector {
            width: 40px;
            height: 2px;
            background: #dee2e6;
            margin-top: 14px;
        }
        
        .step-connector.active {
            background: #007bff;
        }
        
        @media (max-width: 576px) {
            .login-box {
                width: 95%;
            }
            
            .step-indicator {
                transform: scale(0.8);
            }
        }
    </style>
</head>

<body class="login-page" style="min-height: 315.5px;">
    <div class="login-box">
        <!-- Logo -->
        <div class="login-logo">
            <a href="{{ url('/') }}">
                <img src="{{ asset('img/logo/logo.png') }}" alt="Auth Logo" width="120" height="120">
                <b>T-</b>Piece
            </a>
        </div>

        <!-- Reset Password Card -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title float-none text-center">
                    Reset Password
                </h3>
                
                <!-- Step Indicator -->
                <div class="step-indicator mt-3">
                    <div class="step active" id="step-indicator-1">1</div>
                    <div class="step-connector" id="connector-1"></div>
                    <div class="step" id="step-indicator-2">2</div>
                    <div class="step-connector" id="connector-2"></div>
                    <div class="step" id="step-indicator-3">3</div>
                </div>
            </div>

            <div class="card-body login-card-body">
                <!-- Display Session Status -->
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Display Validation Errors -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Step 1: Email Input -->
                <div id="step1-email" style="display: block;">
                    <p class="login-box-msg">Masukkan e-mail anda untuk mendapatkan kode verifikasi</p>
                    
                    <div class="input-group mb-3">
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email') }}" 
                               placeholder="Email Address" 
                               autofocus 
                               required>

                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>

                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Google reCAPTCHA -->
                    @if(config('app.env') !== 'local' || config('recaptcha.enabled', true))
                    <div class="recaptcha-container">
                        <div class="g-recaptcha" 
                             data-sitekey="{{ env('RECAPTCHA_SITE_KEY', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI') }}"
                             data-callback="onRecaptchaSuccess"
                             data-expired-callback="onRecaptchaExpired"></div>
                    </div>
                    @else
                    <!-- reCAPTCHA disabled in local development -->
                    <div class="alert alert-info" style="font-size: 12px;">
                        <i class="fas fa-info-circle"></i> reCAPTCHA dinonaktifkan dalam mode development
                    </div>
                    @endif

                    <button type="button" id="btnSendOTP" class="btn btn-block btn-flat btn-primary">
                        <span class="fas fa-share-square"></span>
                        Send OTP Code
                    </button>
                </div>

                <!-- Step 2: OTP Verification -->
                <div id="step2-otp" style="display: none;">
                    <p class="login-box-msg">
                        Enter the 6-digit OTP code sent to your email: 
                        <strong id="displayEmail"></strong>
                    </p>
                    
                    <div class="input-group mb-3">
                        <input type="text" 
                               id="otp_code" 
                               name="otp_code" 
                               class="form-control text-center" 
                               placeholder="Enter 6-digit OTP" 
                               maxlength="6" 
                               pattern="[0-9]{6}"
                               style="font-size: 18px; letter-spacing: 3px;"
                               required>

                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-key"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <button type="button" id="btnBack" class="btn btn-block btn-flat btn-secondary">
                                <span class="fas fa-arrow-left"></span>
                                Back
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" id="btnVerifyOTP" class="btn btn-block btn-flat btn-success">
                                <span class="fas fa-check"></span>
                                Verify OTP
                            </button>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <p class="text-muted small">
                            Tidak mendapatkan kode?
                            <a href="#" id="btnResendOTP" class="text-primary">Kirim Ulang OTP</a>
                        </p>
                        <div id="resendTimer" class="text-muted small" style="display: none;">
                            Kirim ulang tersedia dalam <span id="countdown">60</span> detik
                        </div>
                    </div>
                </div>

                <!-- Step 3: New Password -->
                <div id="step3-password" style="display: none;">
                    <p class="login-box-msg">Set your new password</p>
                    
                    <div class="input-group mb-3">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="New Password" 
                               minlength="8"
                               required>

                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>

                    <div class="input-group mb-3">
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               class="form-control" 
                               placeholder="Confirm New Password" 
                               minlength="8"
                               required>

                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <button type="button" id="btnBackToOTP" class="btn btn-block btn-flat btn-secondary">
                                <span class="fas fa-arrow-left"></span>
                                Back
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" id="btnResetPassword" class="btn btn-block btn-flat btn-primary">
                                <span class="fas fa-save"></span>
                                Reset Password
                            </button>
                        </div>
                    </div>
                </div>


                <!-- Back to Login Link -->
                <p class="mt-3 mb-1">
                    <a href="{{ route('login') }}">Back to Login</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
    <script>
        let currentEmail = '';
        let resendCountdown = 0;
        let countdownTimer = null;

        $(document).ready(function() {
            // Step 1: Send OTP
            $("#btnSendOTP").click(function() {
                sendOTPCode();
            });

            // Step 2: Verify OTP
            $("#btnVerifyOTP").click(function() {
                verifyOTPCode();
            });

            // Step 3: Reset Password
            $("#btnResetPassword").click(function() {
                resetPassword();
            });

            // Navigation buttons
            $("#btnBack").click(function() {
                showStep(1);
            });

            $("#btnBackToOTP").click(function() {
                showStep(2);
            });

            // Resend OTP
            $("#btnResendOTP").click(function(e) {
                e.preventDefault();
                if (resendCountdown <= 0) {
                    sendOTPCode(true);
                }
            });

            // Handle Enter key press for each step
            $('#email').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#btnSendOTP').click();
                }
            });

            $('#otp_code').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#btnVerifyOTP').click();
                }
            });

            $('#password, #password_confirmation').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#btnResetPassword').click();
                }
            });

            // OTP input formatting
            $('#otp_code').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length === 6) {
                    $('#btnVerifyOTP').focus();
                }
            });

            // Password validation
            $('#password_confirmation').on('input', function() {
                const $field = $(this);
                const password = $('#password').val();
                const confirmation = $field.val();

                if (confirmation && password !== confirmation) {
                    $field.addClass('is-invalid');
                    if (!$field.siblings('.invalid-feedback.js-password-mismatch').length) {
                        $field.after('<div class="invalid-feedback js-password-mismatch" style="display: block; position: absolute; font-size: 12px; margin-top: 2px;">Password tidak cocok</div>');
                    }
                } else {
                    $field.removeClass('is-invalid');
                    $field.siblings('.invalid-feedback.js-password-mismatch').remove();
                }
            });
        });

        function sendOTPCode(isResend = false) {
            const email = $('#email').val();
            if (!email || !isValidEmail(email)) {
                showAlert('danger', 'Silakan masukkan alamat email yang valid.');
                return;
            }

            // Validate reCAPTCHA (skip in development and resend)
            let captchaResponse = '';
            if (!isResend) {
                @if(config('app.env') !== 'local' || config('recaptcha.enabled', true))
                captchaResponse = grecaptcha.getResponse();
                if (!captchaResponse) {
                    showAlert('danger', 'Silakan verifikasi bahwa Anda bukan robot.');
                    return;
                }
                @else
                captchaResponse = 'development-bypass';
                @endif
            } else {
                captchaResponse = 'resend-bypass';
            }

            const $btn = isResend ? $("#btnResendOTP") : $("#btnSendOTP");
            const originalText = $btn.html();
            
            $btn.prop("disabled", true);
            $btn.html(`<span class="spinner-border spinner-border-sm" role="status"></span> ${isResend ? 'Resending...' : 'Sending...'}`);
            
            $.ajax({
                url: "{{ route('api.send-reset-password-email') }}",
                type: "POST",
                dataType: "json",
                data: {
                    email: email,
                    'g-recaptcha-response': captchaResponse,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {
                    console.log("OTP sent:", data);
                    currentEmail = email;
                    $('#displayEmail').text(email);

                    showAlert('success', isResend ? 'Kode OTP telah dikirim ulang ke email Anda.' : 'Kode OTP telah dikirim ke email Anda.');
                    showStep(2);
                    
                    // Start resend countdown
                    startResendCountdown();
                    
                    // Reset reCAPTCHA
                    @if(config('app.env') !== 'local' || config('recaptcha.enabled', true))
                    if (typeof grecaptcha !== 'undefined') {
                        grecaptcha.reset();
                    }
                    @endif
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", error);
                    let errorMessage = "Gagal mengirimkan kode OTP!";
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showAlert('danger', errorMessage);
                    
                    // Reset reCAPTCHA
                    @if(config('app.env') !== 'local' || config('recaptcha.enabled', true))
                    if (typeof grecaptcha !== 'undefined') {
                        grecaptcha.reset();
                    }
                    @endif
                },
                complete: function () {
                    $btn.prop("disabled", false).html(originalText);
                }
            });
        }

        function verifyOTPCode() {
            const otpCode = $('#otp_code').val();
            if (!otpCode || otpCode.length !== 6) {
                showAlert('danger', 'Silakan masukkan kode OTP 6 digit yang valid.');
                return;
            }

            const $btn = $("#btnVerifyOTP");
            $btn.prop("disabled", true);
            $btn.html(`<span class="spinner-border spinner-border-sm" role="status"></span> Verifying...`);
            
            $.ajax({
                url: "{{ route('api.verify-otp') }}",
                type: "POST",
                dataType: "json",
                data: {
                    email: currentEmail,
                    otp_code: otpCode,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {
                    console.log("OTP verified:", data);
                    showAlert('success', 'Verifikasi OTP berhasil. Silakan atur password baru Anda.');
                    showStep(3);
                },
                error: function (xhr, status, error) {
                    console.error("OTP Error:", error);
                    let errorMessage = "Kode OTP tidak valid atau telah kedaluwarsa.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showAlert('danger', errorMessage);
                    $('#otp_code').addClass('is-invalid').focus();
                },
                complete: function () {
                    $btn.prop("disabled", false).html(`<span class="fas fa-check"></span> Verify OTP`);
                }
            });
        }

        function resetPassword() {
            const password = $('#password').val();
            const passwordConfirmation = $('#password_confirmation').val();
            
            if (!password || password.length < 8) {
                showAlert('danger', 'Password minimal 8 karakter.');
                return;
            }
            
            if (password !== passwordConfirmation) {
                showAlert('danger', 'Konfirmasi password tidak cocok.');
                return;
            }

            const $btn = $("#btnResetPassword");
            $btn.prop("disabled", true);
            $btn.html(`<span class="spinner-border spinner-border-sm" role="status"></span> Proses`);
            
            $.ajax({
                url: "{{ route('api.reset-password') }}",
                type: "POST",
                dataType: "json",
                data: {
                    email: currentEmail,
                    password: password,
                    password_confirmation: passwordConfirmation,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {
                    console.log("Password reset successful:", data);
                    showAlert('success', 'Password berhasil direset. Anda akan diarahkan ke halaman login.');
                    updateStepIndicator(3);
                    $('#step-indicator-3').removeClass('active').addClass('completed');

                    // Show success state on button
                    $btn.removeClass('btn-primary').addClass('btn-success').prop('disabled', true);
                    $btn.html(`<span class="fas fa-check"></span> Berhasil!`);
                    setTimeout(function() {
                        window.location.href = "{{ route('login') }}";
                    }, 2000);
                },
                error: function (xhr, status, error) {
                    console.error("Reset Error:", error);
                    let errorMessage = "Gagal mereset password.";
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showAlert('danger', errorMessage);
                    $btn.prop("disabled", false).html(`<span class="fas fa-save"></span> Reset Password`);
                }
            });
        }

        function showStep(step) {
            // Hide all steps
            $('#step1-email, #step2-otp, #step3-password').hide();
            
            // Show target step
            $(`#step${step}-${step === 1 ? 'email' : step === 2 ? 'otp' : 'password'}`).show();
            
            // Update step indicator
            updateStepIndicator(step);
            
            // Clear alerts
            $('.alert').remove();
            
            // Focus on appropriate input
            setTimeout(() => {
                if (step === 1) {
                    $('#email').focus();
                } else if (step === 2) {
                    $('#otp_code').val('').removeClass('is-invalid').focus();
                } else if (step === 3) {
                    $('#password').focus();
                }
            }, 100);
        }

        function updateStepIndicator(currentStep) {
            // Reset all steps
            $('.step').removeClass('active completed');
            $('.step-connector').removeClass('active');
            
            // Mark completed steps
            for (let i = 1; i < currentStep; i++) {
                $(`#step-indicator-${i}`).addClass('completed');
                $(`#connector-${i}`).addClass('active');
            }
            
            // Mark current step as active
            $(`#step-indicator-${currentStep}`).addClass('active');
        }

        function startResendCountdown() {
            resendCountdown = 60;
            $('#btnResendOTP').hide();
            $('#resendTimer').show();
            
            countdownTimer = setInterval(function() {
                resendCountdown--;
                $('#countdown').text(resendCountdown);
                
                if (resendCountdown <= 0) {
                    clearInterval(countdownTimer);
                    $('#resendTimer').hide();
                    $('#btnResendOTP').show();
                }
            }, 1000);
        }

        // Helper functions
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        @if(config('app.env') !== 'local' || config('recaptcha.enabled', true))
        // reCAPTCHA callback functions
        function onRecaptchaSuccess(token) {
            console.log('reCAPTCHA verification successful');
            $('.alert-danger').fadeOut(); // Hide any previous captcha error
        }

        function onRecaptchaExpired() {
            console.log('reCAPTCHA expired');
            showAlert('warning', 'Verifikasi reCAPTCHA telah kedaluwarsa. Silakan verifikasi ulang.');
        }
        @endif

        function showAlert(type, message) {
            $('.alert').remove();
            $('.card-body').prepend(`
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `);
            
            // Auto hide success alerts after 5 seconds
            if (type === 'success') {
                setTimeout(() => {
                    $('.alert-success').fadeOut();
                }, 5000);
            }
        }
    </script>
</body>
</html>
