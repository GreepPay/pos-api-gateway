<?php

namespace App\GraphQL\Mutations;

use App\Exceptions\GraphQLException;
use App\Models\Auth\User;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\AuthService;
use App\Services\BlockchainService;
use Illuminate\Support\Facades\Auth;

final class UserMutator
{
    use FileUploadTrait;

    protected UserService $userService;
    protected AuthService $authService;
    protected BlockchainService $blockchainService;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->authService = new AuthService();
        $this->blockchainService = new BlockchainService();
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
        if ($args["profile_photo"]) {
            $request = new Request();
            $request->files->set("attachment", $args["profile_photo"]);
            $profilePhotoUrl = $this->uploadFile($request, false);
        }

        // Update names in auth service
        if (isset($args["first_name"]) || isset($args["last_name"])) {
            $this->authService->updateAuthUserProfile([
                "userUuid" => $authUser->uuid,
                "firstName" => isset($args["first_name"])
                    ? $args["first_name"]
                    : null,
                "lastName" => isset($args["last_name"])
                    ? $args["last_name"]
                    : null,
            ]);
        }

        // Update other user info in user service
        $payload = [
            "auth_user_id" => $authUser->id,
            "default_currency" => isset($args["default_currency"])
                ? $args["default_currency"]
                : null,
            "profile_picture" => $profilePhotoUrl,
            "profileData" => [
                "country" => isset($args["country"]) ? $args["country"] : null,
                "city" => isset($args["state"]) ? $args["state"] : null,
                "business_name" => isset($args["business_name"])
                    ? $args["business_name"]
                    : null,
            ],
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
}
