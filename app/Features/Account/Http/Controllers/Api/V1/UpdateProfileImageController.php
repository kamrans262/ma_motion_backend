<?php

namespace App\Features\Account\Http\Controllers\Api\V1;

use App\Features\Account\Actions\UpdateMakerProfileImageAction;
use App\Features\Account\Http\Requests\UpdateProfileImageRequest;
use App\Features\Auth\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class UpdateProfileImageController extends Controller
{
    public function __invoke(UpdateProfileImageRequest $request, UpdateMakerProfileImageAction $action): JsonResponse
    {
        $user = $action->execute($request->user(), $request->file('image'));

        return ApiResponse::success(
            data: UserResource::make($user)->resolve($request),
            message: 'Profile image updated successfully.',
        );
    }
}
