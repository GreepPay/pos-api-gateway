<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\UserProfile;
use Illuminate\Support\Facades\Auth;
use Log;

final class UserQuery
{
    public function userProfile()
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        return UserProfile::where('auth_user_id', $user->id)->first();
    }
}
