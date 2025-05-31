<?php

namespace App\Services;

use App\Datasource\NetworkHandler;

class WalletService
{
    protected $serviceUrl;
    protected NetworkHandler $walletNetwork;

    /**
     * construct
     *
     * @param bool $useCache Whether to use cache
     * @param array $headers Headers to send with the request
     * @param string $apiType Type of API to use
     * @return mixed
     */
    public function __construct(
        bool $useCache = false,
        array $headers = [],
        string $apiType = "graphql"
    ) {
        $this->serviceUrl = env(
            "WALLET_API",
            env("SERVICE_BROKER_URL") .
                "/broker/greep-wallet/" .
                env("APP_STATE")
        );

        $this->walletNetwork = new NetworkHandler(
            "",
            $this->serviceUrl,
            $useCache,
            $headers,
            $apiType
        );
    }

    // Wallet

    /**
     * Create a wallet
     *
     * @param array $data
     * @return mixed
     */
    public function createWallet(array $data)
    {
        return $this->walletNetwork->post("/v1/wallets", $data);
    }

    // Saved Bank Account

    /**
     * Create a saved bank account
     *
     * @param array $data
     * @return mixed
     */
    public function createSavedBankAccount(array $data)
    {
        return $this->walletNetwork->post("/v1/user-banks", $data);
    }

    /**
     * Soft delete a saved bank account
     *
     * @param string $id
     * @return mixed
     */
    public function softDeleteSavedBankAccount(string $id)
    {
        return $this->walletNetwork->post("/v1/user-banks/{$id}/soft-delete");
    }

    // Settlement (offRamp)

    /**
     * Get OffRamp Supported Countries
     *
     * @return mixed
     */
    public function getOffRampSupportedCountries()
    {
        return $this->walletNetwork->get("/v1/offramp/supported-countries");
    }

    /**
     * Get channels by country code
     *
     * @param string $countryCode
     * @return mixed
     */
    public function getOffRampChannelsByCountryCode(string $countryCode)
    {
        return $this->walletNetwork->get("/v1/onramp/channels/{$countryCode}");
    }

    /**
     * Get network by country code
     *
     * @param string $countryCode
     * @return mixed
     */
    public function getOffRampNetworkByCountryCode(string $countryCode)
    {
        return $this->walletNetwork->get("/v1/onramp/networks/{$countryCode}");
    }

    /**
     * Get exchange rates
     *
     * @param string $toCurrency. Default from currency is USD
     * @return mixed
     */
    public function getExchangeRates(string $toCurrency)
    {
        return $this->walletNetwork->get(
            "/v1/onramp/exchange-rates/{$toCurrency}"
        );
    }

    /**
     * Get payment settlements
     *
     * @param int $id
     * @return mixed
     */
    public function getPaymentSettlement($id)
    {
        return $this->walletNetwork->get("/v1/offramp/{$id}");
    }

    /**
     * Create payment settlement
     *
     * @param array $data
     * @param int $wallet_id
     * @param int $user_id
     * @return mixed
     */
    public function createPaymentSettlement(array $data, $wallet_id, $user_id)
    {
        return $this->walletNetwork->post(
            "/v1/offramp/{$wallet_id}/{$user_id}",
            $data
        );
    }

    /**
     * Accept payment settlement
     * @param int $id
     * @return mixed
     */
    public function acceptPaymentSettlement($id)
    {
        return $this->walletNetwork->post(
            "/v1/offramp/accept/{$id}",
            [],
            false
        );
    }

    /**
     * Deny payment settlement
     * @param int $id
     * @return mixed
     */
    public function denyPaymentSettlement($id)
    {
        return $this->walletNetwork->post("/v1/offramp/deny/{$id}", []);
    }

    /**
     * Resolve bank account
     * @param array $data
     * @return mixed
     */
    public function resolveBankAccount(array $data)
    {
        return $this->walletNetwork->post(
            "/v1/offramp/resolve-bank-account",
            $data
        );
    }

    // Point transactions

    /**
     * Create point transaction
     * @param array $data
     * @return mixed
     */
    public function createPointTransaction(array $data)
    {
        return $this->walletNetwork->post("/v1/point-transactions", $data);
    }

    // Update point transaction status
    /**
     * Update point transaction status
     * @param int $id
     * @param string $status
     * @return mixed
     */
    public function updatePointTransactionStatus($id, $status)
    {
        return $this->walletNetwork->post(
            "/v1/point-transactions/{$id}/status",
            ["status" => $status]
        );
    }

    // Transaction (normal)

    /**
     * Create transaction
     * @param array $data
     * @return mixed
     */
    public function createTransaction(array $data)
    {
        return $this->walletNetwork->post("/v1/transactions", $data);
    }

    /**
     * Update transaction status
     * @param int $id
     * @param string $status
     * @return mixed
     */
    public function updateTransactionStatus($id, $status)
    {
        return $this->walletNetwork->post("/v1/transactions/{$id}/status", [
            "status" => $status,
        ]);
    }

    // Global exchange rate
    /**
     * Get global exchange rate
     * @param string $base
     * @param string $target
     * @return mixed
     */
    public function getGlobalExchangeRate(string $base, string $target)
    {
        return $this->walletNetwork->get(
            "/v1/global-exchange-rates?base={$base}&target={$target}"
        );
    }

    // Bridge API
    /**
     * Get bridge customers
     * @return mixed
     */
    public function getBridgeCustomers()
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get("/v1/bridge/customers", "", false);
    }

    /**
     * Create bridge customer
     * @param array $data
     * @return mixed
     */
    public function createBridgeCustomer(array $data, string $idempotencyKey)
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->post(
            "/{$appVersion}/bridge/customers?idempotencyKey={$idempotencyKey}",
            $data
        );
    }

    /**
     * Get bridge customer by ID
     * @param string $customerID
     * @return mixed
     */
    public function getBridgeCustomer(string $customerID)
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get(
            "/{$appVersion}/bridge/customers/{$customerID}",
            "",
            false
        );
    }

    /**
     * Update bridge customer by ID
     * @param string $customerID
     * @param array $data
     * @return mixed
     */
    public function updateBridgeCustomer(string $customerID, array $data)
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->put(
            "/{$appVersion}/bridge/customers/{$customerID}",
            $data
        );
    }

    /**
     * Delete bridge customer by ID
     * @param string $customerID
     * @return mixed
     */
    public function deleteBridgeCustomer(string $customerID)
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->delete(
            "/{$appVersion}/bridge/customers/{$customerID}"
        );
    }

    /**
     * Get bridge customer TOS
     * @param string $customerID
     * @return mixed
     */
    public function getBridgeCustomerTos(string $customerID)
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get(
            "/{$appVersion}/bridge/customers/{$customerID}/tos",
            "",
            false
        );
    }

    /**
     * Get bridge customer KYC
     * @param string $customerID
     * @return mixed
     */
    public function getBridgeCustomerKyc(string $customerID)
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get(
            "/{$appVersion}/bridge/customers/{$customerID}/kyc",
            "",
            false
        );
    }

    /**
     * Create bridge TOS
     * @param array $data
     * @return mixed
     */
    public function createBridgeTos(array $data, string $idempotencyKey)
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->post(
            "/{$appVersion}/bridge/tos?idempotencyKey={$idempotencyKey}",
            $data
        );
    }

    /**
     * Get bridge customer transfers
     * @param string $customerID
     * @return mixed
     */
    public function getBridgeCustomerTransfers(string $customerID)
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get(
            "/{$appVersion}/bridge/customers/{$customerID}/transfers",
            "",
            false
        );
    }

    /**
     * Create bridge transfer
     * @param array $data
     * @return mixed
     */
    public function createBridgeTransfer(
        array $data,
        $wallet_id,
        $user_id,
        string $idempotencyKey
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->post(
            "/{$appVersion}/bridge/transfers/{$wallet_id}/{$user_id}?idempotencyKey={$idempotencyKey}",
            $data
        );
    }

    /**
      * Create offramp
      * @param array $data
      * @return mixed
      */
     public function createOfframp(
         array $data,
         $wallet_id,
         $user_id,
         string $idempotencyKey
     ) {
         $appVersion = env("APP_VERSION", "v1");
         return $this->walletNetwork->post(
             "/{$appVersion}/create/offramp/{$wallet_id}/{$user_id}?idempotencyKey={$idempotencyKey}",
             $data
         );
     }

    /**
     * Get bridge transfer by ID
     * @param string $transferID
     * @return mixed
     */
    public function getBridgeTransfer(string $transferID)
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get(
            "/{$appVersion}/bridge/transfers/{$transferID}",
            "",
            false
        );
    }

    /**
     * Update bridge transfer by ID
     * @param string $transferID
     * @param array $data
     * @return mixed
     */
    public function updateBridgeTransfer(string $transferID, array $data)
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->put(
            "/{$appVersion}/bridge/transfers/{$transferID}",
            $data
        );
    }

    /**
     * Delete bridge transfer by ID
     * @param string $transferID
     * @return mixed
     */
    public function deleteBridgeTransfer(string $transferID)
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->delete(
            "/{$appVersion}/bridge/transfers/{$transferID}"
        );
    }

    /**
     * Get bridge customer external accounts
     * @param string $customerID
     * @return mixed
     */
    public function getBridgeCustomerExternalAccounts(string $customerID)
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get(
            "/{$appVersion}/bridge/customers/{$customerID}/external-accounts",
            "",
            false
        );
    }

    /**
     * Create bridge customer external account
     * @param string $customerID
     * @param array $data
     * @return mixed
     */
    public function createBridgeCustomerExternalAccount(
        string $customerID,
        array $data,
        string $idempotencyKey
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->post(
            "/{$appVersion}/bridge/customers/{$customerID}/external-accounts?idempotencyKey={$idempotencyKey}",
            $data
        );
    }

    /**
     * Update bridge customer external account by ID
     * @param string $customerID
     * @param string $externalAccountID
     * @param array $data
     * @return mixed
     */
    public function updateBridgeCustomerExternalAccount(
        string $customerID,
        string $externalAccountID,
        array $data
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->put(
            "/{$appVersion}/bridge/customers/{$customerID}/external-accounts/{$externalAccountID}",
            $data
        );
    }

    /**
     * Delete bridge customer external account by ID
     * @param string $customerID
     * @param string $externalAccountID
     * @return mixed
     */
    public function deleteBridgeCustomerExternalAccount(
        string $customerID,
        string $externalAccountID
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->delete(
            "/{$appVersion}/bridge/customers/{$customerID}/external-accounts/{$externalAccountID}"
        );
    }

    /**
     * Reactivate bridge customer external account by ID
     * @param string $customerID
     * @param string $externalAccountID
     * @return mixed
     */
    public function reactivateBridgeCustomerExternalAccount(
        string $customerID,
        string $externalAccountID
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->post(
            "/{$appVersion}/bridge/customers/{$customerID}/external-accounts/{$externalAccountID}/reactivate",
            []
        );
    }

    /**
     * Get bridge fees
     * @return mixed
     */
    public function getBridgeFees()
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get(
            "/{$appVersion}/bridge/fees",
            "",
            false
        );
    }

    /**
     * Update bridge fees
     * @param array $data
     * @return mixed
     */
    public function updateBridgeFees(array $data)
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->put("/{$appVersion}/bridge/fees", $data);
    }

    /**
     * Get bridge external account
     * @return mixed
     */
    public function getBridgeExternalAccount()
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get(
            "/{$appVersion}/bridge/external-account",
            "",
            false
        );
    }

    /**
     * Create bridge external account
     * @param array $data
     * @return mixed
     */
    public function createBridgeExternalAccount(
        array $data,
        string $idempotencyKey
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->post(
            "/{$appVersion}/bridge/external-account?idempotencyKey={$idempotencyKey}",
            $data
        );
    }

    /**
     * Get bridge customer card accounts
     * @param string $customerID
     * @return mixed
     */
    public function getBridgeCustomerCardAccounts(string $customerID)
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get(
            "/{$appVersion}/bridge/customers/{$customerID}/card-accounts",
            "",
            false
        );
    }

    /**
     * Create bridge customer card account
     * @param string $customerID
     * @param array $data
     * @return mixed
     */
    public function createBridgeCustomerCardAccount(
        string $customerID,
        array $data,
        string $idempotencyKey
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->post(
            "/{$appVersion}/bridge/customers/{$customerID}/card-accounts?idempotencyKey={$idempotencyKey}",
            $data
        );
    }

    /**
     * Freeze bridge customer card account by ID
     * @param string $customerID
     * @param string $cardAccountID
     * @return mixed
     */
    public function freezeBridgeCustomerCardAccount(
        string $customerID,
        string $cardAccountID
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->post(
            "/{$appVersion}/bridge/customers/{$customerID}/card-accounts/{$cardAccountID}/freeze",
            []
        );
    }

    /**
     * Unfreeze bridge customer card account by ID
     * @param string $customerID
     * @param string $cardAccountID
     * @return mixed
     */
    public function unfreezeBridgeCustomerCardAccount(
        string $customerID,
        string $cardAccountID
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->post(
            "/{$appVersion}/bridge/customers/{$customerID}/card-accounts/{$cardAccountID}/unfreeze",
            []
        );
    }

    /**
     * Get bridge customer card account by ID
     * @param string $customerID
     * @param string $cardAccountID
     * @return mixed
     */
    public function getBridgeCustomerCardAccount(
        string $customerID,
        string $cardAccountID
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get(
            "/{$appVersion}/bridge/customers/{$customerID}/card-accounts/{$cardAccountID}",
            "",
            false
        );
    }

    /**
     * Create bridge customer card account wallet provisioning
     * @param string $customerID
     * @param string $cardAccountID
     * @param array $data
     * @return mixed
     */
    public function createBridgeCustomerCardAccountWalletProvisioning(
        string $customerID,
        string $cardAccountID,
        array $data,
        string $idempotencyKey
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->post(
            "/{$appVersion}/bridge/customers/{$customerID}/card-accounts/{$cardAccountID}/wallet-provisioning?idempotencyKey={$idempotencyKey}",
            $data
        );
    }

    /**
     * Get bridge customer card account authorizations
     * @param string $customerID
     * @param string $cardAccountID
     * @return mixed
     */
    public function getBridgeCustomerCardAccountAuthorizations(
        string $customerID,
        string $cardAccountID
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get(
            "/{$appVersion}/bridge/customers/{$customerID}/card-accounts/{$cardAccountID}/authorizations",
            "",
            false
        );
    }

    /**
     * Get bridge customer card account transactions
     * @param string $customerID
     * @param string $cardAccountID
     * @return mixed
     */
    public function getBridgeCustomerCardAccountTransactions(
        string $customerID,
        string $cardAccountID
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get(
            "/{$appVersion}/bridge/customers/{$customerID}/card-accounts/{$cardAccountID}/transactions",
            "",
            false
        );
    }

    /**
     * Create bridge customer card account withdrawal
     * @param string $customerID
     * @param string $cardAccountID
     * @param array $data
     * @return mixed
     */
    public function createBridgeCustomerCardAccountWithdrawal(
        string $customerID,
        string $cardAccountID,
        array $data,
        string $idempotencyKey
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->post(
            "/{$appVersion}/bridge/customers/{$customerID}/card-accounts/{$cardAccountID}/withdrawals?idempotencyKey={$idempotencyKey}",
            $data
        );
    }

    /**
     * Get bridge customer card account withdrawal by ID
     * @param string $customerID
     * @param string $cardAccountID
     * @param string $withdrawalID
     * @return mixed
     */
    public function getBridgeCustomerCardAccountWithdrawal(
        string $customerID,
        string $cardAccountID,
        string $withdrawalID
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get(
            "/{$appVersion}/bridge/customers/{$customerID}/card-accounts/{$cardAccountID}/withdrawals/{$withdrawalID}",
            "",
            false
        );
    }

    /**
     * Get bridge customer card account withdrawals
     * @param string $customerID
     * @param string $cardAccountID
     * @return mixed
     */
    public function getBridgeCustomerCardAccountWithdrawals(
        string $customerID,
        string $cardAccountID
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get(
            "/{$appVersion}/bridge/customers/{$customerID}/card-accounts/{$cardAccountID}/withdrawals",
            "",
            false
        );
    }

    /**
     * Create bridge customer card account statement
     * @param string $customerID
     * @param string $cardAccountID
     * @param array $data
     * @return mixed
     */
    public function createBridgeCustomerCardAccountStatement(
        string $customerID,
        string $cardAccountID,
        array $data,
        string $idempotencyKey
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->post(
            "/{$appVersion}/bridge/customers/{$customerID}/card-accounts/{$cardAccountID}/statements?idempotencyKey={$idempotencyKey}",
            $data
        );
    }

    /**
     * Create bridge customer card account pin update
     * @param string $customerID
     * @param string $cardAccountID
     * @param array $data
     * @return mixed
     */
    public function createBridgeCustomerCardAccountPinUpdate(
        string $customerID,
        string $cardAccountID,
        array $data,
        string $idempotencyKey
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->post(
            "/{$appVersion}/bridge/customers/{$customerID}/card-accounts/{$cardAccountID}/pin-update?idempotencyKey={$idempotencyKey}",
            $data
        );
    }

    /**
     * Get bridge wallets
     * @return mixed
     */
    public function getBridgeWallets()
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get("/{$appVersion}/bridge/wallets");
    }

    /**
     * Get bridge wallets balances
     * @return mixed
     */
    public function getBridgeWalletsBalances()
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get(
            "/{$appVersion}/bridge/wallets/balances",
            "",
            false
        );
    }

    /**
     * Get bridge wallet transactions
     * @param string $bridgeWalletID
     * @return mixed
     */
    public function getBridgeWalletTransactions(string $bridgeWalletID)
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get(
            "/{$appVersion}/bridge/wallets/{$bridgeWalletID}/transactions",
            "",
            false
        );
    }

    /**
     * Get bridge customer wallets
     * @param string $customerID
     * @return mixed
     */
    public function getBridgeCustomerWallets(string $customerID)
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get(
            "/{$appVersion}/bridge/customers/{$customerID}/wallets",
            "",
            false
        );
    }

    /**
     * Create bridge customer wallet
     * @param string $customerID
     * @param array $data
     * @return mixed
     */
    public function createBridgeCustomerWallet(
        string $customerID,
        array $data,
        string $idempotencyKey
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->post(
            "/{$appVersion}/bridge/customers/{$customerID}/wallets?idempotencyKey={$idempotencyKey}",
            $data
        );
    }

    /**
     * Get bridge customer wallet
     * @param string $customerID
     * @param string $bridgeWalletID
     * @return mixed
     */
    public function getBridgeCustomerWallet(
        string $customerID,
        string $bridgeWalletID
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get(
            "/{$appVersion}/bridge/customers/{$customerID}/wallets/{$bridgeWalletID}",
            "",
            false
        );
    }

    /**
     * Create bridge customer batch settlement schedule
     * @param string $customerID
     * @param array $data
     * @return mixed
     */
    public function createBridgeCustomerBatchSettlementSchedule(
        string $customerID,
        array $data,
        string $idempotencyKey
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->post(
            "/{$appVersion}/bridge/customers/{$customerID}/batch-settlement-schedule?idempotencyKey={$idempotencyKey}",
            $data
        );
    }

    /**
     * Create bridge kyc
     * @param string $walletId
     * @param array $data
     * @return mixed
     */
    public function createBridgeKyc(
        string $walletId,
        array $data,
        string $idempotencyKey
    ) {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->post(
            "/{$appVersion}/bridge/kyc/{$walletId}?idempotencyKey={$idempotencyKey}",
            $data
        );
    }

    /**
     * Get bridge kyc status
     * @param string $kycLinkID
     * @return mixed
     */
    public function getBridgeKycStatus(string $kycLinkID)
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get(
            "/{$appVersion}/bridge/kyc/{$kycLinkID}/status",
            "",
            false
        );
    }

    /**
     * Get bridge webhooks
     * @return mixed
     */
    public function getBridgeWebhooks()
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->get(
            "/{$appVersion}/bridge/webhooks",
            "",
            false
        );
    }

    /**
     * Update bridge webhook
     * @param string $webhookID
     * @param array $data
     * @return mixed
     */
    public function updateBridgeWebhook(string $webhookID, array $data)
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->put(
            "/{$appVersion}/bridge/webhooks/{$webhookID}",
            $data
        );
    }

    /**
     * Delete bridge webhook
     * @param string $webhookID
     * @return mixed
     */
    public function deleteBridgeWebhook(string $webhookID)
    {
        $appVersion = env("APP_VERSION", "v1");
        return $this->walletNetwork->delete(
            "/{$appVersion}/bridge/webhooks/{$webhookID}"
        );
    }

    /**
     * Create ad
     * @param array $data
     * @return mixed
     */
    public function createAd(array $data)
    {
        return $this->walletNetwork->post("/v1/ads", $data);
    }

    /**
     * Update ad
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function updateAd(int $id, array $data)
    {
        return $this->walletNetwork->put("/v1/ads/{$id}", $data);
    }

    /**
     * Delete ad
     * @param int $id
     * @return mixed
     */
    public function deleteAd(int $id)
    {
        return $this->walletNetwork->delete("/v1/ads/{$id}");
    }
    
    // Order

    /**
     * Create order
     * @param array $data
     * @return mixed
     */
    public function createOrder(array $data)
    {
        return $this->walletNetwork->post("/v1/orders", $data);
    }

    /**
     * Cancel order
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function cancelOrder(int $id, array $data)
    {
        return $this->walletNetwork->post("/v1/orders/{$id}/cancel", $data);
    }

    
}
