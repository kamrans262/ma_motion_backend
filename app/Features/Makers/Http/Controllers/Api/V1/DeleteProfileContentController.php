<?php

namespace App\Features\Makers\Http\Controllers\Api\V1;

use App\Features\Auth\Enums\UserRole;
use App\Features\Makers\Actions\DeleteMakerProfileContentAction;
use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DeleteProfileContentController extends Controller
{
    public function __invoke(Request $request, int $slot, DeleteMakerProfileContentAction $action): JsonResponse
    {
        if (! $request->user()->hasRole(UserRole::Maker)) {
            throw new AuthorizationException('You are not authorized to perform this action.');
        }

        $action->execute($request->user(), $slot);

        return ApiResponse::success(
            data: null,
            message: 'Maker content removed successfully.',
        );
    }
}
