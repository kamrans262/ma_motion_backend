<?php

namespace App\Features\FeaturedMaker\Http\Controllers\Api\V1;

use App\Features\FeaturedMaker\Http\Resources\FeaturedMakerResource;
use App\Features\FeaturedMaker\Services\FeaturedMakerService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ShowController extends Controller
{
    public function __invoke(Request $request, FeaturedMakerService $service): JsonResponse
    {
        $setting = $service->current();

        return ApiResponse::success(
            data: $setting ? FeaturedMakerResource::make($setting)->resolve($request) : null,
            message: $setting
                ? 'Featured Maker retrieved successfully.'
                : 'No Featured Maker is currently published.',
        );
    }
}
