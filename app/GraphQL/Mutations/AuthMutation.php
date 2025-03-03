<?php

namespace App\GraphQL\Mutations;
use Illuminate\Http\Request;

final class AuthMutation
{
    public function signIn($_, array $args)
    {
        // authenticate user
        $authResponse = $this->authService->loginUser(
            new Request([
                "password" => $args["password"],
                "email" => $args["email"],
            ])
        );

        $authResponse = $authResponse;

        return [
            "token" => $authResponse["access_token"],
            "user" => User::where("id", $authResponse["user"]["id"])->first(),
        ];
    }
}
