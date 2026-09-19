<?php

use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\AttributeValueController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\BlogTagController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ContentBlockController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventImageController;
use App\Http\Controllers\Admin\FamousClientController;
use App\Http\Controllers\Admin\FaqCategoryController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\JobApplicationController;
use App\Http\Controllers\Admin\JobOpeningController;
use App\Http\Controllers\Admin\MediaCategoryController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductFileController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| Registered via bootstrap/app.php `then` callback, under the "web"
| middleware group. All routes here are prefixed "admin" and named
| "admin.*". Protected by the "auth" middleware + Spatie permissions.
*/

// Registers a resourceful set of admin CRUD routes with per-action
// spatie permission middleware, following the {module}.{action} convention.
$crud = function (string $uri, string $controller, string $module, array $only = ['index', 'create', 'store', 'edit', 'update', 'destroy']) {
    $registration = Route::resource($uri, $controller)->only($only);

    if (in_array('index', $only) || in_array('show', $only)) {
        $registration->middlewareFor(array_intersect(['index', 'show'], $only), "permission:{$module}.view");
    }
    if (in_array('create', $only) || in_array('store', $only)) {
        $registration->middlewareFor(array_intersect(['create', 'store'], $only), "permission:{$module}.create");
    }
    if (in_array('edit', $only) || in_array('update', $only)) {
        $registration->middlewareFor(array_intersect(['edit', 'update'], $only), "permission:{$module}.update");
    }
    if (in_array('destroy', $only)) {
        $registration->middlewareFor(['destroy'], "permission:{$module}.delete");
    }

    return $registration;
};

Route::prefix('admin')->name('admin.')->group(function () use ($crud) {

    // ---------------------------------------------------------------------
    // Guest routes: login + password reset
    //
    // Deliberately NOT wrapped in the generic "guest" middleware: the
    // public/customer stream registers its own route literally named
    // "home" (and "login") in routes/web.php, which is what Laravel's
    // default RedirectIfAuthenticated ("guest") middleware falls back to.
    // LoginController::create() already redirects an already-authenticated
    // admin to admin.dashboard itself, so this is unaffected.
    // ---------------------------------------------------------------------
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.attempt');

    Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.update');

    Route::post('logout', [LoginController::class, 'destroy'])->middleware(EnsureUserIsAdmin::class)->name('logout');

    // ---------------------------------------------------------------------
    // Authenticated admin routes
    //
    // EnsureUserIsAdmin performs its own Auth::check() (see that class for
    // why "auth" middleware is intentionally not used here too).
    // ---------------------------------------------------------------------
    Route::middleware([EnsureUserIsAdmin::class])->group(function () use ($crud) {

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', fn () => redirect()->route('admin.dashboard'))->name('home');

    // ---------------- Content: Pages ----------------
    $crud('pages', PageController::class, 'pages');

    // ---------------- Content: Homepage CMS ----------------
    $crud('hero-slides', HeroSlideController::class, 'homepage');
    Route::patch('hero-slides/{hero_slide}/toggle', [HeroSlideController::class, 'toggle'])->name('hero-slides.toggle')->middleware('permission:homepage.update');
    Route::patch('hero-slides/{hero_slide}/order', [HeroSlideController::class, 'updateOrder'])->name('hero-slides.order')->middleware('permission:homepage.update');

    $crud('content-blocks', ContentBlockController::class, 'homepage', ['index', 'edit', 'update']);

    $crud('famous-clients', FamousClientController::class, 'homepage');
    Route::patch('famous-clients/{famous_client}/toggle', [FamousClientController::class, 'toggle'])->name('famous-clients.toggle')->middleware('permission:homepage.update');

    $crud('testimonials', TestimonialController::class, 'testimonials');
    Route::patch('testimonials/{testimonial}/toggle', [TestimonialController::class, 'toggle'])->name('testimonials.toggle')->middleware('permission:testimonials.update');

    // ---------------- Content: Menus ----------------
    $crud('menus', MenuController::class, 'menus');
    Route::patch('menus/{menu}/toggle', [MenuController::class, 'toggle'])->name('menus.toggle')->middleware('permission:menus.update');

    // ---------------- Content: FAQs ----------------
    $crud('faq-categories', FaqCategoryController::class, 'faqs');
    Route::patch('faq-categories/{faq_category}/toggle', [FaqCategoryController::class, 'toggle'])->name('faq-categories.toggle')->middleware('permission:faqs.update');

    $crud('faqs', FaqController::class, 'faqs');
    Route::patch('faqs/{faq}/toggle', [FaqController::class, 'toggle'])->name('faqs.toggle')->middleware('permission:faqs.update');

    // ---------------- Content: Blogs ----------------
    $crud('blog-categories', BlogCategoryController::class, 'blogs');
    $crud('blog-tags', BlogTagController::class, 'blogs');
    $crud('blog-posts', BlogPostController::class, 'blogs');

    // ---------------- Content: Events ----------------
    $crud('events', EventController::class, 'events');
    Route::post('events/{event}/images', [EventImageController::class, 'store'])->name('events.images.store')->middleware('permission:events.update');
    Route::delete('event-images/{event_image}', [EventImageController::class, 'destroy'])->name('event-images.destroy')->middleware('permission:events.update');

    // ---------------- Content: Careers ----------------
    $crud('job-openings', JobOpeningController::class, 'careers');

    $crud('job-applications', JobApplicationController::class, 'careers', ['index', 'show', 'update', 'destroy']);

    // ---------------- Products ----------------
    $crud('product-categories', ProductCategoryController::class, 'categories');

    $crud('attributes', AttributeController::class, 'attributes');
    Route::post('attributes/{attribute}/values', [AttributeValueController::class, 'store'])->name('attribute-values.store')->middleware('permission:attributes.update');
    Route::put('attribute-values/{attribute_value}', [AttributeValueController::class, 'update'])->name('attribute-values.update')->middleware('permission:attributes.update');
    Route::delete('attribute-values/{attribute_value}', [AttributeValueController::class, 'destroy'])->name('attribute-values.destroy')->middleware('permission:attributes.delete');

    $crud('products', ProductController::class, 'products');

    Route::post('products/{product}/images', [ProductImageController::class, 'store'])->name('products.images.store')->middleware('permission:products.update');
    Route::delete('product-images/{product_image}', [ProductImageController::class, 'destroy'])->name('product-images.destroy')->middleware('permission:products.update');
    Route::patch('product-images/{product_image}/set-main', [ProductImageController::class, 'setMain'])->name('product-images.set-main')->middleware('permission:products.update');

    Route::post('products/{product}/files', [ProductFileController::class, 'store'])->name('products.files.store')->middleware('permission:product_files.create');
    Route::delete('product-files/{product_file}', [ProductFileController::class, 'destroy'])->name('product-files.destroy')->middleware('permission:product_files.delete');

    Route::put('products/{product}/attributes', [ProductController::class, 'syncAttributes'])->name('products.attributes.sync')->middleware('permission:products.update');

    Route::post('products/{product}/variants', [ProductVariantController::class, 'store'])->name('products.variants.store')->middleware('permission:products.update');
    Route::put('product-variants/{product_variant}', [ProductVariantController::class, 'update'])->name('product-variants.update')->middleware('permission:products.update');
    Route::delete('product-variants/{product_variant}', [ProductVariantController::class, 'destroy'])->name('product-variants.destroy')->middleware('permission:products.update');

    // ---------------- Commerce ----------------
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index')->middleware('permission:orders.view');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show')->middleware('permission:orders.view');
    Route::put('orders/{order}', [OrderController::class, 'update'])->name('orders.update')->middleware('permission:orders.update');
    Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy')->middleware('permission:orders.delete');

    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index')->middleware('permission:customers.view');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show')->middleware('permission:customers.view');
    Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit')->middleware('permission:customers.update');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update')->middleware('permission:customers.update');
    Route::patch('customers/{customer}/toggle', [CustomerController::class, 'toggle'])->name('customers.toggle')->middleware('permission:customers.update');
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy')->middleware('permission:customers.delete');

    // ---------------- Locations ----------------
    $crud('branches', BranchController::class, 'branches');
    Route::patch('branches/{branch}/toggle', [BranchController::class, 'toggle'])->name('branches.toggle')->middleware('permission:branches.update');

    // ---------------- Media ----------------
    // Cross-cutting picker endpoints: any authenticated admin may browse/upload
    // media from within another module's form, regardless of media.* permissions.
    Route::get('media/picker-data', [MediaController::class, 'pickerData'])->name('media.picker-data');
    Route::post('media/quick-upload', [MediaController::class, 'quickUpload'])->name('media.quick-upload');

    Route::get('media', [MediaController::class, 'index'])->name('media.index')->middleware('permission:media.view');
    Route::post('media', [MediaController::class, 'store'])->name('media.store')->middleware('permission:media.create');
    Route::get('media/{medium}/edit', [MediaController::class, 'edit'])->name('media.edit')->middleware('permission:media.update');
    Route::put('media/{medium}', [MediaController::class, 'update'])->name('media.update')->middleware('permission:media.update');
    Route::post('media/{medium}/replace', [MediaController::class, 'replace'])->name('media.replace')->middleware('permission:media.update');
    Route::delete('media/{medium}', [MediaController::class, 'destroy'])->name('media.destroy')->middleware('permission:media.delete');

    $crud('media-categories', MediaCategoryController::class, 'media');

    // ---------------- Communication ----------------
    Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index')->middleware('permission:contact_messages.view');
    Route::get('contact-messages/{contact_message}', [ContactMessageController::class, 'show'])->name('contact-messages.show')->middleware('permission:contact_messages.view');
    Route::put('contact-messages/{contact_message}', [ContactMessageController::class, 'update'])->name('contact-messages.update')->middleware('permission:contact_messages.update');
    Route::delete('contact-messages/{contact_message}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy')->middleware('permission:contact_messages.delete');

    // ---------------- Settings ----------------
    Route::middleware('permission:settings.view')->group(function () {
        Route::get('settings/general', [SettingController::class, 'general'])->name('settings.general');
        Route::get('settings/seo', [SettingController::class, 'seo'])->name('settings.seo');
        Route::get('settings/social', [SettingController::class, 'social'])->name('settings.social');
    });
    Route::middleware('permission:settings.update')->group(function () {
        Route::post('settings/general', [SettingController::class, 'updateGeneral'])->name('settings.general.update');
        Route::post('settings/seo', [SettingController::class, 'updateSeo'])->name('settings.seo.update');
        Route::post('settings/social', [SettingController::class, 'updateSocial'])->name('settings.social.update');
    });

    $crud('users', UserController::class, 'users');

    $crud('roles', RoleController::class, 'roles');
    });
});
