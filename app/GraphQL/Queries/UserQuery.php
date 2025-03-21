<?php declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Exceptions\GraphQLException;
use App\Models\User\UserProfile;
use Illuminate\Support\Facades\Auth;
use Log;

final class UserQuery
{
    public function userProfile()
    {
        $user = Auth::user();
        if (!$user) {
            throw new GraphQLException("Unauthenticated");
        }

        $profile = UserProfile::where('auth_user_id', $user->uuid)->first();
        if (!$profile) {
            throw new GraphQLException("User profile not found");
        }

        $profileData = null;
        switch ($profile->user_type) {
            case 'Business':
                $profileData = $profile->business;
                break;
            case 'Rider':
                $profileData = $profile->rider;
                break;
            case 'Customer':
                $profileData = $profile->customer;
                break;
            default:
                $profileData = null;
        }

        return [
            'user' => $user,
            'userProfile' => $profile,
            'profileData' => $profileData,
        ];
    }

}
