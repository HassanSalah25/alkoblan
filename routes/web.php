<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Web\AboutController;
use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\CareerController;
use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\CompanyProfileController;
use App\Http\Controllers\Web\ContactController;
use App\Http\Controllers\Web\EventController;
use App\Http\Controllers\Web\FaqController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\NewsletterController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\PipePlannerController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\ShopController;
use App\Http\Controllers\Web\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Storefront Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/company-profile', [CompanyProfileController::class, 'index'])->name('company-profile');

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{slug}', [EventController::class, 'show'])->name('events.show');

Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');

Route::get('/careers', [CareerController::class, 'index'])->name('careers.index');
Route::get('/careers/{slug}', [CareerController::class, 'show'])->name('careers.show');
Route::post('/careers/{slug}/apply', [CareerController::class, 'apply'])->name('careers.apply')->middleware('throttle:10,1');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store')->middleware('throttle:10,1');

Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe')->middleware('throttle:10,1');

Route::get('/pages/{slug}', [PageController::class, 'show'])->name('pages.show');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['ar', 'en'])) {
        session(['locale' => $locale]);
    }

    return back();
})->name('lang.switch');

// ---------------------------------------------------------------------
// AI Pipe Planner (guest-friendly; ownership enforced in the controller)
// ---------------------------------------------------------------------
Route::get('/pipe-planner', [PipePlannerController::class, 'index'])->name('pipe-planner.index');
Route::prefix('pipe-planner')->name('pipe-planner.')->middleware('throttle:pipe-planner')->group(function () {
    Route::get('projects', [PipePlannerController::class, 'projects'])->name('projects.index');
    Route::post('projects', [PipePlannerController::class, 'store'])->name('projects.store')->middleware('throttle:pipe-planner-create');
    Route::get('projects/{project}', [PipePlannerController::class, 'show'])->name('projects.show');
    Route::patch('projects/{project}', [PipePlannerController::class, 'update'])->name('projects.update');
    Route::delete('projects/{project}', [PipePlannerController::class, 'destroy'])->name('projects.destroy');

    Route::post('projects/{project}/images', [PipePlannerController::class, 'uploadImage'])->name('images.store')->middleware('throttle:pipe-planner-upload');
    Route::patch('projects/{project}/images/{image}', [PipePlannerController::class, 'updateImage'])->name('images.update');
    Route::delete('projects/{project}/images/{image}', [PipePlannerController::class, 'deleteImage'])->name('images.destroy');
    Route::get('images/{image}', [PipePlannerController::class, 'showImage'])->name('images.show')->middleware('signed');

    Route::post('projects/{project}/analyses', [PipePlannerController::class, 'analyze'])->name('analyses.store')->middleware('throttle:pipe-planner-analysis');
    Route::get('projects/{project}/analyses/{version}', [PipePlannerController::class, 'analysis'])->name('analyses.show')->whereNumber('version');
    Route::patch('projects/{project}/analyses/{version}/waste', [PipePlannerController::class, 'updateWaste'])->name('analyses.waste')->whereNumber('version');
    Route::get('projects/{project}/analyses/{version}/report', [PipePlannerController::class, 'report'])->name('analyses.report')->whereNumber('version');
    Route::post('projects/{project}/analyses/{version}/quote', [PipePlannerController::class, 'requestQuote'])->name('analyses.quote')->whereNumber('version')->middleware('throttle:pipe-planner-quote');
});

// ---------------------------------------------------------------------
// Cart & Checkout
// ---------------------------------------------------------------------
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{item}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/success/{orderNumber}', [CheckoutController::class, 'success'])->name('checkout.success');

// ---------------------------------------------------------------------
// Customer Auth (separate from /admin auth)
// ---------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.attempt');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/account/orders', [AccountController::class, 'orders'])->name('account.orders');
});
