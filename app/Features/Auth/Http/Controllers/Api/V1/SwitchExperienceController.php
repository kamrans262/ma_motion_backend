<?php

namespace App\Features\Auth\Http\Controllers\Api\V1;

use App\Features\Auth\Actions\SwitchUserExperienceAction;
use App\Features\Auth\Enums\UserRole;
use App\Features\Auth\Http\Requests\SwitchExperienceRequest;
use App\Features\Auth\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class SwitchExperienceController extends Controller
{
    public function __invoke(
        SwitchExperienceRequest $request,
        SwitchUserExperienceAction $action,
    ): JsonResponse {
        $user = $action->execute(
            $request->user(),
            UserRole::from((string) $request->validated('experience')),
        );

        return ApiResponse::success(
            data: UserResource::make($user)->resolve($request),
            message: 'Active experience switched successfully.',
        );
    }
}
