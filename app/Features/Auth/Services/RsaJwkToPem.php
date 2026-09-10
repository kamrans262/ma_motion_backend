<?php

namespace App\Features\Auth\Services;

use App\Features\Auth\Exceptions\InvalidSocialIdentityException;

final class RsaJwkToPem
{
    /** @param array<string, mixed> $jwk */
    public function convert(array $jwk): string
    {
        $modulus = $this->decodeBase64Url((string) ($jwk['n'] ?? ''));
        $exponent = $this->decodeBase64Url((string) ($jwk['e'] ?? ''));

        if ($modulus === null || $exponent === null || $modulus === '' || $exponent === '') {
            throw new InvalidSocialIdentityException();
        }

        $rsaKey = $this->sequence($this->integer($modulus).$this->integer($exponent));
        $algorithmIdentifier = hex2bin('300d06092a864886f70d0101010500');
        if ($algorithmIdentifier === false) {
            throw new InvalidSocialIdentityException();
        }

        $subjectPublicKeyInfo = $this->sequence(
            $algorithmIdentifier.$this->bitString($rsaKey),
        );

        return "-----BEGIN PUBLIC KEY-----\n"
            .chunk_split(base64_encode($subjectPublicKeyInfo), 64, "\n")
            ."-----END PUBLIC KEY-----\n";
    }

    private function integer(string $bytes): string
    {
        $bytes = ltrim($bytes, "\x00");
        if ($bytes === '') {
            $bytes = "\x00";
        }
        if ((ord($bytes[0]) & 0x80) !== 0) {
            $bytes = "\x00".$bytes;
        }

        return "\x02".$this->length(strlen($bytes)).$bytes;
    }

    private function sequence(string $bytes): string
    {
        return "\x30".$this->length(strlen($bytes)).$bytes;
    }

    private function bitString(string $bytes): string
    {
        $payload = "\x00".$bytes;
        return "\x03".$this->length(strlen($payload)).$payload;
    }

    private function length(int $length): string
    {
        if ($length < 128) {
            return chr($length);
        }

        $encoded = '';
        while ($length > 0) {
            $encoded = chr($length & 0xff).$encoded;
            $length >>= 8;
        }

        return chr(0x80 | strlen($encoded)).$encoded;
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
