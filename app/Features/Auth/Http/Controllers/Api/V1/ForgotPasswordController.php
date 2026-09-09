<?php

namespace App\Features\Auth\Http\Controllers\Api\V1;

use App\Features\Auth\Actions\RequestPasswordResetAction;
use App\Features\Auth\Http\Requests\ForgotPasswordRequest;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class ForgotPasswordController extends Controller
{
    public function __invoke(ForgotPasswordRequest $request, RequestPasswordResetAction $action): JsonResponse
    {
        $action->execute($request->validated('email'));

        return ApiResponse::success(
            message: 'If an account exists for that email, password reset instructions have been sent.',
        );
    }
}
