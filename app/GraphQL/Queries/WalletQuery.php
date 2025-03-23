<?php

namespace App\GraphQL\Queries;

use App\Exceptions\GraphQLException;
use App\Models\Wallet\PointTransaction;
use App\Models\Wallet\Transaction;
use App\Models\Wallet\Wallet;
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

    /**
     * Retrieve the transactions(Normal, Points, or both) for the authenticated user.
     *
     * @param  mixed  $_
     * @param  array  $args
     * @return array
     */
    public function getTransactions($_, array $args)
    {
        $user = Auth::user();
        if (!$user) {
            return [
                'success' => false,
                'transactions' => [],
                'total' => 0,
                'page' => $args['page'] ?? 1,
                'perPage' => $args['perPage'] ?? 20,
                'message' => 'User is not authenticated.'
            ];
        }

        // Get type, page, and perPage values from arguments (default: BOTH)
        $type    = strtoupper($args['type'] ?? 'BOTH'); // NORMAL, POINT, or BOTH
        $page    = $args['page'] ?? 1;
        $perPage = $args['perPage'] ?? 20;

        if ($type === 'NORMAL') {
            $query = Transaction::where('user_id', $user->id)
                ->orderBy('created_at', 'desc');
            $transactions = $query->skip(($page - 1) * $perPage)->take($perPage)->get();
            $total = $query->count();
        } elseif ($type === 'POINT') {
            $query = PointTransaction::where('user_id', $user->id)
                ->orderBy('created_at', 'desc');
            $transactions = $query->skip(($page - 1) * $perPage)->take($perPage)->get();
            $total = $query->count();
        } else {
            $normalTransactions = Transaction::where('user_id', $user->id)->get();
            $pointTransactions  = PointTransaction::where('user_id', $user->id)->get();

            $combined = $normalTransactions->merge($pointTransactions)
                ->sortByDesc(function ($item) {
                    return strtotime($item->created_at);
                });

            $total = $combined->count();
            $transactions = $combined->slice(($page - 1) * $perPage, $perPage)->values();
        }

        return [
            'success'      => true,
            'transactions' => $transactions,
            'total'        => $total,
            'page'         => $page,
            'perPage'      => $perPage,
            'message'      => 'Transactions retrieved successfully.'
        ];
    }
}
