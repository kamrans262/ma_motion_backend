<?php

namespace App\Features\Auth\Http\Controllers\Api\V1;

use App\Features\Auth\Actions\RegisterAppreciatorOnboardingAction;
use App\Features\Auth\Http\Requests\AppreciatorOnboardingRegisterRequest;
use App\Features\Auth\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class AppreciatorOnboardingRegisterController extends Controller
{
    public function __invoke(
        AppreciatorOnboardingRegisterRequest $request,
        RegisterAppreciatorOnboardingAction $action,
    ): JsonResponse {
        $result = $action->execute($request->validated());

        return ApiResponse::success(
            data: [
                'user' => UserResource::make($result['user'])->resolve($request),
                'token' => $result['token'],
                'token_type' => 'Bearer',
            ],
            message: 'Appreciator onboarding completed successfully.',
            status: 201,
        );
    }
}
