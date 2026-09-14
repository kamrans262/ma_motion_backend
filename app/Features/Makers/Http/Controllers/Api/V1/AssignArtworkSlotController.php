<?php

namespace App\Features\Makers\Http\Controllers\Api\V1;

use App\Features\Makers\Actions\AssignMakerProfileArtworkSlotAction;
use App\Features\Makers\Http\Requests\AssignMakerProfileArtworkSlotRequest;
use App\Features\Makers\Http\Resources\MakerProfileArtworkSlotResource;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class AssignArtworkSlotController extends Controller
{
    public function __invoke(
        AssignMakerProfileArtworkSlotRequest $request,
        int $slot,
        AssignMakerProfileArtworkSlotAction $action,
    ): JsonResponse {
        $assignment = $action->execute(
            $request->user(),
            $slot,
            (int) $request->validated('artwork_id'),
        );

        return ApiResponse::success(
            data: MakerProfileArtworkSlotResource::make($assignment)->resolve($request),
            message: 'Maker Info artwork assigned successfully.',
        );
    }
}
