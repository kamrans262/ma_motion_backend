<?php

namespace App\Features\Discovery\Http\Controllers\Api\V1;

use App\Features\Discovery\Services\DiscoveryFilterOptionsService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class FilterOptionsController extends Controller
{
    public function __invoke(DiscoveryFilterOptionsService $options): JsonResponse
    {
        return ApiResponse::success(
            data: $options->get(),
            message: 'Discovery filter options retrieved successfully.',
        );
    }
}
