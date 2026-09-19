<?php

use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CareerController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\HomepageController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// -- Public content ---------------------------------------------------------
Route::get('/settings', [SettingController::class, 'index']);
Route::get('/menus', [MenuController::class, 'index']);
Route::get('/pages/{slug}', [PageController::class, 'show']);
Route::get('/homepage', [HomepageController::class, 'index']);

// -- Catalog ------------------------------------------------------------------
Route::get('/categories', [ProductCategoryController::class, 'index']);
Route::get('/categories/{slug}', [ProductCategoryController::class, 'show']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);

// -- Blog -----------------------------------------------------------------
Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blogs/{slug}', [BlogController::class, 'show']);

// -- Events -----------------------------------------------------------------
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{slug}', [EventController::class, 'show']);

// -- FAQs / Branches ---------------------------------------------------------
Route::get('/faqs', [FaqController::class, 'index']);
Route::get('/branches', [BranchController::class, 'index']);

// -- Contact ------------------------------------------------------------------
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:10,1');

// -- Careers ------------------------------------------------------------------
Route::get('/careers', [CareerController::class, 'index']);
Route::get('/careers/{slug}', [CareerController::class, 'show']);
Route::post('/careers/{slug}/apply', [CareerController::class, 'apply'])->middleware('throttle:10,1');

// -- Cart (guest-friendly via cart_token, or by authenticated user) ----------
Route::get('/cart', [CartController::class, 'show']);
Route::post('/cart', [CartController::class, 'store']);
Route::patch('/cart/{cartItemId}', [CartController::class, 'update']);
Route::delete('/cart/{cartItemId}', [CartController::class, 'destroyItem']);
Route::delete('/cart', [CartController::class, 'clear']);

// -- Orders -------------------------------------------------------------------
Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:10,1');
Route::get('/orders/{order_number}', [OrderController::class, 'show']);
