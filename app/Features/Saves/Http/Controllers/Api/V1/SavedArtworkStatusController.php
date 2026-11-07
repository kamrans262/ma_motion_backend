<?php

namespace App\Features\Saves\Http\Controllers\Api\V1;

use App\Features\Artworks\Models\Artwork;
use App\Features\Saves\Models\ArtworkSave;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SavedArtworkStatusController extends Controller
{
    public function __invoke(Request $request, Artwork $artwork): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return ApiResponse::success(
            data: [
                'artwork_id' => $artwork->id,
                'saved' => ArtworkSave::query()
                    ->where('user_id', $user->id)
                    ->where('artwork_id', $artwork->id)
                    ->exists(),
            ],
            message: 'Saved artwork status retrieved successfully.',
        );
    }
}
