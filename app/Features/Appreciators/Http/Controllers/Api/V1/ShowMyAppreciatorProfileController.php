<?php

namespace App\Features\Appreciators\Http\Controllers\Api\V1;

use App\Features\Appreciators\Http\Resources\MyAppreciatorProfileResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ShowMyAppreciatorProfileController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing(['appreciatorProfile.location']);

        return ApiResponse::success(
            data: MyAppreciatorProfileResource::make($user)->resolve($request),
            message: 'Appreciator profile retrieved successfully.',
        );
    }
}
