<?php

namespace App\Features\Makers\Http\Controllers\Api\V1;

use App\Features\Makers\Http\Requests\MakerDirectoryRequest;
use App\Features\Makers\Http\Resources\MakerResource;
use App\Features\Makers\Services\MakerDirectoryService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class IndexController extends Controller
{
    public function __invoke(MakerDirectoryRequest $request, MakerDirectoryService $makers): JsonResponse
    {
        $paginator = $makers->paginate($request->validated());

        return ApiResponse::success(
            data: MakerResource::collection($paginator->getCollection())->resolve($request),
            message: 'Makers retrieved successfully.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        );
    }
}
