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
            "auth",
            $this->serviceUrl,
            $useCache,
            $headers,
            $apiType
        );
    }

    public function addUser($request)
    {
        return $this->authNetwork->post("/create-user", $request);
    }

    public function loginUser($request)
    {
        return $this->authNetwork->post("/v1/auth/login", $request->all());
    }
}
