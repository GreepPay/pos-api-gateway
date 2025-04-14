<?php

namespace App\GraphQL\Mutations;

use App\Exceptions\GraphQLException;
use App\Models\Auth\User;
use App\Models\Wallet\Wallet;
use App\Services\AuthService;
use App\Services\BlockchainService;
use App\Services\NotificationService;
use App\Services\UserService;
use App\Services\WalletService;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class AuthMutator
{
    use FileUploadTrait;

    /**
     * @var AuthService
     */
    protected $authService;
    /**
     * @var NotificationService
     */
    protected $notificationService;
    /**
     * @var UserService
     */
    protected $userService;
    /**
     * @var BlockchainService
     */
    protected $blockchainService;
    /**
     * @var WalletService
     */
    protected $walletService;

    /**
     * AuthMutator constructor.
     */
    public function __construct()
    {
        $this->authService = new AuthService();
        $this->notificationService = new NotificationService();
        $this->userService = new UserService();
        $this->blockchainService = new BlockchainService();
        $this->walletService = new WalletService();
    }

    /**
     * Sign in a user and return a token.
     *
     * @param mixed $_
     * @param array $args
     * @return array
     */
    public function signIn($_, array $args): array
    {
        $userAuth = $this->authService->authenticateUser([
            "username" => $args["email"],
            "password" => $args["password"],
        ]);

        return [
            "token" => $userAuth["data"]["token"],
            "user" => User::query()
                ->where("id", $userAuth["data"]["user"]["id"])
                ->first(),
        ];
    }

    /**
     * Sign Up a new user.
     *
     * @param mixed $_
     * @param array $args
     * @return User
     * @throws \Exception
     */
    public function signUp($_, array $args): User
    {
        // Process the array of document uploads directly.
        // Each element in $args['documents'] is expected to be an instance of UploadedFile.
        $documentUrls = [];
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

        // Create a new user in auth service
        $authUser = $this->authService->saveUser([
            "firstName" => $args["first_name"],
            "lastName" => $args["last_name"],
            "email" => $args["email"],
            "password" => $args["password"],
            "role" => "Business",
        ]);

        $authUser = $authUser["data"];

        // Create a default profile for the user
        $this->userService->createProfile([
            "user_type" => "Business",
            "auth_user_id" => (string) $authUser["id"],
            "default_currency" => $args["default_currency"],
            "profileData" => [
                "country" => $args["country"],
                "city" => $args["state"],
                "business_name" => $args["business_name"],
                "documents" => $documentUrls,
            ],
        ]);

        // Let create a default wallet for the user
        // Check if user has a wallet already
        $userWallet = Wallet::query()
            ->where("user_id", $authUser["id"])
            ->first();

        if (!$userWallet) {
            // But before creating the wallet, a need an account to be generated on the blockchain for the new user
            $blockchainAccount = $this->blockchainService->createAccount([
                "account_type" => "user",
                "status" => "closed",
            ]);

            $blockchainAccount = $blockchainAccount["data"];

            // Create a default wallet for the user
            $this->walletService->createWallet([
                "user_id" => $authUser["id"],
                "blockchain_account_id" => $blockchainAccount["id"],
                "currency" => "USDC",
            ]);
        }

        // Send a verify email notification to the user
        // TODO: Implement email verification notification

        return User::query()->where("id", $authUser["id"])->first();
    }

    /**
     * Resend email OTP to a user.
     *
     * @param mixed $_
     * @param array $args
     * @return bool
     * @throws GraphQLException
     */
    public function resendEmailOTP($_, array $args): bool
    {
        $userWithEmail = User::query()->where("email", $args["email"])->first();

        if (!$userWithEmail) {
            throw new GraphQLException("User with email not found");
        }

        // First reset user OTP
        $this->authService->resetOtp([
            "email" => $userWithEmail->email,
        ]);

        // TODO: Implement email verification notification

        return true;
    }

    /**
     * Send reset password OTP to a user.
     *
     * @param mixed $_
     * @param array $args
     * @return bool
     * @throws GraphQLException
     */
    public function sendResetPasswordOTP($_, array $args): bool
    {
        $userWithEmail = User::query()->where("email", $args["email"])->first();

        if (!$userWithEmail) {
            throw new GraphQLException("User with email not found");
        }

        // First reset user OTP
        $this->authService->resetOtp([
            "email" => $userWithEmail->email,
        ]);

        // TODO: Implement email reset password notification

        return true;
    }

    /**
     * Reset the password of a user.
     *
     * @param mixed $_
     * @param array $args
     * @return bool
     * @throws GraphQLException
     */
    public function resetPassword($_, array $args): bool
    {
        $userWithUuid = User::query()
            ->where("uuid", $args["user_uuid"])
            ->first();

        if (!$userWithUuid) {
            throw new GraphQLException("User not found");
        }

        // First verify the user OTP
        $this->authService->verifyUserOtp([
            "userUuid" => $userWithUuid->uuid,
            "email" => $userWithUuid->email,
            "otp" => $args["otp"],
        ]);

        // If it succeeds, reset the password
        $this->authService->updatePassword([
            "currentPassword" => null,
            "newPassword" => $args["new_password"],
        ]);

        return true;
    }

    /**
     * Update the password of the currently authenticated user.
     *
     * @param mixed $_
     * @param array $args
     * @return bool
     */
    public function updatePassword($_, array $args): bool
    {
        $authUser = Auth::user();

        $this->authService->updatePassword([
            "currentPassword" => $args["current_password"],
            "newPassword" => $args["new_password"],
        ]);

        return true;
    }

    /**
     * Verify a user's OTP.
     *
     * @param mixed $_
     * @param array $args
     * @return bool
     * @throws GraphQLException
     */
    public function verifyUserOTP($_, array $args): bool
    {
        $userWithUuid = User::query()
            ->where("uuid", $args["user_uuid"])
            ->first();

        if (!$userWithUuid) {
            throw new GraphQLException("User with UUID not found");
        }

        $payload = [
            "userUuid" => $userWithUuid->uuid,
            "email" => $userWithUuid->email,
            "otp" => $args["otp"],
        ];

        // Free pass for now, until sending of email verification is implemented
        // $this->authService->verifyUserOtp($payload);

        return true;
    }

    /**
     * Log out the currently authenticated user.
     *
     * @param mixed $_
     * @param array $args
     * @return bool
     */
    public function logout($_, array $args): bool
    {
        return $this->authService->logOut();

        return true;
    }

    /**
     * Delete a user.
     *
     * @param mixed $_
     * @param array $args
     * @return mixed
     * @throws GraphQLException
     */
    public function deleteUser($_, array $args): mixed
    {
        return $this->authService->deleteUser($args["id"]);
    }
}
