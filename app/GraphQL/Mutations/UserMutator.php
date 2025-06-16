<?php

namespace App\GraphQL\Mutations;

use App\Exceptions\GraphQLException;
use App\Models\Auth\User;
use App\Models\User\Business;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\AuthService;
use App\Services\WalletService;
use App\Services\BlockchainService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

final class UserMutator
{
    use FileUploadTrait;

    protected UserService $userService;
    protected AuthService $authService;
    protected BlockchainService $blockchainService;
    protected WalletService $walletService;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->authService = new AuthService();
        $this->blockchainService = new BlockchainService();
        $this->walletService = new WalletService();
    }

    /**
     * Update the profile of the authenticated user.
     *
     * @param  mixed  $_
     * @param  array  $args
     * @return User
     * @throws \Exception
     */
    public function updateProfile($_, array $args): User
    {
        $authUser = Auth::user();

        $profilePhotoUrl = null;
        // Check if profile photo is provided
        if (isset($args["profile_photo"])) {
            $request = new Request();
            $request->files->set("attachment", $args["profile_photo"]);
            $profilePhotoUrl = $this->uploadFile($request, false);
        }

        // Update names in auth service
        if (
            isset($args["first_name"]) ||
            isset($args["last_name"]) ||
            isset($args["auth_passcode"])
        ) {
            $this->authService->updateAuthUserProfile([
                "userUuid" => $authUser->uuid,
                "firstName" => isset($args["first_name"])
                    ? $args["first_name"]
                    : null,
                "lastName" => isset($args["last_name"])
                    ? $args["last_name"]
                    : null,
                "phoneNumber" => isset($args["phone_number"])
                    ? $args["phone_number"]
                    : null,
                "transactionPin" => isset($args["auth_passcode"])
                    ? $args["auth_passcode"]
                    : null,
            ]);
        }

        $logoUrl = null;

        if (isset($args["business_logo"])) {
            $request = new Request();
            $request->files->set("attachment", $args["business_logo"]);
            $url = $this->uploadFile($request, false);
            $logoUrl = $url;
        }

        // Update other user info in user service
        $payload = [
            "user_type" => "Business",
            "auth_user_id" => $authUser->id,
            "default_currency" => isset($args["default_currency"])
                ? $args["default_currency"]
                : null,
            "profile_picture" => $profilePhotoUrl,
            "profileData" => [],
        ];

        $this->userService->updateProfile($payload);

        return User::query()->find($authUser->id);
    }

    /**
     * Verify the identity of a user.
     *
     * @param  mixed  $_
     * @param  array  $args
     * @return bool
     * @throws GraphQLException
     */
    public function verifyUserIdentity($_, array $args): bool
    {
        $payload = [
            "user_uuid" => $args["user_uuid"],
            "id_type" => $args["id_type"],
            "id_number" => $args["id_number"],
            "id_country" => $args["id_country"],
        ];

        $user = User::query()->where("uuid", $payload["user_uuid"])->first();

        if (!$user) {
            throw new GraphQLException("User not found");
        }

        $this->userService->verifyIdentity($payload);

        // Activate user blockchain account
        $userBlockchainAccountId = $user->wallet->blockchain_account_id;

        $this->blockchainService->activateAccount($userBlockchainAccountId);

        return true;
    }

    /**
     * Create a new business profile
     *
     * @param  mixed  $_
     * @param  array  $args
     * @return mixed
     * @throws GraphQLException
     */
    public function createBusinessProfile($_, array $args): mixed
    {
        $authUser = Auth::user();

        $logoUrl = null;

        if (isset($args["business_logo"])) {
            $request = new Request();
            $request->files->set("attachment", $args["business_logo"]);
            $url = $this->uploadFile($request, false);
            $logoUrl = $url;
        }

        // Each element in $args['documents'] is expected to be an instance of UploadedFile.
        $documentUrls = [];
        if (isset($args["documents"])) {
            if (is_array($args["documents"])) {
                foreach ($args["documents"] as $doc) {
                    // Call your uploadFile function with the file upload.
                    $request = new Request();
                    $request->files->set("attachment", $doc);
                    $url = $this->uploadFile($request, false);
                    $documentUrls[] = $url;
                }
            } else {
                throw new GraphQLException(
                    "Invalid 'documents' input. Expected an array of uploads."
                );
            }
        }

        $payload = [
            "auth_user_id" => $authUser->id,
            "business_name" => $args["business_name"],
            "business_type" => $args["business_type"],
            "logo" => $logoUrl,
            "location" => $args["location"],
            "category" => isset($args["category"]) ? $args["category"] : null,
            "banner" => isset($args["banner"]) ? $args["banner"] : null,
            "description" => isset($args["description"])
                ? $args["description"]
                : null,
            "website" => isset($args["website"]) ? $args["website"] : null,
            "resident_permit" => isset($args["resident_permit"])
                ? $args["resident_permit"]
                : null,
            "passport" => isset($args["passport"]) ? $args["passport"] : null,
            "registration_number" => isset($args["registration_number"])
                ? $args["registration_number"]
                : null,
            "documents" => $documentUrls,
            "country" => isset($args["country"]) ? $args["country"] : null,
            "city" => isset($args["city"]) ? $args["city"] : null,
            "address" => isset($args["address"])
                ? json_decode($args["address"], true)
                : null,
            "default_currency" => isset($args["default_currency"])
                ? $args["default_currency"]
                : null,
        ];

        $business = $this->userService->createBusinessProfile($payload);

        $business = $business["data"];

        // Create a new wallet for business

        // First create a new blockchain account
        $blockchainAccount = $this->blockchainService->createAccount([
            "account_type" => "user",
            "status" => "closed",
        ]);

        $blockchainAccount = $blockchainAccount["data"];

        // Create a default wallet for the business
        $this->walletService->createWallet([
            "user_id" => $authUser["id"],
            "business_id" => $business["id"],
            "blockchain_account_id" => $blockchainAccount["id"],
            "currency" => "USDC",
        ]);

        // Activate the blockchain account
        $this->blockchainService->activateAccount($blockchainAccount["id"]);

        return Business::query()->where("id", $business["id"])->first();
    }

    /**
     * Update a new business profile
     *
     * @param  mixed  $_
     * @param  array  $args
     * @return mixed
     * @throws GraphQLException
     */
    public function updateBusinessProfile($_, array $args): mixed
    {
        $authUser = Auth::user();

        $business = Business::query()
            ->where("id", $args["business_uuid"])
            ->first();

        if (!$business) {
            throw new GraphQLException("Business not found");
        }

        $logoUrl = null;

        if (isset($args["business_logo"])) {
            $request = new Request();
            $request->files->set("attachment", $args["business_logo"]);
            $url = $this->uploadFile($request, false);
            $logoUrl = $url;
        }

        // Each element in $args['documents'] is expected to be an instance of UploadedFile.
        $documentUrls = [];
        if (isset($args["documents"])) {
            if (is_array($args["documents"])) {
                foreach ($args["documents"] as $doc) {
                    // Call your uploadFile function with the file upload.
                    $request = new Request();
                    $request->files->set("attachment", $doc);
                    $url = $this->uploadFile($request, false);
                    $documentUrls[] = $url;
                }
            } else {
                throw new GraphQLException(
                    "Invalid 'documents' input. Expected an array of uploads."
                );
            }
        }

        $payload = [
            "id" => $business->id,
            "auth_user_id" => $authUser->id,
            "business_type" => $business->business_type,
            "business_name" => isset($args["business_name"])
                ? $args["business_name"]
                : null,
            "logo" => $logoUrl,
            "location" => isset($args["location"]) ? $args["location"] : null,
            "category" => isset($args["category"]) ? $args["category"] : null,
            "banner" => isset($args["banner"]) ? $args["banner"] : null,
            "description" => isset($args["description"])
                ? $args["description"]
                : null,
            "website" => isset($args["website"]) ? $args["website"] : null,
            "resident_permit" => isset($args["resident_permit"])
                ? $args["resident_permit"]
                : null,
            "passport" => isset($args["passport"]) ? $args["passport"] : null,
            "registration_number" => isset($args["registration_number"])
                ? $args["registration_number"]
                : null,
            "documents" => $documentUrls,
            "country" => isset($args["country"]) ? $args["country"] : null,
            "city" => isset($args["city"]) ? $args["city"] : null,
            "address" => isset($args["address"])
                ? json_decode($args["address"], true)
                : null,
            "default_currency" => isset($args["default_currency"])
                ? $args["default_currency"]
                : null,
        ];

        $business = $this->userService->updateBusinessProfile($payload);

        $business = Business::query()
            ->where("id", $business["data"]["id"])
            ->first();

        if (!$business->wallet) {
            // Create a new wallet for business

            // First create a new blockchain account
            $blockchainAccount = $this->blockchainService->createAccount([
                "account_type" => "user",
                "status" => "closed",
            ]);

            $blockchainAccount = $blockchainAccount["data"];

            // Create a default wallet for the business
            $this->walletService->createWallet([
                "user_id" => $authUser["id"],
                "business_id" => $business["id"],
                "blockchain_account_id" => $blockchainAccount["id"],
                "currency" => "USDC",
            ]);

            // Activate the blockchain account
            $this->blockchainService->activateAccount($blockchainAccount["id"]);
        }

        return $business;
    }
}
