<?php

namespace App\Features\Content\Http\Controllers\Api\V1;

use App\Features\Content\Http\Resources\ContentPageResource;
use App\Features\Content\Services\PublicContentService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ShowController extends Controller
{
    public function __invoke(Request $request, string $slug, PublicContentService $content): JsonResponse
    {
        $page = $content->findPublishedBySlug($slug);
        abort_if($page === null, 404);

        return ApiResponse::success(
            data: ContentPageResource::make($page)->resolve($request),
            message: 'App content was retrieved successfully.',
        );
    }
}
