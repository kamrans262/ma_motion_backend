<?php

namespace App\Features\Appreciators\Http\Controllers\Api\V1;

use App\Features\Appreciators\Actions\UpdateMyAppreciatorProfileAction;
use App\Features\Appreciators\Http\Requests\UpdateMyAppreciatorProfileRequest;
use App\Features\Appreciators\Http\Resources\MyAppreciatorProfileResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class UpdateMyAppreciatorProfileController extends Controller
{
    public function __invoke(
        UpdateMyAppreciatorProfileRequest $request,
        UpdateMyAppreciatorProfileAction $action,
    ): JsonResponse {
        $user = $action->execute($request->user(), $request->validated());

        return ApiResponse::success(
            data: MyAppreciatorProfileResource::make($user)->resolve($request),
            message: 'Appreciator settings saved successfully.',
        );
    }
}
