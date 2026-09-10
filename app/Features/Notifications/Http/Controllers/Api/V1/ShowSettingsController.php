<?php

namespace App\Features\Notifications\Http\Controllers\Api\V1;

use App\Features\Notifications\Services\NotificationPreferenceService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ShowSettingsController extends Controller
{
    public function __invoke(Request $request, NotificationPreferenceService $preferences): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $preference = $preferences->forUser($user);

        return ApiResponse::success(
            data: ['saved_maker_show_alerts' => $preference->saved_maker_show_alerts],
            message: 'Notification settings were retrieved successfully.',
        );
    }
}
