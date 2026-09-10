<?php

namespace App\Features\Notifications\Http\Controllers\Api\V1;

use App\Features\Notifications\Services\InAppNotificationService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MarkAllReadController extends Controller
{
    public function __invoke(Request $request, InAppNotificationService $notifications): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $updated = $notifications->markAllRead($user);

        return ApiResponse::success(
            data: ['marked_read' => $updated, 'unread_count' => 0],
            message: 'All notifications were marked as read.',
        );
    }
}
