<?php

namespace App\Features\Account\Http\Controllers\Api\V1;

use App\Features\Account\Actions\UpdateProfileAction;
use App\Features\Account\Http\Requests\UpdateProfileRequest;
use App\Features\Auth\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class UpdateProfileController extends Controller
{
    public function __invoke(UpdateProfileRequest $request, UpdateProfileAction $action): JsonResponse
    {
        $user = $action->execute($request->user(), (string) $request->validated('name'));

        return ApiResponse::success(
            data: UserResource::make($user)->resolve($request),
            message: 'Profile updated successfully.',
        );
    }
}
