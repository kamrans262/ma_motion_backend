<?php

namespace App\Features\Discovery\Http\Controllers\Api\V1;

use App\Features\Discovery\Http\Requests\LocationIndexRequest;
use App\Features\Discovery\Http\Resources\DiscoveryLocationResource;
use App\Features\Discovery\Services\DiscoveryLocationService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class LocationIndexController extends Controller
{
    public function __invoke(LocationIndexRequest $request, DiscoveryLocationService $locations): JsonResponse
    {
        $paginator = $locations->paginate($request->validated());

        return ApiResponse::success(
            data: DiscoveryLocationResource::collection($paginator->getCollection())->resolve($request),
            message: 'Discovery locations retrieved successfully.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        );
    }
}
