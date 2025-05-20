<?php

use App\GraphQL\Queries\WalletQuery;
use App\Services\WalletService;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIDocController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Microservice API Docs
Route::get("/service-docs/{serviceName}", [
    APIDocController::class,
    "getAPIDocHTML",
]);

Route::get("/service-swagger-doc/{serviceName}", [
    APIDocController::class,
    "getAPIDoc",
]);

Route::get("/", function () {
    $walletService = new WalletService();

    return $walletService->acceptPaymentSettlement("318bf9cc-74eb-55be-a98d-6ced29054162
");

    return view("welcome");
});
