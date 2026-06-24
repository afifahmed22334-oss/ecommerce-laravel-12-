<?php

use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\BrandsController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\admin\CouponController;
use App\Http\Controllers\Admin\OrdersController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\frontend\CartController;
use App\Http\Controllers\frontend\CheckoutController;
use App\Http\Controllers\frontend\ShopController;
use App\Http\Controllers\frontend\UserOrdersController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SlidesController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AuthAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;



Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::resource('shop', ShopController::class);
Route::get('/contact', [HomeController::class, 'contactPage'])->name('contact.index');
Route::post('/contact/contact_store', [ContactController::class, 'store'])->name('contact_store');
Route::get('/search', [HomeController::class, 'search'])->name('home.search');


Route::middleware(['auth'])->group(function () {

    Route::get('/account-dashboard', [UserController::class, 'index'])->name('user.index');
    Route::put('/cart/increase-quantity/{rowId}', [CartController::class, 'increase_cart_quantity'])->name('cart.qty.increase');
    Route::put('/cart/decrease-quantity/{rowId}', [CartController::class, 'decrease_cart_quantity'])->name('cart.qty.decrease');
    Route::delete('/cart/clear', [CartController::class, 'empty_cart'])->name('cart.allClear');

    Route::resource('cart', CartController::class);

    Route::post('/cart/apply-coupon', [CartController::class, 'apply_coupon_code'])->name('cart.coupon.apply');
    Route::delete('/cart/coupon/remove', [CartController::class, 'removeCoupon'])->name('cart.couponRemove');

    Route::resource('checkout', CheckoutController::class);
    Route::get('/order/confirmation', [CheckoutController::class, 'order_confirmation'])->name('order_confirmation');
    Route::resource('user_orders', UserOrdersController::class);

});

Route::middleware(['auth', AuthAdmin::class])->group(function () {

    Route::prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.index');
        Route::resource('brands', BrandsController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('products', ProductController::class);
        Route::resource('coupons', CouponController::class);
        Route::resource('orders', OrdersController::class);
        Route::resource('sliders', SlidesController::class);
        Route::resource('comments', ContactController::class);
        Route::resource('users', AdminUsersController::class);

    });

});
