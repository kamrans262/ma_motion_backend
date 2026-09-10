<?php

namespace App\Features\Saves\Http\Controllers\Api\V1;

use App\Features\Makers\Http\Resources\MakerResource;
use App\Features\Saves\Http\Requests\SavedMakerIndexRequest;
use App\Features\Saves\Services\SavedMakerService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class IndexController extends Controller
{
    public function __invoke(SavedMakerIndexRequest $request, SavedMakerService $savedMakers): JsonResponse
    {
        /** @var User $appreciator */
        $appreciator = $request->user();
        $paginator = $savedMakers->paginate($appreciator, $request->validated());

        return ApiResponse::success(
            data: MakerResource::collection($paginator->getCollection())->resolve($request),
            message: 'Saved Makers were retrieved successfully.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        );
    }
}
