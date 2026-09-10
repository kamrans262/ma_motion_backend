<?php

namespace App\Features\Auth\Services;

use App\Features\Auth\Contracts\SocialIdentityVerifier;
use App\Features\Auth\Data\SocialIdentity;
use App\Features\Auth\Exceptions\InvalidSocialIdentityException;
use App\Features\Auth\Exceptions\SocialProviderUnavailableException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

final class OidcIdTokenVerifier implements SocialIdentityVerifier
{
    public function __construct(private readonly RsaJwkToPem $jwkToPem) {}

    public function verify(string $provider, string $idToken): SocialIdentity
    {
        $settings = config('ma_motion.social.'.$provider);
        if (! is_array($settings) || ! ($settings['enabled'] ?? false)) {
            throw new SocialProviderUnavailableException('This social login provider is not enabled.');
        }

        $clientIds = array_values(array_filter((array) ($settings['client_ids'] ?? [])));
        $issuers = array_values(array_filter((array) ($settings['issuers'] ?? [])));
        $jwksUrl = (string) ($settings['jwks_url'] ?? '');
        if ($clientIds === [] || $issuers === [] || $jwksUrl === '') {
            throw new SocialProviderUnavailableException('This social login provider is not configured.');
        }

        [$header, $claims, $signingInput, $signature] = $this->decodeToken($idToken);
        if (($header['alg'] ?? null) !== 'RS256' || ! is_string($header['kid'] ?? null) || $header['kid'] === '') {
            throw new InvalidSocialIdentityException();
        }

        $jwk = $this->findKey($provider, $jwksUrl, $header['kid']);
        $pem = $this->jwkToPem->convert($jwk);
        if (openssl_verify($signingInput, $signature, $pem, OPENSSL_ALGO_SHA256) !== 1) {
            throw new InvalidSocialIdentityException();
        }

        $now = time();
        $skew = max(0, (int) config('ma_motion.social.clock_skew_seconds', 60));
        $issuer = (string) ($claims['iss'] ?? '');
        $subject = trim((string) ($claims['sub'] ?? ''));
        $expiresAt = filter_var($claims['exp'] ?? null, FILTER_VALIDATE_INT);
        $notBefore = isset($claims['nbf']) ? filter_var($claims['nbf'], FILTER_VALIDATE_INT) : null;

        $audClaim = $claims['aud'] ?? [];
        $audiences = is_array($audClaim) ? array_map('strval', $audClaim) : [(string) $audClaim];

        if (! in_array($issuer, $issuers, true)
            || $subject === ''
            || $expiresAt === false
            || $expiresAt < ($now - $skew)
            || ($notBefore !== null && $notBefore !== false && $notBefore > ($now + $skew))
            || array_intersect($clientIds, $audiences) === []) {
            throw new InvalidSocialIdentityException();
        }

        $email = isset($claims['email']) && is_string($claims['email'])
            ? strtolower(trim($claims['email']))
            : null;
        if ($email === '') {
            $email = null;
        }

        $emailVerifiedClaim = $claims['email_verified'] ?? false;
        $emailVerified = $emailVerifiedClaim === true || $emailVerifiedClaim === 1
            || in_array(strtolower((string) $emailVerifiedClaim), ['1', 'true'], true);

        $name = isset($claims['name']) && is_string($claims['name']) ? trim($claims['name']) : null;
        if ($name === '') {
            $name = null;
        }

        return new SocialIdentity($provider, $subject, $email, $name, $emailVerified);
    }

    /** @return array{0:array<string,mixed>,1:array<string,mixed>,2:string,3:string} */
    private function decodeToken(string $token): array
    {
        $parts = explode('.', trim($token));
        if (count($parts) !== 3) {
            throw new InvalidSocialIdentityException();
        }

        [$headerPart, $payloadPart, $signaturePart] = $parts;
        $headerJson = $this->decodeBase64Url($headerPart);
        $payloadJson = $this->decodeBase64Url($payloadPart);
        $signature = $this->decodeBase64Url($signaturePart);
        if ($headerJson === null || $payloadJson === null || $signature === null) {
            throw new InvalidSocialIdentityException();
        }

        try {
            $header = json_decode($headerJson, true, 32, JSON_THROW_ON_ERROR);
            $claims = json_decode($payloadJson, true, 64, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            throw new InvalidSocialIdentityException();
        }

        if (! is_array($header) || ! is_array($claims)) {
            throw new InvalidSocialIdentityException();
        }

        return [$header, $claims, $headerPart.'.'.$payloadPart, $signature];
    }

    /** @return array<string,mixed> */
    private function findKey(string $provider, string $url, string $kid): array
    {
        $cacheKey = 'ma-motion:social:jwks:'.$provider;
        $ttl = max(300, (int) config('ma_motion.social.jwks_cache_seconds', 21600));
        $keys = Cache::remember($cacheKey, $ttl, fn (): array => $this->fetchKeys($url));
        $match = $this->matchingKey($keys, $kid);

        if ($match !== null) {
            return $match;
        }

        Cache::forget($cacheKey);
        $keys = $this->fetchKeys($url);
        Cache::put($cacheKey, $keys, $ttl);
        $match = $this->matchingKey($keys, $kid);

        if ($match === null) {
            throw new InvalidSocialIdentityException();
        }

        return $match;
    }

    /** @return list<array<string,mixed>> */
    private function fetchKeys(string $url): array
    {
        try {
            $response = Http::acceptJson()->timeout(5)->get($url);
            if (! $response->successful()) {
                throw new SocialProviderUnavailableException();
            }
            $keys = $response->json('keys');
        } catch (SocialProviderUnavailableException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new SocialProviderUnavailableException();
        }

        if (! is_array($keys)) {
            throw new SocialProviderUnavailableException();
        }

        return array_values(array_filter($keys, static fn ($key): bool => is_array($key)));
    }

    /** @param list<array<string,mixed>> $keys @return array<string,mixed>|null */
    private function matchingKey(array $keys, string $kid): ?array
    {
        foreach ($keys as $key) {
            if (($key['kty'] ?? null) === 'RSA' && ($key['kid'] ?? null) === $kid) {
                return $key;
            }
        }

        return null;
    }

    private function decodeBase64Url(string $value): ?string
    {
        if ($value === '') {
            return null;
        }
        $padding = (4 - strlen($value) % 4) % 4;
        $decoded = base64_decode(strtr($value, '-_', '+/').str_repeat('=', $padding), true);

        return $decoded === false ? null : $decoded;
    }
}
