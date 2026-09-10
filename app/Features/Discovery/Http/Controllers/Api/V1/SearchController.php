<?php

namespace App\Features\Discovery\Http\Controllers\Api\V1;

use App\Features\Discovery\Http\Requests\DiscoveryIndexRequest;
use App\Features\Discovery\Http\Resources\DiscoveryArtworkResource;
use App\Features\Discovery\Http\Resources\DiscoveryMakerResource;
use App\Features\Discovery\Services\ArtworkDiscoveryService;
use App\Features\Discovery\Services\MakerDiscoveryService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class SearchController extends Controller
{
    public function __invoke(
        DiscoveryIndexRequest $request,
        ArtworkDiscoveryService $artworks,
        MakerDiscoveryService $makers,
    ): JsonResponse {
        $filters = $request->validated();
        $artworkPaginator = $artworks->paginate($filters);
        $makerPaginator = $makers->paginate($filters);

        return ApiResponse::success(
            data: [
                'artworks' => DiscoveryArtworkResource::collection($artworkPaginator->getCollection())->resolve($request),
                'makers' => DiscoveryMakerResource::collection($makerPaginator->getCollection())->resolve($request),
            ],
            message: 'Discovery search completed successfully.',
            meta: [
                'artworks' => $this->paginationMeta($artworkPaginator),
                'makers' => $this->paginationMeta($makerPaginator),
            ],
        );
    }

    /** @return array<string, int> */
    private function paginationMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
