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
    public function signIn($_, array $args)
    {
        // authenticate user
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
        $requiredFields = ['firstName', 'lastName', 'email', 'phoneNumber', 'password', 'role'];
        foreach ($requiredFields as $field) {
            if (!isset($args[$field])) {
                throw new GraphQLException("Missing required field: {$field}");
            }
        }

        $payload = [
            'firstName' => $args['firstName'],
            'lastName' => $args['lastName'],
            'email' => $args['email'],
            'phoneNumber' => $args['phoneNumber'],
            'password' => $args['password'],
            'role' => $args['role'],
            'ssoId' => $args['ssoId'],
            'otp' => $args['otp'],
            'isSso' => $args['isSso'],
            'ignoreError' => $args['ignoreError'],
        ];

        // Create user
        $authResponse = $this->authService->addUser(new Request($payload));

        return $authResponse;
    }



}
