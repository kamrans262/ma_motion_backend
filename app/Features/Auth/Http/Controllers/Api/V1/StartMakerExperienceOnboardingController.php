<?php

namespace App\Features\Auth\Http\Controllers\Api\V1;

use App\Features\Auth\Actions\StartMakerExperienceOnboardingAction;
use App\Features\Auth\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class StartMakerExperienceOnboardingController extends Controller
{
    public function __invoke(
        Request $request,
        StartMakerExperienceOnboardingAction $action,
    ): JsonResponse {
        $user = $action->execute($request->user());

        return ApiResponse::success(
            data: UserResource::make($user)->resolve($request),
            message: 'Maker onboarding is ready for this account.',
        );
    }
}
