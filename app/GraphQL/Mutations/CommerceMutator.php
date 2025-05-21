<?php

namespace App\GraphQL\Mutations;

use App\Services\CommerceService;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopMutator
{
    use FileUploadTrait;

    protected CommerceService $commerceService;

    public function __construct()
    {
        $this->commerceService = new CommerceService();
    }

    /**
     * Setup shop with cover photo and weekly schedule
     *
     * @param mixed $_
     * @param array $args
     * @return bool
     */
    public function setupShop($_, array $args): bool
    {
        $input = $args['input'];
        
        // Upload cover image
        $request = new Request();
        $request->files->set('attachment', $input['cover_image']);
        $coverImageUrl = $this->uploadFile($request, false);
        
        // Prepare schedule data
        $schedule = [];
        foreach ($input['schedule'] as $daySchedule) {
            $schedule[] = [
                'day' => $daySchedule['day'],
                'open' => $daySchedule['open'],
                'opening_time' => $daySchedule['from'] ?? null,
                'closing_time' => $daySchedule['to'] ?? null,
            ];
        }
        
        // Call commerce service
        $response = $this->commerceService->createShop([
            'user_id' => Auth::id(),
            'cover_image_url' => $coverImageUrl,
            'schedule' => $schedule,
        ]);
        
        return $response['success'] ?? false;
    }
}

class ProductMutator
{
    use FileUploadTrait;

    protected CommerceService $commerceService;

    public function __construct()
    {
        $this->commerceService = new CommerceService();
    }

    /**
     * Add a new product with variants and inventory
     *
     * @param mixed $_
     * @param array $args
     * @return bool
     */
    public function addProduct($_, array $args): bool
    {
        $input = $args['input'];
        
        // Upload cover photo if provided
        $coverPhotoUrl = null;
        if (isset($input['cover_photo'])) {
            $request = new Request();
            $request->files->set('attachment', $input['cover_photo']);
            $coverPhotoUrl = $this->uploadFile($request, false);
        }
        
        // Prepare variants data
        $variants = [];
        foreach ($input['variants'] as $variant) {
            $variants[] = [
                'type' => $variant['type'],
                'value' => $variant['value'],
                'attribute' => $variant['attribute'] ?? null,
            ];
        }
        
        // Prepare prices data
        $prices = [];
        foreach ($input['prices'] as $price) {
            $prices[] = [
                'price' => $price['price'],
                'currency' => $price['currency'],
            ];
        }
        
        // Call commerce service
        $response = $this->commerceService->createProduct([
            'product_uuid' => $input['product_uuid'],
            'user_id' => Auth::id(),
            'name' => $input['name'],
            'category' => $input['category'],
            'description' => $input['description'],
            'cover_photo_url' => $coverPhotoUrl,
            'variants' => $variants,
            'stock' => $input['stock'],
            'prices' => $prices,
        ]);
        
        return $response['success'] ?? false;
    }
}