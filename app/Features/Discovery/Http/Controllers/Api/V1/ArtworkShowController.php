<?php

namespace App\Features\Discovery\Http\Controllers\Api\V1;

use App\Features\Discovery\Http\Resources\DiscoveryArtworkDetailResource;
use App\Features\Discovery\Services\ArtworkDetailService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ArtworkShowController extends Controller
{
    public function __invoke(Request $request, int $artwork, ArtworkDetailService $artworks): JsonResponse
    {
        $model = $artworks->findVisible($artwork);

        return ApiResponse::success(
            data: DiscoveryArtworkDetailResource::make($model)->resolve($request),
            message: 'Artwork retrieved successfully.',
        );
    }
}
