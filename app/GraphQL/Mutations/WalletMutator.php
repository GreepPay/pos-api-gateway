<?php

namespace App\GraphQL\Mutations;
use App\Exceptions\GraphQLException;
use App\Models\Wallet\OffRamp;
use App\Models\Wallet\UserBank;
use App\Services\BlockchainService;
use App\Services\OfframpService;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Auth\User;

final class WalletMutator
{
    protected WalletService $walletService;
    protected BlockchainService $blockchainService;
    protected OfframpService $offrampService;

    public function __construct()
    {
        $this->walletService = new WalletService();
        $this->blockchainService = new BlockchainService();
        $this->offrampService = new OfframpService();
    }

    /**
     * Redeems GRP tokens for the authenticated user.
     *
     * @param  mixed  $_ The parent resolver.
     * @param  array  $args The arguments passed to the mutation.
     * @return bool True if the redemption was successful (not implemented yet).
     */
    public function redeemGRPToken($_, array $args): bool
    {
        $authUser = Auth::user();

        // TODO: Implement redeemGRPToken method
        return false;
    }

    /**
     * Initiates a withdrawal for the authenticated user.
     *
     * @param  mixed  $_ The parent resolver.
     * @param  array  $args The arguments passed to the mutation.
     * @return array True if the withdrawal was initiated successfully (not implemented yet).
     */
    public function initiateWithdrawal($_, array $args): mixed
    {
        $authUser = Auth::user();

        return Cache::lock("payment_" . $authUser->uuid)->get(function () use (
            $authUser,
            $args
        ) {
            $yellowCardCurrencies = ["NGN", "GHS", "KES", "ZAR"];

            $currency = $args["withdrawal_currency"];

            $userWallet = $authUser->wallet;

            $amountInUSD = 0;

            if ($currency != "USD") {
                $globalExchangeRate = $this->walletService->getGlobalExchangeRate(
                    "USD",
                    $currency
                );

                $globalExchangeRate = $globalExchangeRate["data"];

                $midRate = $globalExchangeRate["mid"];

                $amountInUSD = (float) $args["amount"] / $midRate;
            } else {
                $amountInUSD = (float) $args["amount"];
            }

            $walletBalance = (float) $userWallet->total_balance;

            // Check if user can afford it

            if ($amountInUSD > $walletBalance) {
                throw new GraphQLException("Insufficient funds");
            }

            if (in_array($currency, $yellowCardCurrencies)) {
                $savedAccount = UserBank::query()
                    ->where("uuid", $args["saved_account_uuid"])
                    ->first();

                if (!$savedAccount) {
                    throw new GraphQLException("Saved account not found");
                }

                $withdrawaReference = Str::random(16);

                $accountMetadata = json_decode($savedAccount->meta_data, true);

                $accountType = "bank";

                if ($savedAccount->bank_code == "bank_account") {
                    $accountType = "bank";
                } else {
                    $accountType = "momo";
                }

                $requestData = [
                    "channelId" => $accountMetadata["channel_id"],
                    "sequenceId" => $withdrawaReference,
                    "localAmount" => $args["amount"],
                    "currency" => $currency,
                    "reason" => "other",
                    "extra_data" => json_encode([
                        "account" => $accountMetadata,
                    ]),
                    "customerType" => "institution",
                    "destination" => [
                        "accountNumber" => isset(
                            $accountMetadata["account_number"]
                        )
                            ? $accountMetadata["account_number"]
                            : $accountMetadata["mobile_number"],
                        "accountType" => $accountType,
                        "networkId" => isset($accountMetadata["network_id"])
                            ? $accountMetadata["network_id"]
                            : "",
                        "accountName" => isset(
                            $accountMetadata["account_holder_name"]
                        )
                            ? $accountMetadata["account_holder_name"]
                            : "",
                        "phoneNumber" => isset(
                            $accountMetadata["mobile_number"]
                        )
                            ? $accountMetadata["mobile_number"]
                            : "",
                        "networkName" => isset($accountMetadata["provider"])
                            ? $accountMetadata["provider"]
                            : "",
                    ],
                    "forceAccept" => env("APP_STATE") == "dev",
                ];

                $offrampResponse = $this->walletService->createPaymentSettlement(
                    $requestData,
                    $userWallet->id,
                    $authUser->id
                );

                return OffRamp::query()
                    ->where("uuid", $offrampResponse["data"]["id"])
                    ->first();
            }

            return null;
        });
    }

    public function initiateWalletKYC($_, array $args)
    {
        $authUser = Auth::user();

        $wallet = $authUser->wallet;

        $currency = $args["currency"];

        if ($currency == "EUR") {
            // Get user kyc details from anchor
            $accountSecret = $this->blockchainService->getAccountSecret(
                $wallet->blockchain_account_id
            )["data"];

            $userKYCStatus = $this->offrampService->getKycStatus([
                "slug" => "mykobo",
                "account" => $accountSecret,
            ]);

            $userKYCStatus["provider"] = "mykobo";

            return json_encode($userKYCStatus);
        }

        $bridgeCurrencies = [
            "USD",
            "USDC",
            "XLM",
            "EURC",
            "USDT",
            "BTC",
            "ETH",
        ];

        if (in_array($currency, $bridgeCurrencies)) {
            $kycLinkResponse = $this->walletService->createBridgeKyc(
                $wallet->id,
                [
                    "full_name" => $authUser->profile->business->business_name,
                    "email" => $authUser->email,
                    "type" => "business",
                ],
                "create_kyc_links_{$wallet->id}"
            );

            $kycLinkResponse = $kycLinkResponse["data"];

            $kycLinkResponse["provider"] = "bridge";

            return json_encode($kycLinkResponse);
        }

        return null;
    }

    public function confirmWithdrawal($_, array $args)
    {
        $authUser = Auth::user();

        return Cache::lock("payment_" . $authUser->uuid)->get(function () use (
            $authUser,
            $args
        ) {
            $bridgeCurrencies = [
                "USD",
                "USDC",
                "XLM",
                "EURC",
                "USDT",
                "BTC",
                "ETH",
            ];

            $yellowCardCurrencies = ["NGN", "GHS", "KES", "ZAR"];

            $offramp = null;

            $userWallet = $authUser->wallet;

            $currency = $args["currency"];

            $amountInUSD = 0;

            if ($currency == "USDC" || $currency == "USDT") {
                $currency = "USD";
            }

            if ($currency == "EURC") {
                $currency = "EUR";
            }

            if ($currency != "USD") {
                $globalExchangeRate = $this->walletService->getGlobalExchangeRate(
                    "USD",
                    $currency
                );

                $globalExchangeRate = $globalExchangeRate["data"];

                $midRate = $globalExchangeRate["mid"];

                $amountInUSD = (float) $args["amount"] / $midRate;
            } else {
                $amountInUSD = (float) $args["amount"];
            }

            $walletBalance = (float) $userWallet->total_balance;

            // Check if user can afford it

            if ($amountInUSD > $walletBalance) {
                throw new GraphQLException("Insufficient funds");
            }

            $amount = $args["amount"];

            $chargesPercent = 0.01;

            $transactionReference = Str::random(16);

            $transactionStatus = "pending";

            $gateway = "yellow_card";

            if (in_array($args["currency"], $yellowCardCurrencies)) {
                $offramp = OffRamp::query()
                    ->where("uuid", $args["uuid"])
                    ->first();

                if (!$offramp) {
                    throw new GraphqlException("Offramp not found");
                }

                if (env("APP_STATE") != "dev") {
                    $offrampResponse = $this->walletService->acceptPaymentSettlement(
                        $offramp->uuid
                    );
                }

                $yellowCardPayment = $offramp->yellowCardPayment();

                if ($yellowCardPayment) {
                    $amount = $yellowCardPayment["amount"];
                }
            } elseif (in_array($args["currency"], $bridgeCurrencies)) {
                if ($args["uuid"] != "pre_confirm") {
                    throw new GraphqlException("Offramp not found");
                }

                if (!isset($args["metadata"])) {
                    throw new GraphqlException("Payment metadata not found");
                }

                $gateway = "bridge";
                $transactionStatus = "created";

                $chargesPercent = 0.005;

                $metadata = json_decode($args["metadata"], true);

                $destination = [
                    "currency" => strtolower($args["currency"]),
                    "payment_rail" => str_replace(
                        strtolower($args["currency"]) . "_",
                        "",
                        $metadata["channel_id"]
                    ),
                ];

                if (isset($metadata["wallet_address"])) {
                    $destination["to_address"] = $metadata["wallet_address"];
                } elseif (isset($metadata["account_number"])) {
                    $savedAccount = UserBank::query()
                        ->where("uuid", $metadata["saved_account_uuid"])
                        ->first();

                    if (!$savedAccount) {
                        throw new GraphQLException("Saved account not found");
                    }

                    $accountMetadata = json_decode(
                        $savedAccount->meta_data,
                        true
                    );

                    $destination["external_account_id"] =
                        $metadata["bridge_account_id"];
                }

                $requestData = [
                    "amount" => (string) $amount,
                    "developer_fee" => (string) round(
                        $amount * $chargesPercent,
                        2
                    ),
                    "on_behalf_of" => $userWallet->bridge_customer_id,
                    "source" => [
                        "currency" => "usdc",
                        "payment_rail" => "stellar",
                        "from_address" =>
                            $userWallet->blockchain_account->stellar_address,
                    ],
                    "destination" => $destination,
                    "extra_data" => $args["metadata"],
                ];

                $randomKey = Str::random(10);

                $initiatedTransferResponse = $this->walletService->createBridgeTransfer(
                    $requestData,
                    $userWallet->id,
                    $authUser->id,
                    "wallet_transfer_{$userWallet->id}_{$randomKey}"
                );

                $initiatedTransferResponse = $initiatedTransferResponse["data"];

                $offramp = OffRamp::query()
                    ->where("uuid", $initiatedTransferResponse["id"])
                    ->first();
            }

            // Debit user wallet
            // We debit the user's wallet
            $senderTransaction = $this->sendWalletTransaction(
                $userWallet,
                [
                    "type" => "debit",
                    "amount" => $amountInUSD,
                    "description" => "Fund Withdrawal",
                    "status" => $transactionStatus,
                    "extra_data" => $offramp->extra_data,
                    "charges" => $amountInUSD * $chargesPercent,
                    "reference" => $transactionReference,
                    "gateway" => $gateway,
                    "currency" => "USDC",
                ],
                "offramp",
                $offramp->uuid,
                "graphql"
            );

            return $offramp;
        });
    }

    /**
     * Creates a saved account for the authenticated user.
     *
     * @param  mixed  $_ The parent resolver.
     * @param  array  $args The arguments passed to the mutation.
     * @return UserBank The created saved account.
     */
    public function createSavedAccount($_, array $args): UserBank
    {
        $authUser = Auth::user();

        $userWallet = $authUser->wallet;

        $metadata = json_decode($args["metadata"], true);

        $randomKey = Str::random(10);

        if ($args["type"] == "bank_account") {
            if ($metadata["country"] == "US") {
                // Save account as external account on Bridge
                $externalAccount = $this->walletService->createBridgeCustomerExternalAccount(
                    $userWallet->bridge_customer_id,
                    [
                        "type" => "raw",
                        "bank_name" => $metadata["bank_name"],
                        "account_number" => $metadata["account_number"],
                        "routing_number" => $metadata["routing_number"],
                        "account_owner_name" =>
                            $metadata["account_holder_name"],
                        "active" => true,
                        "address" => $metadata["address"],
                    ],
                    "external_account_{$userWallet->id}_{$randomKey}"
                );

                $externalAccount = $externalAccount["data"];

                $metadata["bridge_account_id"] = $externalAccount["id"];

                $args["metadata"] = json_encode($metadata);
            } elseif ($metadata["country"] == "EU") {
                // Get user kyc details from anchor
                $accountSecret = $this->blockchainService->getAccountSecret(
                    $userWallet->blockchain_account_id
                )["data"];

                $myKoboKyc = $metadata["mykobo_kyc"];

                $uploads = $args["uploads"];

                $multipart = [
                    ["name" => "slug", "contents" => "mykobo"],
                    ["name" => "account", "contents" => $accountSecret],
                    ["name" => "customer_type", "contents" => "sep31-receiver"],
                    ["name" => "memo", "contents" => $userWallet->id],
                ];

                // Add fields[0].0, fields[0].1, etc.
                $fields = [
                    "first_name" => $myKoboKyc["first_name"],
                    "last_name" => $myKoboKyc["last_name"],
                    "email_address" => $myKoboKyc["email_address"],
                    "mobile_number" => $myKoboKyc["mobile_number_full"],
                    "id_country_code" => $myKoboKyc["id_country_code"],
                    "bank_account_number" => $metadata["account_number"],
                ];

                $index = 0;
                foreach ($fields as $key => $value) {
                    $multipart[] = [
                        "name" => "fields[$index].0",
                        "contents" => $key,
                    ];
                    $multipart[] = [
                        "name" => "fields[$index].1",
                        "contents" => $value,
                    ];
                    $index++;
                }

                // Add files[0].0 = name, files[0].1 = file
                $files = [
                    "photo_id_front" => $uploads[$myKoboKyc["photo_id_front"]],
                    "photo_proof_residence" =>
                        $uploads[$myKoboKyc["photo_proof_residence"]],
                    "proof_of_liveness" =>
                        $uploads[$myKoboKyc["proof_of_liveness"]],
                ];

                $i = 0;
                foreach ($files as $key => $path) {
                    $multipart[] = [
                        "name" => "files[$i].0",
                        "contents" => $key,
                    ];
                    $multipart[] = [
                        "name" => "files[$i].1",
                        "contents" => fopen($path, "r"),
                        "filename" => basename($path),
                    ];
                    $i++;
                }

                $newCustomer = $this->offrampService->createKyc($multipart);

                $newCustomer = $newCustomer["data"];

                $metadata["mykobo_customer_id"] = $newCustomer["id"];

                $args["metadata"] = json_encode($metadata);
            }
        }

        $newAccount = $this->walletService->createSavedBankAccount([
            "user_id" => $authUser->id,
            "wallet_id" => $userWallet->id,
            "bank_code" => $args["type"],
            "bank_name" => "placeholder",
            "account_no" => $args["unique_id"],
            "meta_data" => $args["metadata"],
            "currency" => "base",
        ]);

        $newAccount = $newAccount["data"];

        return UserBank::query()->where("id", $newAccount["id"])->first();
    }

    /**
     * Removes a saved account for the authenticated user.
     *
     * @param  mixed  $_ The parent resolver.
     * @param  array  $args The arguments passed to the mutation.
     * @return bool True if the saved account was removed successfully.
     */
    public function removeSavedAccount($_, array $args): bool
    {
        $authUser = Auth::user();

        $savedAccount = UserBank::query()
            ->where("uuid", $args["saved_account_uuid"])
            ->first();

        if (!$savedAccount) {
            throw new GraphQLException("Saved account not found");
        }

        $newAccount = $this->walletService->softDeleteSavedBankAccount(
            $savedAccount->id
        );

        return true;
    }

    /**
     * Creates and sends a wallet transaction.
     *
     * @param object $userWallet The user's wallet object
     * @param array $transactionData Transaction details including type, amount, currency etc
     * @param string $chargeableType The type of charge, defaults to "wallet"
     * @param string|null $chargeId The ID of the charge, defaults to wallet ID
     * @param string $initiator The initiator of the transaction, defaults to "graphql"
     * @return array The created transaction data
     */
    public function sendWalletTransaction(
        $userWallet,
        $transactionData,
        $chargeableType = "wallet",
        $chargeId = null,
        $initiator = "graphql"
    ): array {
        $request = [
            "user_id" => $userWallet->user_id,
            "wallet_id" => $userWallet->id,
            "dr_or_cr" => $transactionData["type"],
            "amount" => $transactionData["amount"],
            "charge_id" => $chargeId ? $chargeId : $userWallet->id,
            "chargeable_type" => $chargeableType,
            "currency" => $transactionData["currency"],
            "description" => $transactionData["description"],
            "status" => isset($transactionData["status"])
                ? $transactionData["status"]
                : "successful",
            "extra_data" => isset($transactionData["extra_data"])
                ? $transactionData["extra_data"]
                : null,
            "charges" => isset($transactionData["charges"])
                ? $transactionData["charges"]
                : 0,
            "reference" => isset($transactionData["reference"])
                ? $transactionData["reference"]
                : Str::random(),
            "gateway" => isset($transactionData["gateway"])
                ? $transactionData["gateway"]
                : "greep-wallet",
        ];

        $walletService = new WalletService(false, [], $initiator);

        $transaction = $walletService->createTransaction($request);

        if (
            !isset($transactionData["notify_user"]) ||
            $transactionData["notify_user"]
        ) {
            // send notification async
            $user = User::where("id", $userWallet->user_id)->first();

            // dispatch_now(new AsyncRequest([
            //     'params' => [[
            //         "user" => $user,
            //         "transaction" => $transaction['data'],
            //     ]],
            //     'method' => 'newTransaction',
            //     'service' => 'NotificationService',
            // ]));
        }

        return $transaction["data"];
    }

    /**
     * Creates and sends a point transaction.
     *
     * @param object $userWallet The user's wallet object
     * @param array $transactionData Transaction details including type, amount, currency etc
     * @param string $chargeableType The type of charge, defaults to "wallet"
     * @param string|null $chargeId The ID of the charge, defaults to wallet ID
     * @param string $initiator The initiator of the transaction, defaults to "graphql"
     * @return array The created point transaction data
     */
    public function sendPointTransaction(
        $userWallet,
        $transactionData,
        $chargeableType = "wallet",
        $chargeId = null,
        $initiator = "graphql"
    ): array {
        $request = [
            "user_id" => $userWallet->user_id,
            "wallet_id" => $userWallet->id,
            "dr_or_cr" => $transactionData["type"],
            "amount" => $transactionData["amount"],
            "charge_id" => $chargeId ? $chargeId : $userWallet->id,
            "chargeable_type" => $chargeableType,
            "currency" => $transactionData["currency"],
            "description" => $transactionData["description"],
            "status" => isset($transactionData["status"])
                ? $transactionData["status"]
                : "successful",
            "extra_data" => isset($transactionData["extra_data"])
                ? $transactionData["extra_data"]
                : null,
            "charges" => isset($transactionData["charges"])
                ? $transactionData["charges"]
                : 0,
            "reference" => isset($transactionData["reference"])
                ? $transactionData["reference"]
                : Str::random(),
        ];

        $walletService = new WalletService(false, [], $initiator);

        $pointTransaction = $walletService->createPointTransaction($request);

        if (
            !isset($transactionData["notify_user"]) ||
            $transactionData["notify_user"]
        ) {
            // send notification aync
            $user = User::where("id", $userWallet->user_id)->first();

            // dispatch_now(
            //     new AsyncRequest([
            //         "params" => [
            //             [
            //                 "user" => $user,
            //                 "transaction" => $pointTransaction["data"],
            //             ],
            //         ],
            //         "method" => "newPointTransaction",
            //         "service" => "NotificationService",
            //     ])
            // );

            return $pointTransaction["data"];
        }
    }
}
