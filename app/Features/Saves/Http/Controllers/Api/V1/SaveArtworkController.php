<?php

namespace App\Features\Saves\Http\Controllers\Api\V1;

use App\Features\Artworks\Models\Artwork;
use App\Features\Saves\Actions\SaveArtworkAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SaveArtworkController extends Controller
{
    public function __invoke(Request $request, Artwork $artwork, SaveArtworkAction $action): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $save = $action->execute($user, $artwork);

        return ApiResponse::success(
            data: [
                'artwork_id' => $artwork->id,
                'saved' => true,
                'saved_at' => $save->created_at?->toISOString(),
            ],
            message: 'Artwork saved successfully.',
        );
    }
}
