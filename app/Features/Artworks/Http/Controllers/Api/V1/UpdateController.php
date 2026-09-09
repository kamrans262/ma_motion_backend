<?php
namespace App\Features\Artworks\Http\Controllers\Api\V1;
use App\Features\Artworks\Actions\UpdateMyArtworkAction;
use App\Features\Artworks\Http\Requests\UpdateMyArtworkRequest;
use App\Features\Artworks\Http\Resources\ArtworkResource;
use App\Features\Artworks\Services\MakerArtworkService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
final class UpdateController extends Controller
{
    public function __invoke(UpdateMyArtworkRequest $request, int $artwork, MakerArtworkService $artworks, UpdateMyArtworkAction $action): JsonResponse
    {
        $model = $artworks->findOwned($request->user(), $artwork);
        $model = $action->execute($model, $request->validated());
        return ApiResponse::success(data: ArtworkResource::make($model)->resolve($request), message: 'Artwork updated successfully and is pending review.');
    }
}
