<?php

namespace App\GraphQL\Mutations;
use App\Exceptions\GraphQLException;
use App\Models\Wallet\UserBank;
use App\Services\BlockchainService;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

final class WalletMutator
{
    protected WalletService $walletService;
    protected BlockchainService $blockchainService;

    public function __construct()
    {
        $this->walletService = new WalletService();
        $this->blockchainService = new BlockchainService();
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
     * @return bool True if the withdrawal was initiated successfully (not implemented yet).
     */
    public function initiateWithdrawal($_, array $args): bool
    {
        $authUser = Auth::user();

        // TODO: Implement initiateWithdrawal method
        return false;
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
}
