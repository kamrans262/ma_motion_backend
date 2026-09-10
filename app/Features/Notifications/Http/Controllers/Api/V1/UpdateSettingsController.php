<?php

namespace App\Features\Notifications\Http\Controllers\Api\V1;

use App\Features\Notifications\Http\Requests\UpdateNotificationSettingsRequest;
use App\Features\Notifications\Services\NotificationPreferenceService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

final class UpdateSettingsController extends Controller
{
    public function __invoke(UpdateNotificationSettingsRequest $request, NotificationPreferenceService $preferences): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $preference = $preferences->update($user, $request->validated());

        return ApiResponse::success(
            data: ['saved_maker_show_alerts' => $preference->saved_maker_show_alerts],
            message: 'Notification settings were updated successfully.',
        );
    }
}
