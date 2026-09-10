<?php

namespace App\Features\Notifications\Http\Controllers\Api\V1;

use App\Features\Notifications\Http\Requests\NotificationIndexRequest;
use App\Features\Notifications\Http\Resources\InAppNotificationResource;
use App\Features\Notifications\Services\InAppNotificationService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class IndexController extends Controller
{
    public function __invoke(NotificationIndexRequest $request, InAppNotificationService $notifications): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $paginator = $notifications->paginate($user, $request->validated());

        return ApiResponse::success(
            data: InAppNotificationResource::collection($paginator->getCollection())->resolve($request),
            message: 'Notifications were retrieved successfully.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'unread_count' => $notifications->unreadCount($user),
            ],
        );
    }
}
