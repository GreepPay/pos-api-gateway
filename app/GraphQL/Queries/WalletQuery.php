<?php

namespace App\GraphQL\Queries;

use App\Services\OfframpService;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;

final class WalletQuery
{
    protected $walletService;
    protected $offrampService;

    public function __construct()
    {
        $this->walletService = new WalletService();
        $this->offrampService = new OfframpService();
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
     * Get yellowcard networks by country
     *
     * @param  mixed  $_
     * @param  array  $args
     *
     * @return array
     */
    public function getYellowCardNetwork($_, array $args): array
    {
        $allNetworks = $this->walletService->getOffRampNetworkByCountryCode(
            $args["country_code"]
        );

        $allNetworks = $allNetworks["data"]["networks"];

        $finalNetworks = [];

        foreach ($allNetworks as $network) {
            if ($network["status"] == "active") {
                if (!is_string($network["code"])) {
                    $network["code"] = json_encode($network["code"]);
                    $network["hasBranch"] = true;
                } else {
                    $network["hasBranch"] = false;
                }
                $finalNetworks[] = $network;
            }
        }

        return $finalNetworks;
    }

    /**
     * Resolve bank account details
     *
     * @param  mixed  $_
     * @param  array  $args
     *
     * @return string
     */
    public function getBankAccountDetails($_, array $args): string
    {
        $accountDetails = $this->walletService->resolveBankAccount([
            "accountNumber" => $args["accountNumber"],
            "networkId" => $args["networkId"],
        ]);

        $allNetworks = $accountDetails["data"];

        return $allNetworks["accountName"];
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

    public function getWithdrawInfo($_, array $args): mixed
    {
        $amount = $args["amount"];
        $currency = $args["currency"] ?? null;

        $supportedCurrencies = [
            "TRY",
            "USD",
            "EUR",
            "USDC",
            "EURC",
            "USDT",
            "BTC",
            "ETH",
            "NGN",
            "GHS",
            "KES",
            "ZAR",
        ];

        if ($currency && !in_array($currency, $supportedCurrencies)) {
            throw new GraphQLException("Unsupported currency.");
        }

        $withdrawInfo = [];

        // Below is a sample withdraw info for demonstration purposes
        // $withdrawInfo = [
        //     "methods" => [
        //         "name" => "Bank Transfer",
        //         "description" => "Transfer funds to your bank account",
        //         "fee" => "0.0",
        //         "min_amount" => 10.0,
        //         "max_amount" => 10000.0,
        //     ],
        //    "currency" => "USD",
        // ];

        if ($currency == "TRY") {
            $withdrawInfo = [
                "methods" => [
                    [
                        "name" => "P2P Exchange",
                        "description" =>
                            "Pair to pair exchange via bank transfer and cash delivery",
                        "fee" => "0.0",
                        "min_amount" => 50.0,
                        "max_amount" => 1000000.0,
                        "unique_id" => "try_p2p",
                    ],
                ],
                "currency" => "TRY",
            ];
        } elseif ($currency == "USD") {
            $withdrawInfo = [
                "methods" => [
                    [
                        "name" => "Bank Transfer (ACH)",
                        "description" =>
                            "Transfer funds to your US bank account",
                        "fee" => "0.0",
                        "min_amount" => 5.0,
                        "max_amount" => 10000.0,
                        "unique_id" => "usd_ach",
                    ],
                    [
                        "name" => "Bank Transfer (Wire)",
                        "description" =>
                            "Transfer funds to your bank account via Wire transfer",
                        "fee" => "0.0",
                        "min_amount" => 50.0,
                        "max_amount" => 50000.0,
                        "unique_id" => "usd_wire",
                    ],
                ],
                "currency" => "USD",
            ];
        } elseif ($currency == "EUR") {
            $sep31Info = $this->offrampService->getSep31Info([
                "slug" => "mykobo",
            ]);

            $sep31Info = $sep31Info["data"];

            $availableChoices =
                $sep31Info["receive"]["EURC"]["fields"]["transaction"]["type"][
                    "choices"
                ];

            $feePercentage = $sep31Info["receive"]["EURC"]["fee_percent"];

            $withdrawInfo = [
                "methods" => [],
                "currency" => "EUR",
            ];

            foreach ($availableChoices as $choice) {
                $withdrawInfo["methods"][] = [
                    "name" => $choice,
                    "description" => "Withdraw EUR via " . $choice,
                    "fee" => $feePercentage,
                    "min_amount" => 5.0,
                    "max_amount" => 10000.0,
                    "unique_id" => $choice,
                ];
            }
        } elseif ($currency == "USDC") {
            $withdrawInfo = [
                "methods" => [
                    [
                        "name" => "Stellar",
                        "description" => "Withdraw USDC via Stellar network",
                        "fee" => "0.0",
                        "min_amount" => 5.0,
                        "max_amount" => 10000.0,
                        "unique_id" => "usdc_stellar",
                    ],
                    [
                        "name" => "ETH",
                        "description" => "Withdraw USDC via Ethereum network",
                        "fee" => "0.0",
                        "min_amount" => 5.0,
                        "max_amount" => 10000.0,
                        "unique_id" => "usdc_eth",
                    ],
                    [
                        "name" => "Polygon",
                        "description" => "Withdraw USDC via Polygon network",
                        "fee" => "0.0",
                        "min_amount" => 5.0,
                        "max_amount" => 10000.0,
                        "unique_id" => "usdc_polygon",
                    ],
                    [
                        "name" => "Base",
                        "description" => "Withdraw USDC via Base network",
                        "fee" => "0.0",
                        "min_amount" => 5.0,
                        "max_amount" => 10000.0,
                        "unique_id" => "usdc_base",
                    ],
                    [
                        "name" => "Arbitrum",
                        "description" => "Withdraw USDC via Arbitrum network",
                        "fee" => "0.5",
                        "min_amount" => 5.0,
                        "max_amount" => 10000.0,
                        "unique_id" => "usdc_arbitrum",
                    ],
                    [
                        "name" => "Avalanche",
                        "description" => "Withdraw USDC via Avalanche network",
                        "fee" => "0.0",
                        "min_amount" => 5.0,
                        "max_amount" => 10000.0,
                        "unique_id" => "usdc_avalanche",
                    ],
                    [
                        "name" => "Optimism",
                        "description" => "Withdraw USDC via Optimism network",
                        "fee" => "0.0",
                        "min_amount" => 5.0,
                        "max_amount" => 10000.0,
                        "unique_id" => "usdc_optimism",
                    ],
                    [
                        "name" => "Solana",
                        "description" => "Withdraw USDC via Solana network",
                        "fee" => "0.0",
                        "min_amount" => 5.0,
                        "max_amount" => 10000.0,
                        "unique_id" => "usdc_solana",
                    ],
                ],
                "currency" => "USDC",
            ];
        } elseif ($currency == "USDT") {
            $withdrawInfo = [
                "methods" => [
                    [
                        "name" => "ETH",
                        "description" => "Withdraw USDT via Ethereum network",
                        "fee" => "0.0",
                        "min_amount" => 5.0,
                        "max_amount" => 10000.0,
                        "unique_id" => "usdt_eth",
                    ],
                    [
                        "name" => "Tron",
                        "description" => "Withdraw USDT via Tron network",
                        "fee" => "0.0",
                        "min_amount" => 5.0,
                        "max_amount" => 10000.0,
                        "unique_id" => "usdt_tron",
                    ],
                ],
                "currency" => "USDT",
            ];
        } elseif ($currency == "EURC") {
            $withdrawInfo = [
                "methods" => [
                    [
                        "name" => "Solana",
                        "description" => "Withdraw EURC via Solana network",
                        "fee" => "0.0",
                        "min_amount" => 5.0,
                        "max_amount" => 10000.0,
                        "unique_id" => "eurc_solana",
                    ],
                ],
                "currency" => "EURC",
            ];
        } elseif ($currency == "BTC") {
            $withdrawInfo = [
                "methods" => [
                    [
                        "name" => "Bitcoin Network",
                        "description" => "Withdraw BTC via Bitcoin network",
                        "fee" => "0.0",
                        "min_amount" => 0.00004,
                        "max_amount" => 5.0,
                        "unique_id" => "btc",
                    ],
                ],
                "currency" => "BTC",
            ];
        } elseif ($currency == "ETH") {
            $withdrawInfo = [
                "methods" => [
                    [
                        "name" => "Ethereum Network",
                        "description" => "Withdraw ETH via Ethereum network",
                        "fee" => "0.0",
                        "min_amount" => 0.0005,
                        "max_amount" => 10.0,
                        "unique_id" => "eth",
                    ],
                ],
                "currency" => "ETH",
            ];
        } elseif (
            $currency == "NGN" ||
            $currency == "GHS" ||
            $currency == "KES" ||
            $currency == "ZAR"
        ) {
            $currencyCountryMap = [
                "NGN" => "NG",
                "GHS" => "GH",
                "KES" => "KE",
                "ZAR" => "ZA",
            ];

            $channels = $this->walletService->getOffRampChannelsByCountryCode(
                $currencyCountryMap[$currency]
            );

            $channels = $channels["data"]["channels"];

            $withdrawalChannels = array_filter($channels, function ($channel) {
                return $channel["rampType"] == "withdraw";
            });

            $withdrawalChannels = array_values($withdrawalChannels);

            $withdrawInfo = [
                "methods" => [],
                "currency" => $currency,
            ];

            foreach ($withdrawalChannels as $channel) {
                $channelName =
                    $channel["channelType"] == "p2p"
                        ? "Bank Transfer"
                        : $channel["channelType"];

                if ($channelName == "momo") {
                    $channelName = "Mobile Money";
                }

                if ($channelName == "bank") {
                    $channelName = "Bank Transfer";
                }

                if ($channelName == "eft") {
                    $channelName = "Electronic Funds Transfer";
                }

                $channelExists = false;

                foreach ($withdrawInfo["methods"] as $method) {
                    if ($method["name"] == $channelName) {
                        $channelExists = true;
                        break;
                    }
                }

                if (!$channelExists) {
                    $withdrawInfo["methods"][] = [
                        "name" => $channelName,
                        "description" =>
                            "Withdraw " . $currency . " via " . $channelName,
                        "fee" => "0.0",
                        "min_amount" => $channel["min"],
                        "max_amount" => $channel["max"],
                        "unique_id" => $channel["id"],
                    ];
                }
            }
        }

        return $withdrawInfo;
    }
}
