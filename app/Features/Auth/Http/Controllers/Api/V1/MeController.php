<?php

namespace App\Features\Auth\Http\Controllers\Api\V1;

use App\Features\Auth\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing(['makerProfile', 'appreciatorProfile']);

        return ApiResponse::success(
            data: UserResource::make($user)->resolve($request),
            message: 'Authenticated user retrieved successfully.',
        );
    }
}
