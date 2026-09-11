<?php

namespace App\Features\Makers\Http\Controllers\Api\V1;

use App\Features\Makers\Actions\DeleteMakerProfileCarouselAction;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DeleteMyCarouselController extends Controller
{
    public function __invoke(
        Request $request,
        int $slot,
        DeleteMakerProfileCarouselAction $action,
    ): JsonResponse {
        $action->execute($request->user(), $slot);

        return ApiResponse::success(
            data: null,
            message: 'Maker carousel content removed successfully.',
        );
    }
}
