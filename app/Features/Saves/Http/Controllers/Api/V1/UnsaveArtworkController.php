<?php

namespace App\Features\Saves\Http\Controllers\Api\V1;

use App\Features\Artworks\Models\Artwork;
use App\Features\Saves\Actions\UnsaveArtworkAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UnsaveArtworkController extends Controller
{
    public function __invoke(Request $request, Artwork $artwork, UnsaveArtworkAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $action->execute($user, $artwork);

        return ApiResponse::success(
            data: ['artwork_id' => $artwork->id, 'saved' => false],
            message: 'Artwork removed from saved artwork successfully.',
        );
    }
}
