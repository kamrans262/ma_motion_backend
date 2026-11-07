<?php

namespace App\Features\Saves\Http\Controllers\Api\V1;

use App\Features\Discovery\Http\Resources\DiscoveryArtworkResource;
use App\Features\Saves\Http\Requests\SavedArtworkIndexRequest;
use App\Features\Saves\Services\SavedArtworkService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class SavedArtworkIndexController extends Controller
{
    public function __invoke(SavedArtworkIndexRequest $request, SavedArtworkService $savedArtworks): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $paginator = $savedArtworks->paginate($user, $request->validated());

        return ApiResponse::success(
            data: DiscoveryArtworkResource::collection($paginator->getCollection())->resolve($request),
            message: 'Saved artwork retrieved successfully.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        );
    }
}
