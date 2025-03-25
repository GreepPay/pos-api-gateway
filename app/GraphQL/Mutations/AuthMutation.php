<?php

namespace App\GraphQL\Mutations;

use App\Exceptions\GraphQLException;
use App\Services\AuthService;
use App\Services\UserService;
use Auth;
use Illuminate\Http\Request;
use App\Traits\FileUploadTrait;

final class AuthMutation
{
    protected AuthService $authService;
    protected UserService $userService;

    use FileUploadTrait;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function signIn($_, array $args)
    {
        if (empty($args['username']) || empty($args['password'])) {
            throw new GraphQLException("Username and password are required.");
        }

        $authResponse = $this->authService->loginUser(
            new Request([
                "username" => $args["username"],
                "password" => $args["password"]
            ])
        );

        return $authResponse;
    }

    public function signUp($_, array $args)
    {
        // List required fields for signup
        $requiredFields = [
            'firstName',
            'lastName',
            'email',
            'password',
            'business_name',
            'state',
            'country',
            'documents',
            'default_currency',
        ];
        foreach ($requiredFields as $field) {
            if (!isset($args[$field])) {
                throw new GraphQLException("Missing required field: {$field}");
            }
        }

        // Process the array of document uploads directly.
        // Each element in $args['documents'] is expected to be an instance of UploadedFile.
        $documentUrls = [];
        if (is_array($args['documents'])) {
            foreach ($args['documents'] as $doc) {
                // Call your uploadFile function with the file upload.
                $url = $this->uploadFile($doc, false);
                $documentUrls[] = $url;
            }
        } else {
            throw new GraphQLException("Invalid 'documents' input. Expected an array of uploads.");
        }

        // Build payload for user creation
        $payload = [
            'firstName' => $args['firstName'],
            'lastName' => $args['lastName'],
            'email' => $args['email'],
            'password' => $args['password'],
            'state' => $args['state'],
            'country' => $args['country'],
            'default_currency' => $args['default_currency'],
            'role' => 'business',
        ];

        // Create the user by calling the signup service.
        $authResponse = $this->authService->addUser(new Request($payload));

        // Extract the created user from the response.
        if (!isset($authResponse['data']['user'])) {
            throw new GraphQLException("User creation failed.");
        }
        $user = $authResponse['data']['user'];

        // Build the profile payload for a Business user.
        $profileData = [
            "auth_user_id" => $user->id,
            "user_type" => "Business",
            "profile_picture" => null,
            "profileData" => [
                "business_name" => $args['business_name'],
                "documents" => $documentUrls
            ]
        ];

        // Create the business profile
        $this->userService->createProfile(new Request($profileData));

        return $authResponse;
    }

    public function resetOtp($_, array $args)
    {
        if (!isset($args['email'])) {
            throw new GraphQLException("Missing required field: email");
        }

        $payload = [
            'email' => $args['email']
        ];

        return $this->authService->resetOtp(new Request($payload));
    }

    public function verifyOtp($_, array $args)
    {
        if (!isset($args['otp'])) {
            throw new GraphQLException("Missing required field: otp");
        }

        $payload = [
            'otp' => $args['otp'],
            'userUuid' => $args['userUuid'] ?? null,
            'email' => $args['email'] ?? null,
            'phone' => $args['phone'] ?? null,
        ];

        return $this->authService->verifyOtp(new Request($payload));
    }

    public function updatePassword($_, array $args)
    {
        $authUser = Auth::user();

        if ($authUser) {
            if (!isset($args['old_password']) || !isset($args['new_password'])) {
                throw new GraphQLException("Missing required fields: old_password and/or new_password");
            }

            $payload = [
                'currentPassword' => $args['old_password'],
                'newPassword' => $args['new_password']
            ];
        } else {
            if (!isset($args['otp']) || !isset($args['userUuid']) || !isset($args['new_password'])) {
                throw new GraphQLException("Missing required fields: otp, userUuid and/or new_password");
            }

            $verifyPayload = [
                'otp' => $args['otp'],
                'userUuid' => $args['userUuid']
            ];
            $verifyResponse = $this->authService->verifyOtp(new Request($verifyPayload));
            if (!isset($verifyResponse['success']) || !$verifyResponse['success']) {
                throw new GraphQLException("OTP verification failed: " . ($verifyResponse['message'] ?? 'Unknown error'));
            }

            $payload = [
                'currentPassword' => null,
                'newPassword' => $args['new_password']
            ];
        }

        return $this->authService->updatePassword(new Request($payload));
    }
    public function updateProfile($_, array $args)
    {
        $userPayload = [];
        if (isset($args['first_name'])) {
            $userPayload['firstName'] = $args['first_name'];
        }
        if (isset($args['last_name'])) {
            $userPayload['lastName'] = $args['last_name'];
        }
        if (isset($args['email'])) {
            $userPayload['email'] = $args['email'];
        }
        if (isset($args['phoneNumber'])) {
            $userPayload['phoneNumber'] = $args['phoneNumber'];
        }

        if (isset($args['state'])) {
            $userPayload['state'] = $args['state'];
        }
        if (isset($args['country'])) {
            $userPayload['country'] = $args['country'];
        }
        if (isset($args['default_currency'])) {
            $userPayload['default_currency'] = $args['default_currency'];
        }

        // Update the user via the auth service.
        $authResponse = $this->authService->updateProfile(new Request($userPayload));

        // Expect that the authResponse contains the updated user data.
        if (!isset($authResponse['data']['user'])) {
            throw new GraphQLException("User update failed.");
        }
        $user = $authResponse['data']['user'];

        $profilePayload = [
            "auth_user_id" => $user->id,
            "user_type" => "Business"
        ];

        $profileData = [];

        if (isset($args['profile_photo'])) {
            $photoUrl = $this->uploadFile($args['profile_photo'], true);
            $profilePayload['profile_picture'] = $photoUrl;
        }

        // Business-specific fields
        if (isset($args['business_name'])) {
            $profileData['business_name'] = $args['business_name'];
        }

        // Process document uploads if provided.
        if (isset($args['documents']) && is_array($args['documents'])) {
            $documentUrls = [];
            foreach ($args['documents'] as $doc) {
                // $doc is expected to be an UploadedFile instance.
                $url = $this->uploadFile($doc, false);
                $documentUrls[] = $url;
            }
            $profileData['documents'] = $documentUrls;
        }

        if (!empty($profileData)) {
            $profilePayload['profileData'] = $profileData;
        }

        $profileResponse = $this->userService->updateProfile(new Request($profilePayload));

        return $authResponse;
    }

    public function logout($_, array $args)
    {
        return $this->authService->logout();
    }

    public function deleteUser($_, array $args)
    {
        if (!isset($args['id'])) {
            throw new GraphQLException("Missing required field: id");
        }

        return $this->authService->deleteUser($args['id']);
    }

    public function createRole($_, array $args)
    {
        if (!isset($args['name']) || !isset($args['editable_name'])) {
            throw new GraphQLException("Missing required fields: name and/or editable_name");
        }

        $payload = [
            'name' => $args['name'],
            'editable_name' => $args['editable_name']
        ];

        if (isset($args['role_uuid'])) {
            $payload['role_uuid'] = $args['role_uuid'];
        }

        return $this->authService->createRole(new Request($payload));
    }

    public function updatePermissions($_, array $args)
    {
        if (!isset($args['role_uuid']) || !isset($args['permissions'])) {
            throw new GraphQLException("Missing required fields: role_uuid and/or permissions");
        }

        $payload = [
            'role_uuid' => $args['role_uuid'],
            'permissions' => $args['permissions']
        ];

        return $this->authService->updatePermissions(new Request($payload));
    }

    public function userCan($_, array $args)
    {
        if (!isset($args['permission_name'])) {
            throw new GraphQLException("Missing required field: permission_name");
        }

        return $this->authService->userCan($args['permission_name']);
    }
}
