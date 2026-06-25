<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Account\DashboardController as AccountDashboardController;
use App\Http\Controllers\Account\OrderController as AccountOrderController;
use App\Http\Controllers\Account\ProfileController as AccountProfileController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DetailController as AdminDetailController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\HomeSectionController as AdminHomeSectionController;
use App\Http\Controllers\Admin\MaintenanceModeController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\OrderInvoiceBulkController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SearchController as AdminSearchController;
use App\Http\Controllers\Admin\ShippingRegionController;
use App\Http\Controllers\Admin\StoreSettingController;
use App\Http\Controllers\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaystackController;
use App\Http\Controllers\PaystackWebhookController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::post('/paystack/webhook', PaystackWebhookController::class)->name('paystack.webhook');

Route::middleware('storefront.maintenance')->group(function () {
    Route::get('/', HomeController::class)->name('home');
    Route::get('/new-arrivals', [HomeController::class, 'newArrivals'])->name('home.new-arrivals');
    Route::get('/about', AboutController::class)->name('about');
    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
    Route::get('/shop/{product:slug}', [ShopController::class, 'show'])->name('shop.show');
    Route::get('/pages/{page:slug}', [PageController::class, 'show'])->name('pages.show');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

    Route::middleware('cart.not_empty')->group(function () {
        Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::post('/checkout/coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon.apply');
        Route::delete('/checkout/coupon', [CheckoutController::class, 'removeCoupon'])->name('checkout.coupon.remove');
    });

    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])
        ->name('checkout.success');

    Route::get('/paystack/initialize/{order}', [PaystackController::class, 'initialize'])->name('paystack.initialize');
    Route::get('/paystack/callback', [PaystackController::class, 'callback'])->name('paystack.callback');

    Route::get('/orders/{order}/invoice', [InvoiceController::class, 'show'])->name('orders.invoice');
    Route::get('/orders/{order}/invoice/pdf', [InvoiceController::class, 'pdf'])->name('orders.invoice.pdf');

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

    Route::middleware('auth')->group(function () {
        Route::post('/favorites/{product}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

        Route::prefix('account')->name('account.')->group(function () {
            Route::get('/', AccountDashboardController::class)->name('dashboard');
            Route::get('/orders', [AccountOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [AccountOrderController::class, 'show'])->name('orders.show');
            Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
            Route::get('/profile', [AccountProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [AccountProfileController::class, 'update'])->name('profile.update');
            Route::put('/profile/password', [AccountProfileController::class, 'updatePassword'])->name('profile.password');
        });
    });
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
            Route::resource('products', AdminProductController::class)->except(['show', 'update']);
            Route::match(['put', 'patch', 'post'], 'products/{product}', [AdminProductController::class, 'update'])->name('products.update');
        });

        Route::middleware('admin.permission:categories')->group(function () {
            Route::get('details/categories/{category}', [AdminDetailController::class, 'category'])->name('details.categories');
            Route::get('categories/navbar', [AdminCategoryController::class, 'navbar'])->name('categories.navbar');
            Route::put('categories/navbar', [AdminCategoryController::class, 'updateNavbar'])->name('categories.navbar.update');
            Route::get('categories/shop', [AdminCategoryController::class, 'shop'])->name('categories.shop');
            Route::put('categories/shop', [AdminCategoryController::class, 'updateShop'])->name('categories.shop.update');
            Route::resource('categories', AdminCategoryController::class)->except(['show']);
        });

        Route::middleware('admin.permission:orders')->group(function () {
            Route::get('details/orders/{order}', [AdminDetailController::class, 'order'])->name('details.orders');
            Route::get('orders/{order}/invoice', [InvoiceController::class, 'show'])->name('orders.invoice');
            Route::get('orders/{order}/invoice/pdf', [InvoiceController::class, 'pdf'])->name('orders.invoice.pdf');
            Route::post('orders/invoices/export', [OrderInvoiceBulkController::class, 'export'])->name('orders.invoices.export');
            Route::post('orders/invoices/print', [OrderInvoiceBulkController::class, 'print'])->name('orders.invoices.print');
            Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
            Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
            Route::delete('orders/{order}', [AdminOrderController::class, 'destroy'])->name('orders.destroy');
            Route::resource('coupons', AdminCouponController::class)->except(['show']);
            Route::get('notifications/{notification}', [AdminNotificationController::class, 'show'])->name('notifications.show');
            Route::delete('notifications/{notification}', [AdminNotificationController::class, 'destroy'])->name('notifications.destroy');
            Route::delete('notifications', [AdminNotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
        });

        Route::middleware('admin.permission:customers')->group(function () {
            Route::get('details/customers/{user}', [AdminDetailController::class, 'customer'])->name('details.customers');
            Route::get('customers', [AdminCustomerController::class, 'index'])->name('customers.index');
            Route::patch('customers/{user}/toggle-status', [AdminCustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
            Route::delete('customers/{user}', [AdminCustomerController::class, 'destroy'])->name('customers.destroy');
        });

        Route::middleware('admin.permission:users')->group(function () {
            Route::resource('users', AdminUserController::class)->except(['show']);
        });

        Route::middleware('admin.permission:content')->group(function () {
            Route::patch('maintenance-mode', [MaintenanceModeController::class, 'update'])->name('maintenance.update');
            Route::get('homepage-sections', [AdminHomeSectionController::class, 'index'])->name('home-sections.index');
            Route::get('homepage-sections/{home_section}/edit', [AdminHomeSectionController::class, 'edit'])->name('home-sections.edit');
            Route::put('homepage-sections/{home_section}', [AdminHomeSectionController::class, 'update'])->name('home-sections.update');
            Route::resource('testimonials', AdminTestimonialController::class)->except(['show']);
            Route::get('pages', [AdminPageController::class, 'index'])->name('pages.index');
            Route::get('pages/{page}/edit', [AdminPageController::class, 'edit'])->name('pages.edit');
            Route::put('pages/{page}', [AdminPageController::class, 'update'])->name('pages.update');
            Route::get('email-templates', [EmailTemplateController::class, 'index'])->name('email-templates.index');
            Route::get('email-templates/{email_template}/edit', [EmailTemplateController::class, 'edit'])->name('email-templates.edit');
            Route::put('email-templates/{email_template}', [EmailTemplateController::class, 'update'])->name('email-templates.update');
            Route::get('email-templates/{email_template}/preview', [EmailTemplateController::class, 'preview'])->name('email-templates.preview');
            Route::post('email-templates/{email_template}/send-test', [EmailTemplateController::class, 'sendTest'])->name('email-templates.send-test');
            Route::get('store-settings', [StoreSettingController::class, 'edit'])->name('store-settings.edit');
            Route::put('store-settings', [StoreSettingController::class, 'update'])->name('store-settings.update');
            Route::resource('shipping-regions', ShippingRegionController::class)->except(['show']);
        });

        Route::middleware('admin.permission:reports')->group(function () {
            Route::get('reports', [AdminReportController::class, 'index'])->name('reports.index');
            Route::get('reports/export', [AdminReportController::class, 'export'])->name('reports.export');
        });
    });
