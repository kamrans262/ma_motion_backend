<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Features\Auth\Exceptions\InvalidSocialIdentityException;
use App\Features\Auth\Exceptions\SocialProviderUnavailableException;
use App\Features\Auth\Services\OidcIdTokenVerifier;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class OidcIdTokenVerifierTest extends TestCase
{
    private const KID = 'fixture-key-1';

    // These are pre-signed, non-secret test fixtures. Runtime RSA key generation is
    // intentionally avoided because some Windows OpenSSL builds expose verification
    // but cannot generate keys without an external openssl.cnf configuration.
    private const GOOGLE_VALID_TOKEN = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImZpeHR1cmUta2V5LTEifQ.eyJpc3MiOiJodHRwczovL2FjY291bnRzLmdvb2dsZS5jb20iLCJhdWQiOiJjbGllbnQtMTIzIiwic3ViIjoic3ViamVjdC0xIiwiZXhwIjo0MTAyNDQ0ODAwLCJpYXQiOjE3MDAwMDAwMDAsImVtYWlsIjoiVVNFUkBFWEFNUExFLkNPTSIsImVtYWlsX3ZlcmlmaWVkIjp0cnVlLCJuYW1lIjoiUHJvdmlkZXIgVXNlciJ9.kboYYxLp3dHVkhGC1AP9VXjSaEpYl0EAgkZVOXHv2Q6I4HuSVyEtqUxUi1a-nKMrMpLpdX2RgVFBJuFCrMyn4v54dd46giKg2LQm9R20Ma6EA12yseW2QCmh7CZW9rJKNSg2LU3IBhjdAkgqKzDqbhfRUPpe9xhJ-YQ9SbW3WriJ7Vs6Nt9t0h-SKBVO279QVyYIXz7FDyWhK_38pePprZDVK2CabIZsjKv12Uq6V1UmDKuKDAlDyr_sp7i-P0wnOPjkAucUfo0Wsl7tGGLw2h7rmk91cv1zJcRIgpuP25EGWZ94hxjX9LDa8PYeO0sbK5wbrv0dNb9ZgkRL7Veb5Q';

    private const APPLE_WRONG_AUDIENCE_TOKEN = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImZpeHR1cmUta2V5LTEifQ.eyJpc3MiOiJodHRwczovL2FwcGxlaWQuYXBwbGUuY29tIiwiYXVkIjoid3JvbmctY2xpZW50Iiwic3ViIjoic3ViamVjdC0yIiwiZXhwIjo0MTAyNDQ0ODAwLCJpYXQiOjE3MDAwMDAwMDAsImVtYWlsIjoiYUBleGFtcGxlLmNvbSIsImVtYWlsX3ZlcmlmaWVkIjoidHJ1ZSJ9.PaythZRWhVYrLDDxdhtvuN-EBRmkZ9jWP2AQGKJgQCcl7fPIBuIj63jOlgnOzznJoHxW-wkLEen-4kWEPSK9DBfGwP6_2XvBP1frixacsMMY9tD4iqIdOr6eLJViTCwyK4nsiv060atYDFgL4_k0y3Qp4PV_OhAH7RAGAjN1R8ldIMS550MyaKwZvVb_ey6OJ5f1gghqLAGMb0djMR3jT4QuSxZcdBVyXcnc2u8NrHT0JgrX8GL9DpimpPPvGWZnN49EDqAnJHfVSY-YzIXDPD4FBqw-xX0A3jOQQ5geLzeQEojSMhIJx-2U5eT5V55in3gDyGUKAbRuOk9O7BqLrQ';

    /** @var array<string,string> */
    private const PUBLIC_JWK = [
        'kty' => 'RSA',
        'kid' => self::KID,
        'alg' => 'RS256',
        'use' => 'sig',
        'n' => 'p9dF_G8QuV3DpPVW2fXG02u4f1VNkOIQ2cDr9YeOyuFoigiNdzPlYb1UoMo4exzMD0I72HQscRnXPMcIQC0LQmjGL0LUeTyC8eGtJjeZZWdTLPLswyTDOg68jocjyae5xMRpVYVd9B0nFRW3be_R7Ap6OdRXS4s-ZUb2cLbS67PSk8tRbVoywoQtOSCpAHV2_-HkCygqF98FWt52gY_9hADPq5DyJs46NHy_bumod-vvxK7xvARCp6SraWQHydtd673N0PXLz1mZ71fkZjrNpjmp-RhVrOV0i2B8elWl5aM4EC9Cc0_-lViQB8tMK4Bu64Se0RlRHboskzXvh01RBQ',
        'e' => 'AQAB',
    ];

    protected function tearDown(): void
    {
        Cache::forget('ma-motion:social:jwks:google');
        Cache::forget('ma-motion:social:jwks:apple');
        parent::tearDown();
    }

    public function test_google_rs256_token_is_verified_against_cached_jwks_and_expected_claims(): void
    {
        config()->set('ma_motion.social.google', [
            'enabled' => true,
            'client_ids' => ['client-123'],
            'issuers' => ['https://accounts.google.com'],
            'jwks_url' => 'https://example.test/google-keys',
        ]);
        Cache::forget('ma-motion:social:jwks:google');
        Http::fake([
            'https://example.test/google-keys' => Http::response(['keys' => [self::PUBLIC_JWK]], 200),
        ]);

        $identity = app(OidcIdTokenVerifier::class)->verify('google', self::GOOGLE_VALID_TOKEN);

        $this->assertSame('subject-1', $identity->subject);
        $this->assertSame('user@example.com', $identity->email);
        $this->assertSame('Provider User', $identity->name);
        $this->assertTrue($identity->emailVerified);
        Http::assertSentCount(1);
    }

    public function test_wrong_audience_is_rejected_after_valid_rs256_signature(): void
    {
        config()->set('ma_motion.social.apple', [
            'enabled' => true,
            'client_ids' => ['expected-client'],
            'issuers' => ['https://appleid.apple.com'],
            'jwks_url' => 'https://example.test/apple-keys',
        ]);
        Cache::forget('ma-motion:social:jwks:apple');
        Http::fake([
            'https://example.test/apple-keys' => Http::response(['keys' => [self::PUBLIC_JWK]], 200),
        ]);

        $this->expectException(InvalidSocialIdentityException::class);
        app(OidcIdTokenVerifier::class)->verify('apple', self::APPLE_WRONG_AUDIENCE_TOKEN);
    }

    public function test_invalid_rs256_signature_is_rejected(): void
    {
        config()->set('ma_motion.social.google', [
            'enabled' => true,
            'client_ids' => ['client-123'],
            'issuers' => ['https://accounts.google.com'],
            'jwks_url' => 'https://example.test/google-keys',
        ]);
        Cache::forget('ma-motion:social:jwks:google');
        Http::fake([
            'https://example.test/google-keys' => Http::response(['keys' => [self::PUBLIC_JWK]], 200),
        ]);

        [$header, $payload, $signature] = explode('.', self::GOOGLE_VALID_TOKEN);
        $tamperedSignature = ($signature[0] === 'A' ? 'B' : 'A').substr($signature, 1);

        $this->expectException(InvalidSocialIdentityException::class);
        app(OidcIdTokenVerifier::class)->verify('google', $header.'.'.$payload.'.'.$tamperedSignature);
    }

    public function test_disabled_provider_fails_closed_without_network_call(): void
    {
        config()->set('ma_motion.social.google.enabled', false);
        Http::fake();

        try {
            app(OidcIdTokenVerifier::class)->verify('google', 'irrelevant');
            $this->fail('Disabled social provider should fail closed.');
        } catch (SocialProviderUnavailableException) {
            $this->assertTrue(true);
        }

        Http::assertNothingSent();
    }
}
