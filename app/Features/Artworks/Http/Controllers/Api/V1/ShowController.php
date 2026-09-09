<?php
namespace App\Features\Artworks\Http\Controllers\Api\V1;
use App\Features\Artworks\Http\Resources\ArtworkResource;
use App\Features\Artworks\Services\MakerArtworkService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
final class ShowController extends Controller
{
    public function __invoke(Request $request, int $artwork, MakerArtworkService $artworks): JsonResponse
    {
        $model = $artworks->findOwned($request->user(), $artwork);
        return ApiResponse::success(data: ArtworkResource::make($model)->resolve($request), message: 'Artwork retrieved successfully.');
    }
}
