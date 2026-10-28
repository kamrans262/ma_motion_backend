<?php

namespace App\Features\Notifications\Http\Controllers\Api\V1;

use App\Features\Notifications\Models\PushDeviceToken;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class RegisterPushTokenController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'min:10', 'max:4096'],
            'platform' => ['required', Rule::in(['android', 'ios'])],
        ]);

        PushDeviceToken::query()->updateOrCreate(
            ['token_hash' => hash('sha256', $data['token'])],
            [
                'user_id' => $request->user()->id,
                'personal_access_token_id' => method_exists($request->user()->currentAccessToken(), 'getKey')
                    ? $request->user()->currentAccessToken()->getKey()
                    : null,
                'token' => $data['token'],
                'platform' => $data['platform'],
                'last_seen_at' => now(),
            ],
        );

        return ApiResponse::success(data: ['registered' => true], message: 'Push device registered.');
    }
}
