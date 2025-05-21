<?php

namespace App\Services;

use App\Datasource\NetworkHandler;

class CommerceService
{
    protected $serviceUrl;
    protected NetworkHandler $commerceNetwork;

    /**
     * construct
     *
     * @param bool $useCache Whether to use caching.
     * @param array $headers Headers to be sent with the request.
     * @param string $apiType Type of API to be used.
     * @return mixed
     */
    public function __construct(
        bool $useCache = true,
        array $headers = [],
        string $apiType = "graphql"
    ) {
        $this->serviceUrl = env(
            "COMMERCE_API",
            env("SERVICE_BROKER_URL") . "/broker/greep-commerce/" . env("APP_STATE")
        );

        $this->commerceNetwork = new NetworkHandler(
            "",
            $this->serviceUrl,
            $useCache,
            $headers,
            $apiType
        );
    }

    // Products
    public function getAllProducts(?string $category = null, ?string $type = null): mixed
     {
         $queryParams = [];
         if ($category) {
             $queryParams['category'] = $category;
         }
         if ($type) {
             $queryParams['type'] = $type;
         }
         
         return $this->commerceNetwork->get("/v1/products", $queryParams);
     }

    public function createProduct(array $request): mixed
    {
        return $this->commerceNetwork->post("/v1/products", $request);
    }

    public function getProductById(string $id): mixed
    {
        return $this->commerceNetwork->get("/v1/products/{$id}");
    }

    public function updateProduct(string $id, array $request): mixed
    {
        return $this->commerceNetwork->put("/v1/products/{$id}", $request);
    }

    public function deleteProduct(string $id): mixed
    {
        return $this->commerceNetwork->delete("/v1/products/{$id}");
    }

    public function adjustInventory(string $id, array $request): mixed
    {
        return $this->commerceNetwork->patch("/v1/products/{$id}/inventory", $request);
    }

    public function checkProductAvailability(string $id): mixed
    {
        return $this->commerceNetwork->get("/v1/products/{$id}/availability");
    }

    public function getProductTypes(): mixed
    {
        return $this->commerceNetwork->get("/v1/product-types");
    }

    // Sales
    public function processSale(array $request): mixed
    {
        return $this->commerceNetwork->post("/v1/sales/process", $request);
    }

    public function initiateRefund(string $saleId, array $request): mixed
    {
        return $this->commerceNetwork->post("/v1/sales/{$saleId}/refund", $request);
    }

    public function applyDiscount(array $request): mixed
    {
        return $this->commerceNetwork->post("/v1/discounts/apply", $request);
    }

    public function calculateTaxes(array $request): mixed
    {
        return $this->commerceNetwork->post("/v1/taxes/calculate", $request);
    }

    public function getAllSales(
          ?string $customerId = null,
          ?string $status = null,
          ?string $fromDate = null,
          ?string $toDate = null
      ): mixed {
          $queryParams = [];
          if ($customerId) {
              $queryParams['customerId'] = $customerId;
          }
          if ($status) {
              $queryParams['status'] = $status;
          }
          if ($fromDate) {
              $queryParams['fromDate'] = $fromDate;
          }
          if ($toDate) {
              $queryParams['toDate'] = $toDate;
          }
          
          return $this->commerceNetwork->get("/v1/sales", $queryParams);
      }

      public function getCustomerSalesHistory(
          string $customerId,
          ?string $status = null,
          ?string $fromDate = null,
          ?string $toDate = null
      ): mixed {
          $queryParams = [];
          if ($status) {
              $queryParams['status'] = $status;
          }
          if ($fromDate) {
              $queryParams['fromDate'] = $fromDate;
          }
          if ($toDate) {
              $queryParams['toDate'] = $toDate;
          }
          
          return $this->commerceNetwork->get("/v1/customers/{$customerId}/sales", $queryParams);
      }

    // Orders
    public function createOrder(array $request): mixed
    {
        return $this->commerceNetwork->post("/v1/orders", $request);
    }

    public function getAllOrders(
        ?string $status = null,
        ?string $fromDate = null,
        ?string $toDate = null
    ): mixed {
        $query = http_build_query(array_filter([
            'status' => $status,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ]));
    
        return $this->commerceNetwork->get("/v1/orders" . ($query ? "?$query" : ""));
    }


    public function getOrderById(string $id): mixed
    {
        return $this->commerceNetwork->get("/v1/orders/{$id}");
    }

    
    public function getCustomerOrders(
        string $customerId,
        ?string $status = null,
        ?string $fromDate = null,
        ?string $toDate = null
    ): mixed {
        $query = http_build_query(array_filter([
            'status' => $status,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ]));
    
        return $this->commerceNetwork->get("/v1/customers/{$customerId}/orders" . ($query ? "?$query" : ""));
    }


    public function updateOrderStatus(string $id, array $request): mixed
    {
        return $this->commerceNetwork->patch("/v1/orders/{$id}/status", $request);
    }

    public function cancelOrder(string $id): mixed
    {
        return $this->commerceNetwork->post("/v1/orders/{$id}/cancel");
    }

    // Deliveries
    public function createDelivery(array $request): mixed
    {
        return $this->commerceNetwork->post("/v1/deliveries", $request);
    }

    public function updateDeliveryStatus(string $id, array $request): mixed
    {
        return $this->commerceNetwork->patch("/v1/deliveries/{$id}/status", $request);
    }

    public function getDeliveryDetails(string $id): mixed
    {
        return $this->commerceNetwork->get("/v1/deliveries/{$id}");
    }

    public function getOrderDeliveries(string $orderId): mixed
    {
        return $this->commerceNetwork->get("/v1/orders/{$orderId}/deliveries");
    }

    public function updateTrackingInfo(string $id, array $request): mixed
    {
        return $this->commerceNetwork->post("/v1/deliveries/{$id}/tracking", $request);
    }

    // Tickets
    public function createTicket(array $request): mixed
    {
        return $this->commerceNetwork->post("/vl/tickets", $request);
    }

    public function updateTicket(string $id, array $request): mixed
    {
        return $this->commerceNetwork->patch("/vl/tickets/{$id}", $request);
    }
}