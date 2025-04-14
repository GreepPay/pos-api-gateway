<?php

namespace App\Providers;

use App\Models\Auth\User;
use App\Services\AuthService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\RequestGuard;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\GraphQLException;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        Auth::extend("custom", function ($app, $name, array $config) {
            return new RequestGuard(
                function ($request) {
                    $token = $request->bearerToken();

                    if (!$token) {
                        return null;
                    }

                    $authService = new AuthService();
                    $response = $authService->authUser();

                    if (!$response || empty($response["data"])) {
                        return null;
                    }

                    $user = User::query()
                        ->where("id", $response["data"]["id"])
                        ->with("profile")
                        ->first();

                    if ($user->profile->user_type != "Business") {
                        throw new GraphQlException(
                            "Unauthorized. Please use the {$user->profile->user_type} app instead"
                        );
                    }

                    return $user;
                },
                $app["request"],
                $app["auth"]->createUserProvider($config["provider"])
            );
        });
    }
}
