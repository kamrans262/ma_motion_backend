<?php
namespace App\Features\Artworks\Http\Controllers\Api\V1;
use App\Features\Artworks\Actions\DeleteArtworkAction;
use App\Features\Artworks\Services\MakerArtworkService;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
final class DestroyController extends Controller
{
    public function __invoke(Request $request, int $artwork, MakerArtworkService $artworks, DeleteArtworkAction $action): JsonResponse
    {
        $model = $artworks->findOwned($request->user(), $artwork);
        $action->execute($model);
        return ApiResponse::success(message: 'Artwork deleted successfully.');
    }
}
