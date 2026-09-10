<?php

namespace App\Features\Saves\Http\Controllers\Api\V1;

use App\Features\Saves\Services\MakerStatisticsService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MakerStatisticsController extends Controller
{
    public function __invoke(Request $request, MakerStatisticsService $statistics): JsonResponse
    {
        /** @var User $maker */
        $maker = $request->user();

        return ApiResponse::success(
            data: $statistics->forMaker($maker),
            message: 'Maker statistics were retrieved successfully.',
        );
    }
}
