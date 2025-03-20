<?php

namespace App\GraphQL\Mutations;

use App\Exceptions\GraphQLException;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;

final class AuthMutation
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // Existing mutations
    public function signIn($_, array $args)
    {
        $authResponse = $this->authService->loginUser(
            new Request([
                "username" => $args["username"],
                "password" => $args["password"],
            ])
        );

        return $authResponse;
    }

    public function signUp($_, array $args)
    {
        $requiredFields = [
            "firstName",
            "lastName",
            "email",
            "phoneNumber",
            "password",
            "role",
        ];
        foreach ($requiredFields as $field) {
            if (!isset($args[$field])) {
                throw new GraphQLException("Missing required field: {$field}");
            }
        }

        $payload = [
            "firstName" => $args["firstName"],
            "lastName" => $args["lastName"],
            "email" => $args["email"],
            "phoneNumber" => $args["phoneNumber"],
            "password" => $args["password"],
            "role" => $args["role"],
            "ssoId" => $args["ssoId"] ?? null,
            "otp" => $args["otp"] ?? null,
            "isSso" => $args["isSso"] ?? false,
            "ignoreError" => $args["ignoreError"] ?? false,
        ];

        $authResponse = $this->authService->addUser(new Request($payload));

        return $authResponse;
    }

    public function resetOtp($_, array $args)
    {
        if (!isset($args["email"])) {
            throw new GraphQLException("Missing required field: email");
        }

        $payload = [
            "email" => $args["email"],
        ];

        return $this->authService->resetOtp(new Request($payload));
    }

    public function verifyOtp($_, array $args)
    {
        if (!isset($args["otp"])) {
            throw new GraphQLException("Missing required field: otp");
        }

        $payload = [
            "otp" => $args["otp"],
            "userUuid" => $args["userUuid"] ?? null,
            "email" => $args["email"] ?? null,
            "phone" => $args["phone"] ?? null,
        ];

        return $this->authService->verifyOtp(new Request($payload));
    }

    public function updatePassword($_, array $args)
    {
        if (!isset($args["currentPassword"]) || !isset($args["newPassword"])) {
            throw new GraphQLException(
                "Missing required fields: currentPassword and/or newPassword"
            );
        }

        $payload = [
            "currentPassword" => $args["currentPassword"],
            "newPassword" => $args["newPassword"],
        ];

        return $this->authService->updatePassword(new Request($payload));
    }

    public function updateProfile($_, array $args)
    {
        if (!isset($args["userUuid"])) {
            throw new GraphQLException("Missing required field: userUuid");
        }

        $payload = [
            "userUuid" => $args["userUuid"],
            "firstName" => $args["firstName"] ?? null,
            "lastName" => $args["lastName"] ?? null,
            "phoneNumber" => $args["phoneNumber"] ?? null,
            "email" => $args["email"] ?? null,
        ];

        return $this->authService->updateProfile(new Request($payload));
    }

    public function logout($_, array $args)
    {
        return $this->authService->logout();
    }

    public function deleteUser($_, array $args)
    {
        if (!isset($args["id"])) {
            throw new GraphQLException("Missing required field: id");
        }

        return $this->authService->deleteUser($args["id"]);
    }

    public function createRole($_, array $args)
    {
        if (!isset($args["name"]) || !isset($args["editable_name"])) {
            throw new GraphQLException(
                "Missing required fields: name and/or editable_name"
            );
        }

        $payload = [
            "name" => $args["name"],
            "editable_name" => $args["editable_name"],
        ];

        if (isset($args["role_uuid"])) {
            $payload["role_uuid"] = $args["role_uuid"];
        }

        return $this->authService->createRole(new Request($payload));
    }

    public function updatePermissions($_, array $args)
    {
        if (!isset($args["role_uuid"]) || !isset($args["permissions"])) {
            throw new GraphQLException(
                "Missing required fields: role_uuid and/or permissions"
            );
        }

        $payload = [
            "role_uuid" => $args["role_uuid"],
            "permissions" => $args["permissions"],
        ];

        return $this->authService->updatePermissions(new Request($payload));
    }

    public function userCan($_, array $args)
    {
        if (!isset($args["permission_name"])) {
            throw new GraphQLException(
                "Missing required field: permission_name"
            );
        }

        return $this->authService->userCan($args["permission_name"]);
    }
}
