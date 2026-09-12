<?php

namespace App\Features\Auth\Http\Controllers\Api\V1;

use App\Features\Auth\Actions\RegisterMakerOnboardingAction;
use App\Features\Auth\Http\Requests\MakerOnboardingRegisterRequest;
use App\Features\Auth\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class MakerOnboardingRegisterController extends Controller
{
    public function __invoke(
        MakerOnboardingRegisterRequest $request,
        RegisterMakerOnboardingAction $action,
    ): JsonResponse {
        $result = $action->execute($request->validated());

        return ApiResponse::success(
            data: [
                'user' => UserResource::make($result['user'])->resolve($request),
                'token' => $result['token'],
                'token_type' => 'Bearer',
            ],
            message: 'Maker onboarding session created successfully.',
            status: 201,
        );
    }
}
