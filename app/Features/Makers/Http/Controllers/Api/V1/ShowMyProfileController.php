<?php

namespace App\Features\Makers\Http\Controllers\Api\V1;

use App\Features\Makers\Http\Resources\MyMakerProfileResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ShowMyProfileController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $maker = $request->user()->load([
            'makerProfile.location',
            'makerProfile.types',
            'makerProfile.styles',
            'makerProfile.contents',
            'makerProfile.artworkSlots.artwork.maker',
            'makerProfile.artworkSlots.artwork.type',
            'makerProfile.artworkSlots.artwork.style',
            'makerProfile.artworkSlots.artwork.location',
            'makerProfile.artworkSlots.artwork.media',
        ]);

        return ApiResponse::success(
            data: MyMakerProfileResource::make($maker)->resolve($request),
            message: 'Maker profile retrieved successfully.',
        );
    }
}
