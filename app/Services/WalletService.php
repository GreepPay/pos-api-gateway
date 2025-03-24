<?php

namespace App\Services;

use App\Datasource\NetworkHandler;
use Illuminate\Http\Request;

class WalletService
{
    protected $serviceUrl;
    protected NetworkHandler $walletNetwork;

    public function __construct(
        bool $useCache = true,
        array $headers = [],
        string $apiType = "graphql"
    ) {
        $this->serviceUrl = env(
            "WALLET_API",
            env("SERVICE_BROKER_URL") . "/broker/greep-wallet/" . env("APP_STATE")
        );

        $this->walletNetwork = new NetworkHandler(
            "",
            $this->serviceUrl,
            $useCache,
            $headers,
            $apiType
        );
    }

    public function createWallet($request)
    {
        return $this->walletNetwork->post("/v1/wallets", $request->all());
    }

    public function updateWalletBalance($id, $request)
    {
        return $this->walletNetwork->post("/v1/wallets/{$id}/balance", $request->all());
    }

    public function softDeleteWallet($id)
    {
        return $this->walletNetwork->post("/v1/wallets/{$id}/soft-delete", []);
    }

    // Point Transactions Endpoints
    public function createPointTransaction($request)
    {
        return $this->walletNetwork->post("/v1/point-transactions", $request->all());
    }

    public function updatePointTransactionStatus($id, $request)
    {
        return $this->walletNetwork->post("/v1/point-transactions/{$id}/status", $request->all());
    }

    public function softDeletePointTransaction($id)
    {
        return $this->walletNetwork->post("/v1/point-transactions/{$id}/soft-delete", []);
    }

    public function createTransaction($request)
    {
        return $this->walletNetwork->post("/v1/transactions", $request->all());
    }

    public function updateTransactionStatus($id, $request)
    {
        return $this->walletNetwork->post("/v1/transactions/{$id}/status", $request->all());
    }

    public function softDeleteTransaction($id)
    {
        return $this->walletNetwork->post("/v1/transactions/{$id}/soft-delete", []);
    }

    public function createUserBank($request)
    {
        return $this->walletNetwork->post("/v1/user-banks", $request->all());
    }

    public function updateUserBank($id, $request)
    {
        return $this->walletNetwork->post("/v1/user-banks/{$id}", $request->all());
    }

    public function softDeleteUserBank($id)
    {
        return $this->walletNetwork->post("/v1/user-banks/{$id}/soft-delete", []);
    }
}
