<?php

namespace App\GraphQL\Queries;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

final class AuthQuery
{
    /**
     * Get the authenticated user with wallet and profile info.
     *
     * @return array
     */
    public function getAuthUser()
    {
        $user = Auth::user();

        if (!$user) {
            return [
                'success' => false,
                'user'    => null,
                'wallet'  => null,
                'profile' => null,
                'message' => 'User is not authenticated.'
            ];
        }

        // $user->load('wallet', 'profile');

        return [
            'success' => true,
            'user'    => $user,
            'wallet'  => $user->wallet,
            'profile' => null,
            'message' => 'Authenticated user data retrieved successfully.'
        ];
    }
}
