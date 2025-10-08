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
        $expiresAt = null,
        ?string $recipientName = null,
        ?string $appName = null
    ) {
        $this->verificationCode = $verificationCode;
        $this->verificationUrl = $verificationUrl;
        $this->expiresAt = $expiresAt;
        $this->recipientName = $recipientName;
        $this->appName = 'T-Piece Dashboard' ?: config('app.name', 'Laravel');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject($this->appName . ' - Verifikasi Email')
            ->view('layouts.emailverification')
            ->with([
                'verificationCode' => $this->verificationCode,
                'verificationUrl' => $this->verificationUrl,
                'expiresAt' => $this->expiresAt,
                'recipientName' => $this->recipientName,
                'appName' => $this->appName,
            ]);
    }
}
