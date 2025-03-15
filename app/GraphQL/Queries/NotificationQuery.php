<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

final class NotificationQuery
{
    public function getNotification($_, array $args): ?Notification
    {
        $user = Auth::user();

        if (! $user || !isset($args['id'])) {
            return null;
        }

        return Notification::where('auth_user_id', $user->id)
            ->where('id', $args['id'])
            ->first();
    }

    public function getNotifications(): Collection
    {
        $user = Auth::user();

        if (! $user) {
            return collect();
        }

        return Notification::where('auth_user_id', $user->id)->get();
    }
}

