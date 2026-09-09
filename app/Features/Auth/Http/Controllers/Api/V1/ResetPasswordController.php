<?php

namespace App\Features\Auth\Http\Controllers\Api\V1;

use App\Features\Auth\Actions\ResetPasswordAction;
use App\Features\Auth\Http\Requests\ResetPasswordRequest;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class ResetPasswordController extends Controller
{
    public function __invoke(ResetPasswordRequest $request, ResetPasswordAction $action): JsonResponse
    {
        $data = $request->validated();

        $action->execute(
            email: $data['email'],
            token: $data['token'],
            password: $data['password'],
        );

        return ApiResponse::success(
            message: 'Password reset successfully. Please log in again.',
        );
    }
}
