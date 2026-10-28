<?php

namespace App\Features\Notifications\Http\Controllers\Api\V1;

use App\Features\Notifications\Models\PushDeviceToken;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UnregisterPushTokenController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'min:10', 'max:4096']]);

        PushDeviceToken::query()
            ->where('user_id', $request->user()->id)
            ->where('token_hash', hash('sha256', $data['token']))
            ->delete();

        return ApiResponse::success(data: ['unregistered' => true], message: 'Push device unregistered.');
    }
}
