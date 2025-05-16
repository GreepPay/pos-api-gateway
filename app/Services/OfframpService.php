<?php

namespace App\Services;

use App\Datasource\NetworkHandler;

class OfframpService
{
    protected $serviceUrl;
    protected $offrampNetwork;

    /**
     * Constructor for the OfframpService class.
     *
     * @param bool $useCache Whether to use caching.
     * @param array $headers The headers to use.
     * @param string $apiType The type of API to use.
     */
    public function __construct(
        $useCache = false,
        array $headers = [],
        string $apiType = "graphql"
    ) {
        $this->serviceUrl = env(
            "OFFRAMP_API",
            env("SERVICE_BROKER_URL") .
                "/broker/greep-off-ramp/" .
                env("APP_STATE")
        );
        $this->offrampNetwork = new NetworkHandler(
            "",
            $this->serviceUrl,
            $useCache,
            $headers,
            $apiType
        );
    }

    /**
     * @param array $request
     * @return mixed
     */
    public function getKycStatus(array $request = [])
    {
        return $this->offrampNetwork->get("/v1/kyc/customer", $request);
    }

    /**
     * @param array $request
     * @return mixed
     */
    public function createKyc(array $request)
    {
        return $this->offrampNetwork->put("/v1/kyc/customer", $request);
    }

    /**
     * @param array $request
     * @return mixed
     */
    public function updateKyc(array $request)
    {
        return $this->offrampNetwork->post("/v1/kyc/customer", $request);
    }

    /**
     * @param array $request
     * @return mixed
     */
    public function deleteCustomer(array $request = [])
    {
        return $this->offrampNetwork->delete("/v1/kyc/customer", $request);
    }

    /**
     * @param array $request
     * @return mixed
     */
    public function getSep38Info(array $request)
    {
        return $this->offrampNetwork->post("/v1/exchange/info", $request);
    }

    /**
     * @param array $request
     * @return mixed
     */
    public function getSep38Price(array $request)
    {
        return $this->offrampNetwork->post("/v1/exchange/price", $request);
    }

    /**
     * @param array $request
     * @return mixed
     */
    public function createSep38Quote(array $request)
    {
        return $this->offrampNetwork->post("/v1/exchange/quote", $request);
    }

    /**
     * @param array $request
     * @return mixed
     */
    public function getSep38Quote(array $request)
    {
        return $this->offrampNetwork->post("/v1/exchange/get-qoute", $request);
    }

    /**
     * @param array $request
     * @return mixed
     */
    public function getAnchorInfo(array $request)
    {
        return $this->offrampNetwork->post("/v1/withdrawl/info", $request);
    }

    /**
     * @param array $request
     * @return mixed
     */
    public function withdraw(array $request)
    {
        return $this->offrampNetwork->post("/v1/withdrawl/withdraw", $request);
    }

    /**
     * @param array $request
     * @return mixed
     */
    public function transaction(array $request)
    {
        return $this->offrampNetwork->post(
            "/v1/withdrawl/transaction",
            $request
        );
    }

    /**
     * @param array $request
     * @return mixed
     */
    public function transactions(array $request)
    {
        return $this->offrampNetwork->post(
            "/v1/withdrawl/transactions",
            $request
        );
    }

    /**
     * @param array $request
     * @return mixed
     */
    public function withdrawExchange(array $request)
    {
        return $this->offrampNetwork->post(
            "/v1/withdrawl/withdraw-exchange",
            $request
        );
    }

    /**
     * @param array $request
     * @return mixed
     */
    public function getSep31Info(array $request)
    {
        return $this->offrampNetwork->post(
            "/v1/crossborderpayment/info",
            $request
        );
    }

    /**
     * @param array $request
     * @return mixed
     */
    public function createSep31Transaction(array $request)
    {
        return $this->offrampNetwork->post(
            "/v1/crossborderpayment/transaction",
            $request
        );
    }

    /**
     * @param array $request
     * @return mixed
     */
    public function setSep31TransactionCallback(array $request)
    {
        return $this->offrampNetwork->post(
            "/v1/crossborderpayment/set-callback",
            $request
        );
    }

    /**
     * @param array $request
     * @return mixed
     */
    public function getSep31Transaction(array $request)
    {
        return $this->offrampNetwork->post(
            "/v1/crossborderpayment/get-transaction",
            $request
        );
    }

    /**
     * @param array $request
     * @return mixed
     */
    public function updateSep31Transaction(array $request)
    {
        return $this->offrampNetwork->post(
            "/v1/crossborderpayment/update-transaction",
            $request
        );
    }
}
