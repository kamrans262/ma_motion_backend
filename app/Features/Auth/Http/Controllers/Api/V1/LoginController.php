<?php

namespace App\Features\Auth\Http\Controllers\Api\V1;

use App\Features\Auth\Actions\LoginUserAction;
use App\Features\Auth\Http\Requests\LoginRequest;
use App\Features\Auth\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class LoginController extends Controller
{
    public function __invoke(LoginRequest $request, LoginUserAction $action): JsonResponse
    {
        $data = $request->validated();
        $result = $action->execute(
            email: $data['email'],
            password: $data['password'],
            deviceName: $data['device_name'] ?? null,
        );

        return ApiResponse::success(
            data: [
                'user' => UserResource::make($result['user'])->resolve($request),
                'token' => $result['token'],
                'token_type' => 'Bearer',
            ],
            message: 'Logged in successfully.',
        );
    }
}
