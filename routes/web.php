<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiDocController;

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
    ApiDocController::class,
    "getAPIDocHTML",
]);

Route::get("/service-swagger-doc/{serviceName}", [
    ApiDocController::class,
    "getAPIDoc",
]);

Route::get("/", function () {
    return view("welcome");
});
