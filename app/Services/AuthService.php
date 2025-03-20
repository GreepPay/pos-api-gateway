<?php

namespace App\Services;

use App\Datasource\NetworkHandler;
use Illuminate\Http\Request;

class AuthService
{
    protected $serviceUrl;
    protected $authNetwork;

    public function __construct(
        $useCache = true,
        $headers = [],
        $apiType = "graphql"
    ) {
        $this->serviceUrl = env(
            "AUTH_API",
            env("SERVICE_BROKER_URL") . "/broker/greep-auth/" . env("APP_STATE")
        );
        $this->authNetwork = new NetworkHandler(
            "",
            $this->serviceUrl,
            $useCache,
            $headers,
            $apiType
        );
    }
    public function addUser($request)
    {
        return $this->authNetwork->post("/v1/auth/users", $request->all());
    }

    public function loginUser($request)
    {
        return $this->authNetwork->post("/v1/auth/login", $request->all());
    }

    public function authUser()
    {
        return $this->authNetwork->get("/v1/auth/me");
    }

    public function resetOtp($request)
    {
        return $this->authNetwork->post("/v1/auth/reset-otp", $request->all());
    }

    public function verifyOtp($request)
    {
        return $this->authNetwork->post("/v1/auth/verify-otp", $request->all());
    }

    public function updatePassword($request)
    {
        return $this->authNetwork->post(
            "/v1/auth/update-password",
            $request->all()
        );
    }

    public function updateProfile($request)
    {
        return $this->authNetwork->post(
            "/v1/auth/update-profile",
            $request->all()
        );
    }

    public function logout()
    {
        return $this->authNetwork->post("/v1/auth/logout", []);
    }

    public function deleteUser($id)
    {
        return $this->authNetwork->delete("/v1/auth/users/{$id}");
    }

    public function createRole($request)
    {
        return $this->authNetwork->post("/v1/auth/roles", $request->all());
    }

    public function updatePermissions($request)
    {
        return $this->authNetwork->post(
            "/v1/auth/permissions",
            $request->all()
        );
    }

    public function userCan($permission_name)
    {
        return $this->authNetwork->get("/v1/auth/user-can/{$permission_name}");
    }
}
