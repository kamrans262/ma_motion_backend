<?php
namespace App\Features\Artworks\Http\Controllers\Api\V1;
use App\Features\Artworks\Actions\CreateArtworkAction;
use App\Features\Artworks\Http\Requests\StoreArtworkRequest;
use App\Features\Artworks\Http\Resources\ArtworkResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
final class StoreController extends Controller
{
    public function __invoke(StoreArtworkRequest $request, CreateArtworkAction $action): JsonResponse
    {
        $artwork = $action->execute($request->user(), $request->safe()->except('media'), $request->file('media', []));
        return ApiResponse::success(data: ArtworkResource::make($artwork)->resolve($request), message: 'Artwork uploaded successfully and is pending review.', status: 201);
    }
}
