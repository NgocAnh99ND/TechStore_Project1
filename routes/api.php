<?php

use App\Models\ProductColor;
use Illuminate\Http\Request;
use App\Models\ProductCapacity;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\ShopController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\TrashedController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\CatalogueController;
use App\Http\Controllers\Admin\PaymentMethodControlller;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Admin\StatusOrderController;
use App\Http\Controllers\Client\ClientUserController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use App\Http\Controllers\Admin\StatusPaymentController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Client\CommentController as ClientCommentController;
use App\Http\Controllers\Client\OrderController as ClientOrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Auth
    Route::post("register", [RegisterController::class, 'register']);
    Route::post("login", action: [LoginController::class, 'login']);
    Route::post("logout", [LogoutController::class, 'logout'])->middleware("auth:sanctum");
    // Route::post('/forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
    // Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);
    // Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
    //     ->name('password.reset');

// Admin
    Route::middleware(['auth:sanctum', 'checkAdminMiddleware'])->group(function () {
        Route::apiResource("admin/user", UserController::class);
        Route::apiResource("admin/catalogue", CatalogueController::class);
        Route::apiResource("admin/producCapacity", ProductCapacity::class);
        Route::apiResource("admin/productColor", ProductColor::class);
        Route::apiResource("admin/banner", BannerController::class);
        Route::apiResource("admin/statusOrder", StatusOrderController::class);
        Route::apiResource("admin/statusPayment", StatusPaymentController::class);
        // Route::apiResource("admin/paymentMethod", PaymentMethodControlller::class);
        // Route::apiResource('admin/products', ProductController::class);


        // Comment
        Route::get('/admin/comments', [CommentController::class, 'index']); 
        Route::put('/admin/comments/approve/{id}', [CommentController::class, 'approve']); 
        Route::delete('/admin/comments/{id}', [CommentController::class, 'destroy']); 

        // Trash
        Route::get('/admin/trashed', [TrashedController::class, 'trashed']);
        Route::post('/admin/restore/{id}', [TrashedController::class, 'restore']);
        // Route::delete('/admin/force-delete/{id}', [TrashedController::class, 'forceDelete']);

         // Order
        Route::get('/admin/orders', [OrderController::class, 'index'])->name('index');
        Route::get('/admin/orders/{order}', [OrderController::class, 'show'])->name('.show');
        Route::put('/admin/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('updateStatus');

        // Invoice
        Route::get('/admin/invoices', [InvoiceController::class, 'getInvoices']);
        Route::get('/admin/invoices/{id}', [InvoiceController::class, 'showInvoice']);
    });


// Client
    // Home
        Route::get("/home", [HomeController::class, "index"])->name("index");

    // List product
        Route::get("/shop", [ShopController::class, "listProduct"])->name("product.shop");

    // Filter by category
        Route::get('/shop/category/{id}', [ShopController::class, 'listProductsByCategory'])->name('shop.category');

    // Detail product
        // Route::get('/product/{slug}',[ProductController::class, 'productDetail'])->name('product.detail');

    // Comment
        Route::resource('products/{product_id}/comments', ClientCommentController::class);

    Route::middleware('auth:sanctum')->group(function () {
        // User
        Route::put('/user/{id}', [ClientUserController::class, 'updateUserInfo']);
        Route::put('/user/{id}/password', [ClientUserController::class, 'updatePassword']);

        // Checkout
        Route::post('/checkout', [CheckoutController::class, 'checkout']);

        // Order
        Route::get('orders', [ClientOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [ClientOrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/cancel', [ClientOrderController::class, 'cancel'])->name('orders.cancel');

        // Cart
        // Route::post('/product/add-to-cart',         [CartController::class, 'addToCart']);
        // Route::get('/product/list-cart',            [CartController::class, 'list']);
        // Route::delete('/product/delete-cart/{id}',  [CartController::class, 'deleteCart']);
    });