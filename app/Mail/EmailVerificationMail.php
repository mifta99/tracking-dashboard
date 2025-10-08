<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Verification code that will be rendered inside the email.
     *
     * @var string
     */
    public $verificationCode;

    /**
     * Verification link that the recipient can click.
     *
     * @var string
     */
    public $verificationUrl;

    /**
     * Expiration timestamp for the verification code.
     *
     * @var \Carbon\CarbonInterface|string|null
     */
    public $expiresAt;

    /**
     * Optional recipient name that will be displayed in the greeting.
     *
     * @var string|null
     */
    public $recipientName;

    /**
     * Application name to be shown in the email and subject line.
     *
     * @var string
     */
    public $appName;

    /**
     * Flag to indicate if this is for password reset.
     *
     * @var bool
     */
    public $isResetPassword;

    /**
     * Create a new message instance.
     *
     * @param  string  $verificationCode
     * @param  string  $verificationUrl
     * @param  \Carbon\CarbonInterface|string|null  $expiresAt
     * @param  string|null  $recipientName
     * @param  string|null  $appName
     * @return void
     */
    public function __construct(
        string $verificationCode,
        string $verificationUrl,
        ?string $recipientName = null,
        ?string $appName = null,
        ?bool $isResetPassword = false
    ) {
        $this->verificationCode = $verificationCode;
        $this->verificationUrl = $verificationUrl;
        $this->recipientName = $recipientName;
        $this->appName = 'T-Piece Dashboard' ?: config('app.name', 'Laravel');
        $this->isResetPassword = $isResetPassword;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject($this->appName . ($this->isResetPassword ? ' - Reset Password' : ' - Verifikasi Email'))
            ->view('layouts.emailverification')
            ->with([
                'verificationCode' => $this->verificationCode,
                'verificationUrl' => $this->verificationUrl,
                'recipientName' => $this->recipientName,
                'appName' => $this->appName,
                'isResetPassword' => $this->isResetPassword,
            ]);
    }
}
