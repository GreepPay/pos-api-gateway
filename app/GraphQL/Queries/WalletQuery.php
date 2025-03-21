<?php

namespace App\GraphQL\Queries;

use App\Exceptions\GraphQLException;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;

final class WalletQuery
{
    /**
     * Retrieve the wallet details for the authenticated user.
     *
     * @param  mixed  $_
     * @param  array  $args
     * @return array
     * @throws GraphQLException
     */
    public function walletBalance($_, array $args)
    {
        $user = Auth::user();
        if (!$user) {
            throw new GraphQLException("Unauthenticated");
        }

        // Retrieve the wallet using the authenticated user's ID
        $wallet = Wallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            throw new GraphQLException("Wallet not found");
        }

        return [
            'success' => true,
            'wallet'  => $wallet,
            'message' => "Wallet retrieved successfully",
        ];
    }
}
