<?php

namespace App\Features\Account\Http\Controllers\Api\V1;

use App\Features\Account\Actions\ChangePasswordAction;
use App\Features\Account\Http\Requests\ChangePasswordRequest;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class ChangePasswordController extends Controller
{
    public function __invoke(ChangePasswordRequest $request, ChangePasswordAction $action): JsonResponse
    {
        $action->execute($request->user(), $request->validated());

        return ApiResponse::success(message: 'Password changed successfully. Other sessions were signed out.');
    }
}
