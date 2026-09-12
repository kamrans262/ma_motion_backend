<?php

namespace App\Features\Makers\Http\Controllers\Api\V1;

use App\Features\Auth\Enums\UserRole;
use App\Features\Makers\Actions\SaveMakerProfileContentAction;
use App\Features\Makers\Http\Requests\SaveMakerProfileContentRequest;
use App\Features\Makers\Http\Resources\MakerProfileContentResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

final class SaveProfileContentController extends Controller
{
    public function __invoke(
        SaveMakerProfileContentRequest $request,
        int $slot,
        SaveMakerProfileContentAction $action,
    ): JsonResponse {
        if (! $request->user()->hasRole(UserRole::Maker)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }

        $content = $action->execute(
            $request->user(),
            $slot,
            $request->file('media'),
            $request->validated('caption'),
        );

        return ApiResponse::success(
            data: MakerProfileContentResource::make($content)->resolve($request),
            message: 'Maker content saved successfully.',
        );
    }
}
