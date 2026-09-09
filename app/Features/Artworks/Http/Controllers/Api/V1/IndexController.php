<?php
namespace App\Features\Artworks\Http\Controllers\Api\V1;
use App\Features\Artworks\Http\Requests\MakerArtworkIndexRequest;
use App\Features\Artworks\Http\Resources\ArtworkResource;
use App\Features\Artworks\Services\MakerArtworkService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
final class IndexController extends Controller
{
    public function __invoke(MakerArtworkIndexRequest $request, MakerArtworkService $artworks): JsonResponse
    {
        $paginator = $artworks->paginate($request->user(), $request->validated());
        return ApiResponse::success(
            data: ArtworkResource::collection($paginator->getCollection())->resolve($request),
            message: 'Your artwork was retrieved successfully.',
            meta: ['current_page'=>$paginator->currentPage(),'last_page'=>$paginator->lastPage(),'per_page'=>$paginator->perPage(),'total'=>$paginator->total()],
        );
    }
}
