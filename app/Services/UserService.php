<?php

namespace App\Services;

use App\Datasource\NetworkHandler;

class UserService
{
    protected $serviceUrl;
    protected NetworkHandler $userNetwork;

    public function __construct(
        bool $useCache = true,
        array $headers = [],
        string $apiType = "graphql"
    ) {
        $this->serviceUrl = env(
            "USER_API",
            env("SERVICE_BROKER_URL") . "/broker/greep-user/" . env("APP_STATE")
        );

        $this->userNetwork = new NetworkHandler(
            "",
            $this->serviceUrl,
            $useCache,
            $headers,
            $apiType
        );
    }

    public function createProfile($request)
    {
        return $this->userNetwork->post("/v1/profiles", $request->all());
    }

    public function updateProfile($request)
    {
        return $this->userNetwork->put("/v1/profiles", $request->all());
    }

    public function deleteProfile($request)
    {
        return $this->userNetwork->delete("/v1/profiles", $request->all());
    }
}
