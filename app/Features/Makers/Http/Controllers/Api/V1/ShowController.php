<?php

namespace App\Features\Makers\Http\Controllers\Api\V1;

use App\Features\Makers\Http\Resources\MakerResource;
use App\Features\Makers\Services\MakerDirectoryService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ShowController extends Controller
{
    public function __invoke(Request $request, int $maker, MakerDirectoryService $makers): JsonResponse
    {
        $user = $makers->findVisible($maker);

        return ApiResponse::success(
            data: MakerResource::make($user)->resolve($request),
            message: 'Maker retrieved successfully.',
        );
    }
}
