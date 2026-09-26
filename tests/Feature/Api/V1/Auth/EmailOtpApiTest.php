<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Enums\UserStatus;
use App\Features\Auth\Mail\EmailOtpCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailOtpApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_otp_mail_uses_ma_branding(): void
    {
        $mail = new EmailOtpCodeMail('511615', 'login');
        $mail->build();

        $this->assertSame('Your MA Verification Code', $mail->subject);

        $html = $mail->render();
        $this->assertStringContainsString('>MA</td>', $html);
        $this->assertStringContainsString('Enter this one-time code in MA to continue.', $html);
        $this->assertStringNotContainsString('MA Motion', $html);
    }

    public function test_registration_requires_verified_email_and_issues_session_only_after_registration(): void
    {
        Mail::fake();
        config(['auth_otp.require_onboarding_verification' => true]);

        $this->postJson('/api/v1/auth/maker-onboarding', [
            'name' => 'New Maker', 'email' => 'maker@example.com',
        ])->assertUnprocessable()->assertJsonValidationErrors('otp_challenge_id');

        $request = $this->postJson('/api/v1/auth/email-otp/request', [
            'email' => 'Maker@Example.com', 'purpose' => 'register',
        ])->assertOk();

        $id = $request->json('data.challenge_id');
        $code = '';
        Mail::assertSent(EmailOtpCodeMail::class, function (EmailOtpCodeMail $mail) use (&$code): bool {
            $code = $mail->code;
            return true;
        });
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $code);
        $this->assertDatabaseCount('users', 0);

        $this->postJson('/api/v1/auth/email-otp/verify', [
            'challenge_id' => $id, 'email' => 'maker@example.com', 'code' => '999999' === $code ? '888888' : '999999',
        ])->assertUnprocessable()->assertJsonValidationErrors('code');

        $this->postJson('/api/v1/auth/email-otp/verify', [
            'challenge_id' => $id, 'email' => 'maker@example.com', 'code' => $code,
        ])->assertOk()->assertJsonPath('data.verified', true);

        $this->assertDatabaseCount('users', 0);

        $registration = $this->postJson('/api/v1/auth/maker-onboarding', [
            'name' => 'New Maker', 'email' => 'maker@example.com', 'otp_challenge_id' => $id,
        ])->assertCreated()->assertJsonStructure(['data' => ['token']]);

        $this->assertNotNull($registration->json('data.user.email_verified_at'));
        $this->assertNotNull(\Illuminate\Support\Facades\DB::table('email_otp_challenges')->where('id', $id)->value('consumed_at'));
    }

    public function test_existing_user_logs_in_with_email_code_without_password(): void
    {
        Mail::fake();
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'role' => UserRole::Maker,
            'status' => UserStatus::Active,
            'password' => null,
        ]);
        $user->makerProfile()->create();

        $request = $this->postJson('/api/v1/auth/email-otp/request', [
            'email' => 'existing@example.com', 'purpose' => 'login',
        ])->assertOk();

        $code = '';
        Mail::assertSent(EmailOtpCodeMail::class, function (EmailOtpCodeMail $mail) use (&$code): bool {
            $code = $mail->code;
            return true;
        });

        $id = $request->json('data.challenge_id');
        $verified = $this->postJson('/api/v1/auth/email-otp/verify', [
            'challenge_id' => $id, 'email' => 'existing@example.com', 'code' => $code,
        ])->assertOk()->assertJsonPath('message', 'Email verified successfully.');

        $this->withToken($verified->json('data.token'))
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'existing@example.com');

        $this->postJson('/api/v1/auth/email-otp/verify', [
            'challenge_id' => $id, 'email' => 'existing@example.com', 'code' => $code,
        ])->assertUnprocessable()->assertJsonValidationErrors('code');
    }

    public function test_inactive_account_cannot_request_login_otp(): void
    {
        Mail::fake();
        User::factory()->create([
            'email' => 'inactive@example.com',
            'status' => UserStatus::Inactive,
        ]);

        $this->postJson('/api/v1/auth/email-otp/request', [
            'email' => 'inactive@example.com', 'purpose' => 'login',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('email')
            ->assertJsonPath('errors.email.0', 'This account is not active. Please contact support.');

        Mail::assertNothingSent();
        $this->assertDatabaseCount('email_otp_challenges', 0);
    }

    public function test_unknown_login_does_not_send_email_or_create_an_account(): void
    {
        Mail::fake();
        $this->postJson('/api/v1/auth/email-otp/request', [
            'email' => 'unknown@example.com', 'purpose' => 'login',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('email')
            ->assertJsonPath('errors.email.0', 'This email is not registered. Please create an account to continue.');
        Mail::assertNothingSent();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('email_otp_challenges', 0);
    }
}
