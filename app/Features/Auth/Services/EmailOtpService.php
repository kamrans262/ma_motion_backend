<?php

namespace App\Features\Auth\Services;

use App\Features\Auth\Mail\EmailOtpCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class EmailOtpService
{
    public function request(string $email, string $purpose, ?User $actor = null): string
    {
        $email = Str::lower(trim($email));

        if ($purpose === 'confirm' && (! $actor || $actor->email !== $email)) {
            throw ValidationException::withMessages(['email' => 'Please use the email address for your current account.']);
        }

        // Login is available only to existing active accounts. Reject unknown
        // emails before creating a challenge, starting a cooldown, or sending mail.
        $user = User::query()->where('email', $email)->first();
        if ($purpose === 'login' && $user === null) {
            throw ValidationException::withMessages([
                'email' => 'This email is not registered. Please create an account to continue.',
            ]);
        }

        if ($purpose === 'login' && ! $user->isActive()) {
            throw ValidationException::withMessages([
                'email' => 'This account is not active. Please contact support.',
            ]);
        }

        $cooldownKey = 'auth:otp:cooldown:'.hash('sha256', $email.'|'.$purpose);
        if (! Cache::add($cooldownKey, true, (int) config('auth_otp.resend_seconds', 60))) {
            throw ValidationException::withMessages(['email' => 'Please wait before requesting another code.']);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $id = (string) Str::uuid();

        DB::table('email_otp_challenges')->insert([
            'id' => $id,
            'email' => $email,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes((int) config('auth_otp.expires_minutes', 10)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $shouldSend = match ($purpose) {
            'login' => $user !== null && $user->isActive(),
            'register' => $user === null,
            'confirm' => $actor !== null && $actor->isActive(),
            default => false,
        };

        if ($shouldSend) {
            try {
                Mail::to($email)->send(new EmailOtpCodeMail($code, $purpose));
            } catch (\Throwable $exception) {
                DB::table('email_otp_challenges')->where('id', $id)->delete();
                Cache::forget($cooldownKey);
                report($exception);
                abort(503, 'Email could not be sent. Please try again later.');
            }
        }

        return $id;
    }

    /** @return array{purpose:string,user?:User,token?:string} */
    public function verify(string $id, string $email, string $code, ?User $actor = null): array
    {
        return DB::transaction(function () use ($id, $email, $code, $actor): array {
            $challenge = DB::table('email_otp_challenges')->where('id', $id)->lockForUpdate()->first();
            if (! $challenge || $challenge->email !== Str::lower(trim($email))
                || $challenge->consumed_at !== null || $challenge->verified_at !== null
                || now()->greaterThan($challenge->expires_at)
                || $challenge->attempts >= (int) config('auth_otp.maximum_attempts', 5)) {
                throw ValidationException::withMessages(['code' => 'This code is invalid or expired. Please request a new code.']);
            }

            // Do not throw inside this transaction for a wrong code: attempts
            // must persist, including when the fifth attempt is incorrect.
            if (! Hash::check($code, $challenge->code_hash)) {
                DB::table('email_otp_challenges')->where('id', $id)->increment('attempts');
                return ['purpose' => 'invalid'];
            }

            if ($challenge->purpose === 'confirm') {
                if (! $actor || ! $actor->isActive() || $actor->email !== $challenge->email) {
                    throw ValidationException::withMessages(['code' => 'This verification request is no longer valid.']);
                }
                $actor->forceFill(['email_verified_at' => now()])->save();
            }

            DB::table('email_otp_challenges')->where('id', $id)->update([
                'verified_at' => now(),
                'consumed_at' => $challenge->purpose === 'login' || $challenge->purpose === 'confirm' ? now() : null,
                'updated_at' => now(),
            ]);

            if ($challenge->purpose !== 'login') {
                return ['purpose' => $challenge->purpose];
            }

            $user = User::query()->where('email', $challenge->email)->first();
            if (! $user || ! $user->isActive()) {
                throw ValidationException::withMessages(['code' => 'This verification request is no longer valid.']);
            }

            $user->forceFill(['email_verified_at' => now(), 'last_login_at' => now()])->save();
            return [
                'purpose' => 'login',
                'user' => $user->refresh(),
                'token' => $user->createToken('MA Motion mobile', ['mobile'])->plainTextToken,
            ];
        });
    }

    public function consumeRegistrationProof(?string $id, string $email): void
    {
        if (! $id) {
            throw ValidationException::withMessages(['otp_challenge_id' => 'Verify your email before creating an account.']);
        }

        $challenge = DB::table('email_otp_challenges')->where('id', $id)->lockForUpdate()->first();
        if (! $challenge || $challenge->purpose !== 'register'
            || $challenge->email !== Str::lower(trim($email))
            || $challenge->verified_at === null || $challenge->consumed_at !== null
            || now()->greaterThan($challenge->expires_at)) {
            throw ValidationException::withMessages(['otp_challenge_id' => 'Email verification expired. Please request a new code.']);
        }

        DB::table('email_otp_challenges')->where('id', $id)->update(['consumed_at' => now(), 'updated_at' => now()]);
    }
}
