<?php

namespace App\Features\Notifications\Jobs;

use App\Features\Notifications\Models\InAppNotification;
use App\Features\Notifications\Services\FirebasePushDeliveryService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class SendPushNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $notificationId) {}

    public function handle(FirebasePushDeliveryService $delivery): void
    {
        $notification = InAppNotification::query()->find($this->notificationId);
        if ($notification !== null) {
            $delivery->send($notification);
        }
    }
}
