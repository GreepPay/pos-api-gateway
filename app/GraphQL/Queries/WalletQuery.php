<?php

namespace App\GraphQL\Queries;

use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;

final class WalletQuery
{
    protected $walletService;

    public function __construct()
    {
        $this->walletService = new WalletService();
    }

    /**
     * Get exchange rate for a given currency.
     *
     * @param  mixed  $_
     * @param  array  $args
     *
     * @return float
     */
    public function getExchangeRate($_, array $args): float
    {
        return $this->walletService->getExchangeRates(
            $args["to_currency"]
        )["data"];
    }

    /**
     * Get list of supported on-ramp currencies.
     *
     * @param  mixed  $_
     * @param  array  $args
     *
     * @return array
     */
    public function getOffRampCurrencies($_, array $args): array
    {
        return $this->walletService->getOffRampSupportedCountries()["data"];
    }

    /**
     * Get global exchange rate for a given currency pair.
     *
     * @param  mixed  $_
     * @param  array  $args
     *
     * @return float
     */
    public function getGlobalExchangeRate($_, array $args): mixed
    {
        return $this->walletService->getGlobalExchangeRate(
            $args["base"],
            $args["target"]
        )["data"];
    }

    public function getFinancialSummary($_, array $args): mixed
    {
        $fromDate = isset($args["from"]) ? $args["from"] : null;
        $toDate = isset($args["to"]) ? $args["to"] : null;

        $type = isset($args["type"]) ? $args["type"] : "normal";

        $authUser = Auth::user();

        $response = [
            "credit" => 0,
            "debit" => 0,
        ];

        if ($type == "normal") {
            // For credit transactions
            $creditTransactions = $authUser
                ->transactions()
                ->where("dr_or_cr", "credit");

            if ($fromDate && $toDate) {
                $creditTransactions = $creditTransactions->whereBetween(
                    "created_at",
                    [$fromDate, $toDate]
                );
            }

            $response["credit"] = $creditTransactions->sum("amount");

            // For debit transactions
            $debitTransactions = $authUser
                ->transactions()
                ->where("dr_or_cr", "debit");

            if ($fromDate && $toDate) {
                $debitTransactions = $debitTransactions->whereBetween(
                    "created_at",
                    [$fromDate, $toDate]
                );
            }

            $response["debit"] = $debitTransactions->sum("amount");
        } elseif ($type == "point") {
            // For credit transactions
            $creditTransactions = $authUser
                ->pointTransactions()
                ->where("dr_or_cr", "credit");

            if ($fromDate && $toDate) {
                $creditTransactions = $creditTransactions->whereBetween(
                    "created_at",
                    [$fromDate, $toDate]
                );
            }

            $response["credit"] = $creditTransactions->sum("amount");

            // For debit transactions
            $debitTransactions = $authUser
                ->pointTransactions()
                ->where("dr_or_cr", "debit");

            if ($fromDate && $toDate) {
                $debitTransactions = $debitTransactions->whereBetween(
                    "created_at",
                    [$fromDate, $toDate]
                );
            }

            $response["debit"] = $debitTransactions->sum("amount");
        }

        return $response;
    }
}
