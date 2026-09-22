<?php

namespace App\Features\Auth\Http\Controllers\Api\V1;

use App\Features\Auth\Http\Resources\UserResource;
use App\Features\Auth\Services\EmailOtpService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class VerifyEmailOtpController extends Controller
{
    public function __invoke(Request $request, EmailOtpService $service): JsonResponse
    {
        $data = $request->validate([
            'challenge_id' => ['required', 'uuid'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'code' => ['required', 'regex:/^[0-9]{6}$/'],
        ]);
        $result = $service->verify($data['challenge_id'], $data['email'], $data['code'], $request->user('sanctum'));
        if ($result['purpose'] === 'invalid') {
            throw ValidationException::withMessages(['code' => 'Incorrect code. Please try again.']);
        }

        return ApiResponse::success(
            data: isset($result['token'])
                ? ['user' => UserResource::make($result['user'])->resolve($request), 'token' => $result['token'], 'token_type' => 'Bearer']
                : ['verified' => true, 'challenge_id' => $data['challenge_id']],
            message: 'Email verified successfully.',
        );
    }
}
