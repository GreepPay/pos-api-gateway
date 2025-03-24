<?php

namespace App\GraphQL\Mutations;

use App\Exceptions\GraphQLException;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class WalletMutation
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }
    public function createWallet($_, array $args)
    {
        if (!isset($args["input"]) || !is_array($args["input"])) {
            throw new GraphQLException("Input required for wallet creation");
        }

        $user = Auth::user();
        $input = $args["input"];
        $input["user_id"] = $user->id;

        $response = $this->walletService->createWallet(new Request($input));
        return $response["data"] ?? $response;
    }

    public function updateWalletBalance($_, array $args)
    {
        if (!isset($args["id"]) || !isset($args["input"]) || !is_array($args["input"])) {
            throw new GraphQLException("ID and input are required for wallet balance update");
        }
        $response = $this->walletService->updateWalletBalance($args["id"], new Request($args["input"]));
        return $response["data"] ?? $response;
    }

    public function softDeleteWallet($_, array $args)
    {
        if (!isset($args["id"])) {
            throw new GraphQLException("Wallet ID is required");
        }
        $response = $this->walletService->softDeleteWallet($args["id"]);
        return $response["data"] ?? $response;
    }

    // Point Transactions
    public function createPointTransaction($_, array $args)
    {
        if (!isset($args["input"]) || !is_array($args["input"])) {
            throw new GraphQLException("Input required for point transaction");
        }
        $response = $this->walletService->createPointTransaction(new Request($args["input"]));
        return $response["data"] ?? $response;
    }

    public function updatePointTransactionStatus($_, array $args)
    {
        if (!isset($args["id"]) || !isset($args["status"])) {
            throw new GraphQLException("ID and status are required for updating point transaction status");
        }
        $payload = ["status" => $args["status"]];
        $response = $this->walletService->updatePointTransactionStatus($args["id"], new Request($payload));
        return $response["data"] ?? $response;
    }

    public function softDeletePointTransaction($_, array $args)
    {
        if (!isset($args["id"])) {
            throw new GraphQLException("Point transaction ID is required");
        }
        $response = $this->walletService->softDeletePointTransaction($args["id"]);
        return $response["data"] ?? $response;
    }

    // Transactions
    public function createTransaction($_, array $args)
    {
        if (!isset($args["input"]) || !is_array($args["input"])) {
            throw new GraphQLException("Input required for transaction creation");
        }
        $response = $this->walletService->createTransaction(new Request($args["input"]));
        return $response["data"] ?? $response;
    }

    public function updateTransactionStatus($_, array $args)
    {
        if (!isset($args["id"]) || !isset($args["status"])) {
            throw new GraphQLException("ID and status are required for updating transaction status");
        }
        $payload = ["status" => $args["status"]];
        $response = $this->walletService->updateTransactionStatus($args["id"], new Request($payload));
        return $response["data"] ?? $response;
    }

    public function softDeleteTransaction($_, array $args)
    {
        if (!isset($args["id"])) {
            throw new GraphQLException("Transaction ID is required");
        }
        $response = $this->walletService->softDeleteTransaction($args["id"]);
        return $response["data"] ?? $response;
    }

    // User Banks
    public function createUserBank($_, array $args)
    {
        if (!isset($args["input"]) || !is_array($args["input"])) {
            throw new GraphQLException("Input required for user bank creation");
        }
        $response = $this->walletService->createUserBank(new Request($args["input"]));
        return $response["data"] ?? $response;
    }

    public function updateUserBank($_, array $args)
    {
        if (!isset($args["id"]) || !isset($args["input"]) || !is_array($args["input"])) {
            throw new GraphQLException("ID and input are required for updating user bank");
        }
        $response = $this->walletService->updateUserBank($args["id"], new Request($args["input"]));
        return $response["data"] ?? $response;
    }

    public function softDeleteUserBank($_, array $args)
    {
        if (!isset($args["id"])) {
            throw new GraphQLException("User bank ID is required");
        }
        $response = $this->walletService->softDeleteUserBank($args["id"]);
        return $response["data"] ?? $response;
    }
}
