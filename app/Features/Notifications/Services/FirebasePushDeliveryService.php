<?php

namespace App\Features\Notifications\Services;

use App\Features\Notifications\Models\InAppNotification;
use App\Features\Notifications\Models\PushDeviceToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * FCM HTTP v1 delivery. Credentials are loaded server-side only.
 * A missing Firebase configuration safely disables external push.
 * In-app notifications remain the source of truth.
 */
final class FirebasePushDeliveryService
{
    public function send(InAppNotification $notification): void
    {
        $credentials = $this->credentials();
        if ($credentials === null) {
            return;
        }

        try {
            $accessToken = $this->accessToken($credentials);
        } catch (Throwable $exception) {
            Log::warning('MA Motion push OAuth authentication failed', [
                'notification_id' => $notification->id,
                'exception' => $exception->getMessage(),
            ]);
            return;
        }

        $projectId = (string) (config('services.ma_fcm.project_id') ?: $credentials['project_id']);
        if (!preg_match('/^[A-Za-z0-9-]+$/', $projectId)) {
            Log::warning('MA Motion push project ID is invalid');
            return;
        }

        $recipientTokens = PushDeviceToken::query()
            ->where('user_id', $notification->recipient_id)
            ->cursor();

        foreach ($recipientTokens as $device) {
            try {
                $data = [
                    'notification_id' => (string) $notification->id,
                    'type' => (string) $notification->type->value,
                    'maker_id' => (string) ($notification->maker_id ?? ''),
                    'show_id' => (string) ($notification->show_id ?? ''),
                ];

                $response = Http::withToken($accessToken)
                    ->timeout(12)
                    ->acceptJson()
                    ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                        'message' => [
                            'token' => $device->token,
                            'notification' => [
                                'title' => $notification->title,
                                'body' => $notification->body,
                            ],
                            'data' => $data,
                            'android' => [
                                'priority' => 'HIGH',
                                'notification' => ['channel_id' => 'ma_motion_show_alerts'],
                            ],
                            'apns' => [
                                'payload' => ['aps' => ['sound' => 'default']],
                            ],
                        ],
                    ]);

                if ($response->successful()) {
                    continue;
                }

                $status = (string) $response->json('error.status', '');
                $details = $response->json('error.details', []);
                $unregistered = $status === 'UNREGISTERED';
                if (is_array($details)) {
                    foreach ($details as $detail) {
                        if (is_array($detail) && ($detail['errorCode'] ?? null) === 'UNREGISTERED') {
                            $unregistered = true;
                        }
                    }
                }
                if ($unregistered) {
                    $device->delete();
                } else {
                    Log::warning('MA Motion FCM rejected push', [
                        'notification_id' => $notification->id,
                        'http_status' => $response->status(),
                        'error_status' => $status,
                    ]);
                }
            } catch (Throwable $exception) {
                // Never fail show publishing if an external push service is unavailable.
                Log::warning('MA Motion FCM push delivery failed', [
                    'notification_id' => $notification->id,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }
    }

    /** @return array<string, mixed>|null */
    private function credentials(): ?array
    {
        if (!config('services.ma_fcm.enabled')) {
            return null;
        }

        $path = trim((string) config('services.ma_fcm.credentials_path'));
        if ($path === '') {
            return null;
        }

        $file = str_starts_with($path, '/') ? $path : base_path($path);
        if (!is_file($file) || !is_readable($file)) {
            Log::warning('MA Motion Firebase service account file is missing or unreadable');
            return null;
        }

        $credentials = json_decode((string) file_get_contents($file), true);
        if (!is_array($credentials)
            || empty($credentials['client_email'])
            || empty($credentials['private_key'])
            || empty($credentials['project_id'])) {
            Log::warning('MA Motion Firebase service account file has invalid fields');
            return null;
        }

        return $credentials;
    }

    /** @param array<string, mixed> $credentials */
    private function accessToken(array $credentials): string
    {
        $cacheKey = 'ma_motion_fcm_oauth_'.hash('sha256', (string) $credentials['client_email']);

        return Cache::remember($cacheKey, now()->addMinutes(50), static function () use ($credentials): string {
            $now = time();
            $encode = static fn (array $value): string => rtrim(
                strtr(base64_encode((string) json_encode($value, JSON_THROW_ON_ERROR)), '+/', '-_'),
                '='
            );

            $header = $encode(['alg' => 'RS256', 'typ' => 'JWT']);
            $claims = $encode([
                'iss' => $credentials['client_email'],
                'sub' => $credentials['client_email'],
                'aud' => 'https://oauth2.googleapis.com/token',
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'iat' => $now,
                'exp' => $now + 3600,
            ]);
            $unsigned = "{$header}.{$claims}";
            if (!openssl_sign($unsigned, $signature, (string) $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
                throw new RuntimeException('Could not sign the Firebase service account assertion.');
            }

            $assertion = $unsigned.'.'.rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
            $response = Http::asForm()->timeout(12)->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ]);

            if (!$response->successful() || !is_string($response->json('access_token'))) {
                throw new RuntimeException('Firebase OAuth access token was not issued.');
            }

            return $response->json('access_token');
        });
    }
}
