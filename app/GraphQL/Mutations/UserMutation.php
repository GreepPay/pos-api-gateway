<?php

namespace App\GraphQL\Mutations;

use App\Exceptions\GraphQLException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\UserService;

final class UserMutation
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function createProfile($_, array $args)
    {
        if (!isset($args["input"]) || !is_array($args["input"])) {
            throw new GraphQLException("Invalid input: 'input' field is required");
        }

        $input = $args["input"];

        if (!isset($input["auth_user_id"]) || !isset($input["user_type"])) {
            throw new GraphQLException("Missing required fields: 'auth_user_id' or 'user_type'");
        }

        $profileData = [
            "auth_user_id" => $input["auth_user_id"],
            "user_type" => $input["user_type"],
            "profile_picture" => $input["profile_picture"] ?? null,
            "profileData" => []
        ];

        switch ($input["user_type"]) {
            case "Business":
                if (!isset($input["business"]) || !is_array($input["business"])) {
                    throw new GraphQLException("Invalid business profile data.");
                }
                $profileData["profileData"] = $input["business"];
                break;
            case "Rider":
                if (!isset($input["rider"]) || !is_array($input["rider"])) {
                    throw new GraphQLException("Invalid rider profile data.");
                }
                $profileData["profileData"] = $input["rider"];
                break;
            case "Customer":
                if (!isset($input["customer"]) || !is_array($input["customer"])) {
                    throw new GraphQLException("Invalid customer profile data.");
                }
                $profileData["profileData"] = $input["customer"];
                break;
            default:
                throw new GraphQLException("Invalid user_type.");
        }

        $response = $this->userService->createProfile(new Request($profileData));

        if (!isset($response["data"]["profile"])) {
            throw new GraphQLException("Invalid response from UserService. Missing 'profile' field.");
        }

        // Extract response data safely
        $profile = $response["data"]["profile"];

        return [
            "id" => $profile["id"],
            "auth_user_id" => $response["data"]["auth_user_id"],
            "user_type" => $response["data"]["user_type"],
            "profile_picture" => $response["data"]["profile_picture"],
            "verification_status" => $response["data"]["verification_status"],
            "created_at" => $this->parseDateTime($response["data"]["created_at"]),
            "updated_at" => $this->parseDateTime($response["data"]["updated_at"]),
            "profileData" => $profile
        ];
    }

    /**
     * Convert DateTime string to Carbon instance.
     */
    private function parseDateTime(?string $date): ?string
    {
        return $date ? Carbon::parse($date)->format('Y-m-d H:i:s') : null;
    }


    public function updateProfile($_, array $args)
    {
        $response = $this->userService->updateProfile(
            new Request([
                "auth_user_id" => $args["auth_user_id"],
                "user_type" => $args["user_type"],
                // Add additional profile fields to update here.
            ])
        );

        return $response["data"]["updateProfile"] ?? null;
    }

    public function deleteProfile($_, array $args)
    {
        $response = $this->userService->deleteProfile(
            new Request([
                "auth_user_id" => $args["auth_user_id"],
            ])
        );

        return $response["data"]["deleteProfile"] ?? null;
    }
}
