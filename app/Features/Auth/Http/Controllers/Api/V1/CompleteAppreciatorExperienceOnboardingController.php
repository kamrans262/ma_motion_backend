<?php

namespace App\Features\Auth\Http\Controllers\Api\V1;

use App\Features\Auth\Actions\CompleteAppreciatorExperienceOnboardingAction;
use App\Features\Auth\Http\Requests\CompleteAppreciatorExperienceOnboardingRequest;
use App\Features\Auth\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class CompleteAppreciatorExperienceOnboardingController extends Controller
{
    public function __invoke(
        CompleteAppreciatorExperienceOnboardingRequest $request,
        CompleteAppreciatorExperienceOnboardingAction $action,
    ): JsonResponse {
        $user = $action->execute($request->user(), $request->validated());

        return ApiResponse::success(
            data: UserResource::make($user)->resolve($request),
            message: 'Appreciator onboarding completed for this account.',
        );
    }
}
