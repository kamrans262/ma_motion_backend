<?php
namespace App\Features\Artworks\Http\Controllers\Api\V1;
use App\Features\Artworks\Actions\SetPrimaryArtworkMediaAction;
use App\Features\Artworks\Http\Resources\ArtworkResource;
use App\Features\Artworks\Services\MakerArtworkService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
final class SetPrimaryMediaController extends Controller
{
    public function __invoke(Request $request, int $artwork, int $media, MakerArtworkService $artworks, SetPrimaryArtworkMediaAction $action): JsonResponse
    {
        $model = $artworks->findOwned($request->user(), $artwork);
        $mediaModel = $artworks->findOwnedMedia($model, $media);
        $model = $action->execute($model, $mediaModel);
        return ApiResponse::success(data: ArtworkResource::make($model)->resolve($request), message: 'Primary artwork image updated successfully.');
    }
}
