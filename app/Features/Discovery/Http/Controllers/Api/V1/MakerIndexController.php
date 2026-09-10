<?php

namespace App\Features\Discovery\Http\Controllers\Api\V1;

use App\Features\Discovery\Http\Requests\DiscoveryIndexRequest;
use App\Features\Discovery\Http\Resources\DiscoveryMakerResource;
use App\Features\Discovery\Services\MakerDiscoveryService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class MakerIndexController extends Controller
{
    public function __invoke(DiscoveryIndexRequest $request, MakerDiscoveryService $discovery): JsonResponse
    {
        $paginator = $discovery->paginate($request->validated());

        return ApiResponse::success(
            data: DiscoveryMakerResource::collection($paginator->getCollection())->resolve($request),
            message: 'Discovery Makers retrieved successfully.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        );
    }
}
