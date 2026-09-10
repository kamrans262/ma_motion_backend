<?php

namespace App\Features\Saves\Http\Controllers\Api\V1;

use App\Features\Saves\Actions\UnsaveMakerAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DestroyController extends Controller
{
    public function __invoke(Request $request, User $maker, UnsaveMakerAction $action): JsonResponse
    {
        /** @var User $appreciator */
        $appreciator = $request->user();
        $action->execute($appreciator, $maker);

        return ApiResponse::success(
            data: ['maker_id' => $maker->id, 'saved' => false],
            message: 'Maker removed from saved Makers successfully.',
        );
    }
}
