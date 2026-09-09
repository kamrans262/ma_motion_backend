<?php
namespace App\Features\Artworks\Http\Controllers\Api\V1;
use App\Features\Artworks\Actions\AddArtworkMediaAction;
use App\Features\Artworks\Http\Requests\AddArtworkMediaRequest;
use App\Features\Artworks\Http\Resources\ArtworkResource;
use App\Features\Artworks\Services\MakerArtworkService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
final class AddMediaController extends Controller
{
    public function __invoke(AddArtworkMediaRequest $request, int $artwork, MakerArtworkService $artworks, AddArtworkMediaAction $action): JsonResponse
    {
        $model = $artworks->findOwned($request->user(), $artwork);
        $model = $action->execute($model, $request->file('media', []));
        return ApiResponse::success(data: ArtworkResource::make($model)->resolve($request), message: 'Artwork images added successfully and moderation was reset to pending.', status: 201);
    }
}
