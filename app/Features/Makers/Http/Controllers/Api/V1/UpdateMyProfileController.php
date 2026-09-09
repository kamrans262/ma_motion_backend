<?php

namespace App\Features\Makers\Http\Controllers\Api\V1;

use App\Features\Makers\Actions\UpdateMyMakerProfileAction;
use App\Features\Makers\Http\Requests\UpdateMyMakerProfileRequest;
use App\Features\Makers\Http\Resources\MakerResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class UpdateMyProfileController extends Controller
{
    public function __invoke(UpdateMyMakerProfileRequest $request, UpdateMyMakerProfileAction $action): JsonResponse
    {
        $maker = $action->execute($request->user(), $request->validated());

        return ApiResponse::success(
            data: MakerResource::make($maker)->resolve($request),
            message: 'Maker profile updated successfully.',
        );
    }
}
