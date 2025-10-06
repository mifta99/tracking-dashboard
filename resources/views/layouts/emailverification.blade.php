<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appName ?? config('app.name') }} - Verifikasi Email</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            font-family: 'Poppins', Arial, sans-serif;
            color: #1f2937;
        }
        a {
            color: inherit;
        }
        .wrapper {
            width: 100%;
            background-color: #f5f7fa;
            padding: 24px 0;
        }
        .card {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 12px 35px rgba(15, 23, 42, 0.12);
        }
        .header {
            background: linear-gradient(135deg, #ebf3ff, #dbeafe);
            padding: 28px 36px;
        }
        .brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 600;
            width: 100%;
        }
        
        /* Email client compatibility for logo positioning */
        .brand-left {
            flex: 1;
            display: flex;
            align-items: center;
        }
        
        .brand-right {
            flex-shrink: 0;
            margin-left: auto;
            text-align: right;
        }
        .brand__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background-color: #e0edff;
            color: #1d4ed8;
            font-size: 20px;
            font-weight: 700;
        }
        .brand__name {
            margin-left: 14px;
            font-size: 18px;
            letter-spacing: 0.02em;
            color: #1d4ed8;
        }
        .subheading {
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #2563eb;
        }
        .content {
            padding: 32px 36px;
        }
        .title {
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 16px;
            color: #111827;
        }
        .paragraph {
            font-size: 14px;
            line-height: 1.7;
            margin: 0 0 20px;
            color: #4b5563;
        }
        .info-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            border-radius: 12px;
            padding: 18px 20px;
            margin: 0 0 28px;
            font-size: 13px;
            line-height: 1.6;
            color: #1e40af;
        }
        .code-wrapper {
            text-align: center;
            margin: 36px 0 28px;
        }
        .code-label {
            font-size: 13px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 12px;
        }
        .verification-code {
            display: inline-block;
            padding: 14px 28px;
            border-radius: 12px;
            background: linear-gradient(135deg, #ebf2ff, #e0f2fe);
            color: #1d4ed8 !important;
            font-size: 24px;
            letter-spacing: 0.7rem;
            font-weight: 600;
            border: 2px solid #dbeafe;
            text-shadow: none;
        }
        .expiry {
            margin-top: 14px;
            font-size: 12px;
            color: #6b7280;
        }
        .button {
            display: inline-block;
            padding: 14px 34px;
            background-color: #2563eb !important;
            color: #ffffff !important;
            font-weight: 600;
            text-decoration: none !important;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.25);
            letter-spacing: 0.04em;
            border: 2px solid #2563eb;
            transition: all 0.3s ease;
        }
        .button:hover {
            background-color: #1d4ed8 !important;
            border-color: #1d4ed8;
            color: #ffffff !important;
        }
        .button:visited {
            color: #ffffff !important;
        }
        .button:active {
            color: #ffffff !important;
            background-color: #1e40af !important;
        }
        
        /* Email client fallbacks */
        a[class="button"] {
            background-color: #2563eb !important;
            color: #ffffff !important;
            text-decoration: none !important;
        }
        
        /* Outlook specific fixes */
        [owa] .button {
            background-color: #2563eb !important;
            color: #ffffff !important;
        }
        
        /* Dark mode compatibility */
        @media (prefers-color-scheme: dark) {
            .button {
                background-color: #3b82f6 !important;
                color: #ffffff !important;
                border-color: #3b82f6;
                box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
            }
            .button:hover {
                background-color: #2563eb !important;
                border-color: #2563eb;
                color: #ffffff !important;
            }
            body {
                background-color: #1f2937 !important;
                color: #f9fafb !important;
            }
            .card {
                background-color: #374151 !important;
            }
            .header {
                background: linear-gradient(135deg, #1e3a8a, #1d4ed8) !important;
            }
            .content {
                background-color: #374151 !important;
            }
            .title {
                color: #f9fafb !important;
            }
            .paragraph {
                color: #d1d5db !important;
            }
            .signature {
                color: #f9fafb !important;
            }
            .verification-code {
                background: linear-gradient(135deg, #1e40af, #2563eb) !important;
                color: #ffffff !important;
                border-color: #3b82f6 !important;
                text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            }
            .code-label {
                color: #d1d5db !important;
            }
            .expiry {
                color: #9ca3af !important;
            }
            .info-box {
                background-color: #1e40af !important;
                border-left-color: #60a5fa !important;
                color: #e0f2fe !important;
            }
            .link-box {
                background-color: #4b5563 !important;
                color: #60a5fa !important;
            }
        }
        .link-box {
            background-color: #f3f4f6;
            border-radius: 10px;
            padding: 14px 18px;
            font-size: 13px;
            word-break: break-word;
            font-family: "Courier New", Courier, monospace;
            color: #2563eb;
            margin: 0 0 28px;
        }
        .signature {
            margin-top: 32px;
            font-size: 14px;
            line-height: 1.6;
            color: #1f2937;
        }
        .footer {
            padding: 26px 36px 32px;
            font-size: 11px;
            color: #9ca3af;
            text-align: center;
        }
        @media (max-width: 640px) {
            .card {
                border-radius: 0;
            }
            .header,
            .content,
            .footer {
                padding-left: 24px !important;
                padding-right: 24px !important;
            }
            .verification-code {
                letter-spacing: 0.5rem;
            }
            .button {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
@php
    $appName = $appName ?? config('app.name');
    $displayName = $recipientName ?? 'Bapak/Ibu';
    $rawCode = (string) ($verificationCode ?? '');
    $digitsOnly = preg_replace('/[^0-9]/', '', $rawCode);
    $codeToShow = $digitsOnly !== '' ? $digitsOnly : ($rawCode !== '' ? $rawCode : '------');
    $formattedCode = trim(implode(' ', preg_split('//u', $codeToShow, -1, PREG_SPLIT_NO_EMPTY)));
    $verificationUrl = $verificationUrl ?? '#';
@endphp
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <table width="100%" cellpadding="0" cellspacing="0" style="margin:0; padding:0;">
                    <tr>
                        <td style="vertical-align:middle; text-align:left;">
                            <span class="brand__name">{{ $appName ?? 'T-Piece Dashboard' }}</span>
                        </td>
                        <td style="vertical-align:middle; text-align:right; width:88px;">
                            <div style="margin:0; padding:0; width:88px; height:88px; border-radius:12px; overflow:hidden; line-height:0; font-size:0; display:inline-block;">
                                <img src="https://i.imghippo.com/files/kud1899sk.png"
                                     alt="{{ $appName ?? 'Logo' }}"
                                     width="88" height="88"
                                     style="display:block; width:88px; height:88px; border:0; outline:none; text-decoration:none; -ms-interpolation-mode:bicubic; object-fit:cover; object-position:center; border-radius:12px;">
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="content">
                <h1 class="title">Verifikasi Alamat Email Anda</h1>
                <p class="paragraph">Kepada Yth. {{ $displayName }},</p>
                <p class="paragraph">Terima kasih telah melakukan registrasi pada platform <strong>{{ $appName ?? 'MediDistribusi' }}</strong>. Untuk menjaga keamanan akun Anda, kami perlu memastikan bahwa alamat email ini benar-benar milik Anda.</p>
                <div class="info-box">
                    Mohon selesaikan proses verifikasi dalam waktu 30 menit. Setelah lewat batas waktu, Anda harus meminta kode verifikasi baru untuk keamanan akun Anda.
                </div>
                <div class="code-wrapper">
                    <div class="code-label">Kode verifikasi Anda</div>
                    <div class="verification-code">{{ $formattedCode }}</div>
                    @if(!empty($expiryDisplay))
                        <div class="expiry">Kode berlaku hingga: {{ $expiryDisplay }}</div>
                    @endif
                </div>
                <div style="text-align:center; margin-bottom: 28px;">
                    <a href="{{ $verificationUrl }}" 
                       class="button" 
                       target="_blank" 
                       rel="noopener"
                       style="display: inline-block; padding: 14px 34px; background-color: #2563eb !important; color: #ffffff !important; font-weight: 600; text-decoration: none !important; border-radius: 12px; box-shadow: 0 10px 20px rgba(37, 99, 235, 0.25); letter-spacing: 0.04em; border: 2px solid #2563eb; font-family: 'Poppins', Arial, sans-serif; font-size: 14px;">
                       Verifikasi Sekarang
                    </a>
                </div>
                <p class="paragraph">Jika tombol di atas tidak berfungsi, salin dan tempel tautan berikut pada peramban Anda:</p>
                <div class="link-box">{{ $verificationUrl }}</div>
                <p class="paragraph">Apabila Anda tidak merasa melakukan pendaftaran, abaikan email ini. Anda juga dapat menghubungi tim dukungan kami agar akun tetap aman.</p>
                <div class="signature">
                    Hormat kami,<br>
                    <strong>Tim {{ $appName ?? 'MediDistribusi' }}</strong>
                </div>
            </div>
            <div class="footer">
                Email ini dikirim secara otomatis, mohon tidak membalas pesan ini.<br>
                &copy; {{ now()->format('Y') }} {{ $appName ?? 'MediDistribusi' }}. Seluruh hak cipta dilindungi.
            </div>
        </div>
    </div>
</body>
</html>