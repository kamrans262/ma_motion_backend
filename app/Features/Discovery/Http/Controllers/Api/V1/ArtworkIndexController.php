<?php

namespace App\Features\Discovery\Http\Controllers\Api\V1;

use App\Features\Discovery\Http\Requests\DiscoveryIndexRequest;
use App\Features\Discovery\Http\Resources\DiscoveryArtworkResource;
use App\Features\Discovery\Services\ArtworkDiscoveryService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class ArtworkIndexController extends Controller
{
    public function __invoke(DiscoveryIndexRequest $request, ArtworkDiscoveryService $discovery): JsonResponse
    {
        $paginator = $discovery->paginate($request->validated());

        return ApiResponse::success(
            data: DiscoveryArtworkResource::collection($paginator->getCollection())->resolve($request),
            message: 'Discovery artwork retrieved successfully.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        );
    }
}
