<?php

namespace App\Features\Makers\Http\Controllers\Api\V1;

use App\Features\Makers\Actions\UpsertMakerProfileCarouselAction;
use App\Features\Makers\Http\Requests\UpsertMakerProfileCarouselRequest;
use App\Features\Makers\Http\Resources\MakerProfileCarouselMediaResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class UpsertMyCarouselController extends Controller
{
    public function __invoke(
        UpsertMakerProfileCarouselRequest $request,
        int $slot,
        UpsertMakerProfileCarouselAction $action,
    ): JsonResponse {
        $media = $action->execute(
            $request->user(),
            $slot,
            $request->safe()->except('media'),
            $request->file('media'),
        );

        return ApiResponse::success(
            data: MakerProfileCarouselMediaResource::make($media)->resolve($request),
            message: 'Maker carousel content saved successfully.',
        );
    }
}
