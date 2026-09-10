<?php

namespace App\Features\Content\Http\Controllers\Api\V1;

use App\Features\Content\Http\Requests\ContentIndexRequest;
use App\Features\Content\Http\Resources\ContentPageSummaryResource;
use App\Features\Content\Services\PublicContentService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class IndexController extends Controller
{
    public function __invoke(ContentIndexRequest $request, PublicContentService $content): JsonResponse
    {
        $paginator = $content->paginate((int) ($request->validated('per_page') ?? 20));

        return ApiResponse::success(
            data: ContentPageSummaryResource::collection($paginator->getCollection())->resolve($request),
            message: 'App content was retrieved successfully.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        );
    }
}
