<?php

namespace App\Services;

use App\Datasource\NetworkHandler;
use Illuminate\Http\Request;

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

    public function submitVerification($request)
    {
        return $this->userNetwork->post("/v1/verification", $request->all());
    }

    public function approveVerification($request)
    {
        return $this->userNetwork->post("/v1/verification/approve", $request->all());
    }
}
