<?php

namespace App\Features\Notifications\Http\Controllers\Api\V1;

use App\Features\Notifications\Http\Resources\InAppNotificationResource;
use App\Features\Notifications\Services\InAppNotificationService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MarkReadController extends Controller
{
    public function __invoke(Request $request, int $notification, InAppNotificationService $notifications): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $model = $notifications->markRead($notifications->findOwned($user, $notification));

        return ApiResponse::success(
            data: InAppNotificationResource::make($model)->resolve($request),
            message: 'Notification marked as read.',
        );
    }
}
