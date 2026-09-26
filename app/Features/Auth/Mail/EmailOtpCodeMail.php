<?php

namespace App\Features\Auth\Mail;

use Illuminate\Mail\Mailable;

final class EmailOtpCodeMail extends Mailable
{
    public function __construct(
        public readonly string $code,
        public readonly string $purpose,
    ) {}

    public function build(): static
    {
        return $this->subject('Your MA Verification Code')
            ->view('emails.auth-otp-code')
            ->with([
                'code' => $this->code,
                'minutes' => (int) config('auth_otp.expires_minutes', 10),
            ]);
    }
}
