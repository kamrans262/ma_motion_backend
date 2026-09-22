<?php

namespace App\Features\Auth\Http\Controllers\Api\V1;

use App\Features\Auth\Services\EmailOtpService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class RequestEmailOtpController extends Controller
{
    public function __invoke(Request $request, EmailOtpService $service): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'purpose' => ['required', Rule::in(['login', 'register', 'confirm'])],
        ]);
        $actor = $request->user('sanctum');
        if ($data['purpose'] === 'confirm' && ! $actor) {
            abort(401);
        }
        $id = $service->request($data['email'], $data['purpose'], $actor);

        return ApiResponse::success(
            data: ['challenge_id' => $id, 'expires_in_seconds' => 600, 'resend_after_seconds' => 60],
            message: 'If this email is eligible, a verification code has been sent.',
        );
    }
}
