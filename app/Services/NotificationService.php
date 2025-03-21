<?php

namespace App\Services;

use App\Datasource\NetworkHandler;

class NotificationService
{
    protected $serviceUrl;
    protected $notificationNetwork;

    public function __construct(
        $useCache = true,
        $headers = [],
        $apiType = "graphql"
    ) {
        $this->serviceUrl = env(
            "Notification_API",
            env("SERVICE_BROKER_URL") . "/broker/greep-notification/" . env("APP_STATE")
        );
        $this->notificationNetwork = new NetworkHandler(
            "",
            $this->serviceUrl,
            $useCache,
            $headers,
            $apiType
        );
    }

    // Device Tokens

    public function registerDeviceToken($request)
    {
        return $this->notificationNetwork->post("/v1/device-tokens", $request->all());
    }

    public function updateDeviceToken($request)
    {
        return $this->notificationNetwork->put("/v1/device-tokens", $request->all());
    }

    public function deleteDeviceToken($request)
    {
        return $this->notificationNetwork->delete("/v1/device-tokens", $request->all());
    }

    // Notifications

    public function sendNotification($request)
    {
        return $this->notificationNetwork->post("/v1/notifications", $request->all());
    }

    public function deleteNotification($request)
    {
        return $this->notificationNetwork->delete("/v1/notifications", $request->all());
    }

    public function updateNotificationStatus($request)
    {
        return $this->notificationNetwork->put("/v1/notifications/status", $request->all());
    }

    public function broadcastNotification($request)
    {
        return $this->notificationNetwork->post("/v1/notifications/broadcast", $request->all());
    }

    // Notification Templates

    public function createNotificationTemplate($request)
    {
        return $this->notificationNetwork->post("/v1/notification-templates", $request->all());
    }

    public function updateNotificationTemplate($request)
    {
        return $this->notificationNetwork->put("/v1/notification-templates", $request->all());
    }

    public function deleteNotificationTemplate($request)
    {
        return $this->notificationNetwork->delete("/v1/notification-templates", $request->all());
    }
}
