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
     * Get paginated transactions (a mix of normal and point transactions).
     *
     * @param  null  $_
     * @param  array  $args
     * @return array
     */
    public function getTransactions($_, array $args)
    {
        $user = Auth::user();
        if (!$user) {
            throw new GraphQLException('User is not authenticated.');
        }

        // Set default pagination parameters
        $page    = isset($args['page']) ? (int)$args['page'] : 1;
        $perPage = isset($args['perPage']) ? (int)$args['perPage'] : 20;
        $type    = strtoupper($args['type'] ?? 'BOTH'); // NORMAL, POINT, or BOTH

        if ($type === 'NORMAL') {
            $query = Transaction::where('user_id', $user->id)
                ->orderBy('created_at', 'desc');
            $transactions = $query->skip(($page - 1) * $perPage)
                                  ->take($perPage)
                                  ->get();
            $total = $query->count();
        } elseif ($type === 'POINT') {
            $query = PointTransaction::where('user_id', $user->id)
                ->orderBy('created_at', 'desc');
            $transactions = $query->skip(($page - 1) * $perPage)
                                  ->take($perPage)
                                  ->get();
            $total = $query->count();
        } else {
            // For BOTH, get transactions from both models and merge them
            $normalTransactions = Transaction::where('user_id', $user->id)->get();
            $pointTransactions  = PointTransaction::where('user_id', $user->id)->get();

            // Merge and sort by created_at descending
            $combined = $normalTransactions->merge($pointTransactions)
                ->sortByDesc(function ($item) {
                    return strtotime($item->created_at);
                })
                ->values();

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

    /**
     * Get the current exchange rate from the wallet service.
     *
     * @param  null  $_
     * @param  array  $args  Expected keys: from_currency, to_currency
     * @return array
     */
    public function getExchangeRate($_, array $args)
    {
        if (empty($args['from_currency']) || empty($args['to_currency'])) {
            throw new GraphQLException('Both from_currency and to_currency are required.');
        }
        
        $fromCurrency = $args['from_currency'];
        $toCurrency   = $args['to_currency'];
        
        $walletService = new WalletService();
        $result = $walletService->getExchangeRate($fromCurrency, $toCurrency);
        
        return [
            'success'       => true,
            'rate'          => $result['rate'] ?? null,
            'from_currency' => $fromCurrency,
            'to_currency'   => $toCurrency,
            'message'       => 'Exchange rate retrieved successfully.',
        ];
    }
}
