<?php

namespace App\Features\Makers\Http\Controllers\Api\V1;

use App\Features\Discovery\Http\Resources\DiscoveryArtworkResource;
use App\Features\Makers\Http\Requests\PublicMakerArtworkRequest;
use App\Features\Makers\Services\PublicMakerArtworkService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class ArtworkIndexController extends Controller
{
    public function __invoke(User $maker, PublicMakerArtworkRequest $request, PublicMakerArtworkService $service): JsonResponse
    {
        $data = $request->validated();
        $paginator = $service->paginate($maker, (int) ($data['per_page'] ?? 24));

        return ApiResponse::success(
            data: DiscoveryArtworkResource::collection($paginator->getCollection())->resolve($request),
            message: 'Maker artwork retrieved successfully.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        );
    }
}
