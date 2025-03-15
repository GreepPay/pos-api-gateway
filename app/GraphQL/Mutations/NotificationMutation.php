<?php

namespace App\GraphQL\Mutations;

use App\Exceptions\GraphQLException;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

final class NotificationMutation
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    // Device Tokens

    public function registerDeviceToken($_, array $args)
    {
        $response = $this->notificationService->registerDeviceToken(new Request($args['input']));

        return $response["data"] ?? null;
    }

    public function updateDeviceToken($_, array $args)
    {
        $response = $this->notificationService->updateDeviceToken(new Request($args['input']));

        return $response["data"] ?? null;
    }

    public function deleteDeviceToken($_, array $args)
    {
        $response = $this->notificationService->deleteDeviceToken(new Request($args['input']));

        return $response["data"] ?? null;
    }

    // Notifications

    public function sendNotification($_, array $args)
    {
        $response = $this->notificationService->sendNotification(new Request($args['input']));

        return $response["data"] ?? null;
    }

    public function deleteNotification($_, array $args)
    {
        $response = $this->notificationService->deleteNotification(new Request($args['input']));

        return $response["data"] ?? null;
    }

    public function updateNotificationStatus($_, array $args)
    {
        $response = $this->notificationService->updateNotificationStatus(new Request($args['input']));

        return $response["data"] ?? null;
    }

    public function broadcastNotification($_, array $args)
    {
        $response = $this->notificationService->broadcastNotification(new Request($args['input']));

        return $response["data"] ?? null;
    }

    // Notification Templates

    public function createNotificationTemplate($_, array $args)
    {
        $response = $this->notificationService->createNotificationTemplate(new Request($args['input']));

        return $response["data"] ?? null;
    }

    public function updateNotificationTemplate($_, array $args)
    {
        $response = $this->notificationService->updateNotificationTemplate(new Request($args['input']));

        return $response["data"] ?? null;
    }

    public function deleteNotificationTemplate($_, array $args)
    {
        $response = $this->notificationService->deleteNotificationTemplate(new Request($args['input']));

        return $response["data"] ?? null;
    }

    /**
     * Convert DateTime string to Carbon instance.
     */
    private function parseDateTime(?string $date): ?string
    {
        return $date ? Carbon::parse($date)->format('Y-m-d H:i:s') : null;
    }
}
