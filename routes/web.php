<?php

use App\Http\Controllers\Account\DashboardController as AccountDashboardController;
use App\Http\Controllers\Account\OrderController as AccountOrderController;
use App\Http\Controllers\Account\ProfileController as AccountProfileController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DetailController as AdminDetailController;
use App\Http\Controllers\Admin\HomeSectionController as AdminHomeSectionController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SearchController as AdminSearchController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaystackController;
use App\Http\Controllers\PaystackWebhookController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product:slug}', [ShopController::class, 'show'])->name('shop.show');
Route::get('/pages/{page:slug}', [PageController::class, 'show'])->name('pages.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

Route::middleware(['auth', 'cart.not_empty'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
});

Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])
    ->middleware('auth')
    ->name('checkout.success');

Route::middleware('auth')->group(function () {
    Route::get('/paystack/initialize/{order}', [PaystackController::class, 'initialize'])->name('paystack.initialize');
    Route::get('/paystack/callback', [PaystackController::class, 'callback'])->name('paystack.callback');
});

Route::post('/paystack/webhook', PaystackWebhookController::class)->name('paystack.webhook');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', AccountDashboardController::class)->name('dashboard');
    Route::get('/orders', [AccountOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AccountOrderController::class, 'show'])->name('orders.show');
    Route::get('/profile', [AccountProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [AccountProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [AccountProfileController::class, 'updatePassword'])->name('profile.password');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::middleware('admin.permission:dashboard')->group(function () {
            Route::get('/', AdminDashboardController::class)->name('dashboard');
        });

        Route::get('search', AdminSearchController::class)->name('search');

        Route::middleware('admin.permission:products')->group(function () {
            Route::get('details/products/{product}', [AdminDetailController::class, 'product'])->name('details.products');
            Route::resource('products', AdminProductController::class)->except(['show']);
        });

        Route::middleware('admin.permission:categories')->group(function () {
            Route::get('details/categories/{category}', [AdminDetailController::class, 'category'])->name('details.categories');
            Route::resource('categories', AdminCategoryController::class)->except(['show']);
        });

        Route::middleware('admin.permission:orders')->group(function () {
            Route::get('details/orders/{order}', [AdminDetailController::class, 'order'])->name('details.orders');
            Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
            Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
            Route::delete('orders/{order}', [AdminOrderController::class, 'destroy'])->name('orders.destroy');
            Route::get('notifications/{notification}', [AdminNotificationController::class, 'show'])->name('notifications.show');
            Route::delete('notifications/{notification}', [AdminNotificationController::class, 'destroy'])->name('notifications.destroy');
            Route::delete('notifications', [AdminNotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
        });

        Route::middleware('admin.permission:customers')->group(function () {
            Route::get('details/customers/{user}', [AdminDetailController::class, 'customer'])->name('details.customers');
            Route::get('customers', [AdminCustomerController::class, 'index'])->name('customers.index');
            Route::patch('customers/{user}/toggle-status', [AdminCustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
        });

        Route::middleware('admin.permission:users')->group(function () {
            Route::resource('users', AdminUserController::class)->except(['show']);
        });

        Route::middleware('admin.permission:content')->group(function () {
            Route::get('homepage-sections', [AdminHomeSectionController::class, 'index'])->name('home-sections.index');
            Route::get('homepage-sections/{home_section}/edit', [AdminHomeSectionController::class, 'edit'])->name('home-sections.edit');
            Route::put('homepage-sections/{home_section}', [AdminHomeSectionController::class, 'update'])->name('home-sections.update');
            Route::resource('testimonials', AdminTestimonialController::class)->except(['show']);
            Route::get('pages', [AdminPageController::class, 'index'])->name('pages.index');
            Route::get('pages/{page}/edit', [AdminPageController::class, 'edit'])->name('pages.edit');
            Route::put('pages/{page}', [AdminPageController::class, 'update'])->name('pages.update');
        });

        Route::middleware('admin.permission:reports')->group(function () {
            Route::get('reports', [AdminReportController::class, 'index'])->name('reports.index');
            Route::get('reports/export', [AdminReportController::class, 'export'])->name('reports.export');
        });
    });
