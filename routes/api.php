<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\TmaController;
use App\Http\Controllers\Api\TmaAuthController;

Route::get('/test', function () {
    return response()->json(['status' => 'API works!']);
});

// Authentication
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/telegram', [AuthController::class, 'telegram']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    // Projects
    Route::apiResource('projects', ProjectController::class);
    Route::put('/projects/{project}/theme', [ProjectController::class, 'updateTheme']);
    Route::post('/projects/{project}/activate', [ProjectController::class, 'activate']);

    // Products
    Route::get('/projects/{project}/products', [ProductController::class, 'index']);
    Route::post('/projects/{project}/products', [ProductController::class, 'store']);
    Route::apiResource('products', ProductController::class)->except(['index', 'store']);

    // Файлы продуктов
    Route::post('/products/{product}/files', [ProductController::class, 'uploadFile']);
    Route::delete('/product-files/{fileId}', [ProductController::class, 'deleteFile']);


    // Orders
    Route::get('/projects/{project}/orders', [OrderController::class, 'index']);
    Route::post('/projects/{project}/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);

    // Customers
    Route::get('/projects/{project}/customers', [CustomerController::class, 'index']);
    Route::post('/projects/{project}/customers', [CustomerController::class, 'store']);
    Route::get('/customers/{customer}', [CustomerController::class, 'show']);
    Route::put('/customers/{customer}', [CustomerController::class, 'update']);
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy']);
});

// TMA Auth (public)
Route::post('/tma/auth', [TmaAuthController::class, 'auth']);

// TMA API (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tma/{project}/products', [TmaController::class, 'getProducts']);
    Route::get('/tma/{project}/config', [TmaController::class, 'config']);
    Route::post('/tma/{project}/orders', [TmaController::class, 'createOrder']);
    // === НОВЫЕ РОУТЫ ===
    Route::get('/courses/{course}/lessons', [TmaController::class, 'getCourseLessons']);
    Route::post('/products/{product}/download', [TmaController::class, 'downloadDigital']);
    Route::post('/quote-request', [TmaController::class, 'createQuoteRequest']); 
});