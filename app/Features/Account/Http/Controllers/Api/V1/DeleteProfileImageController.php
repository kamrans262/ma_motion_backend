<?php

namespace App\Features\Account\Http\Controllers\Api\V1;

use App\Features\Account\Actions\DeleteMakerProfileImageAction;
use App\Features\Auth\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DeleteProfileImageController extends Controller
{
    public function __invoke(Request $request, DeleteMakerProfileImageAction $action): JsonResponse
    {
        $user = $action->execute($request->user());

        return ApiResponse::success(
            data: UserResource::make($user)->resolve($request),
            message: 'Profile image removed successfully.',
        );
    }
}
