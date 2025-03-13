<?php

namespace App\Services;

use App\Datasource\NetworkHandler;

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
}
