<?php

namespace App\Features\Saves\Http\Controllers\Api\V1;

use App\Features\Makers\Http\Resources\MakerResource;
use App\Features\Saves\Actions\SaveMakerAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class StoreController extends Controller
{
    public function __invoke(Request $request, User $maker, SaveMakerAction $action): JsonResponse
    {
        /** @var User $appreciator */
        $appreciator = $request->user();
        $save = $action->execute($appreciator, $maker);

        $maker->loadMissing('makerProfile');
        $maker->loadCount(['savedByAppreciators as saves_count']);

        return ApiResponse::success(
            data: [
                'saved' => true,
                'saved_at' => $save->created_at?->toISOString(),
                'maker' => MakerResource::make($maker)->resolve($request),
            ],
            message: 'Maker saved successfully.',
        );
    }
}
