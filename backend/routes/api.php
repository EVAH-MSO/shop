<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Models\Cart;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Product;
use App\Models\Cart as CartModel;

// -------------------------
// AUTH
// -------------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // -------------------------
    // PRODUCTS
    // -------------------------
    Route::post('/products', [ProductController::class, 'store']); // add product

    // -------------------------
    // CART
    // -------------------------
    Route::post('/cart', [CartController::class, 'add']); // add/update cart
    Route::get('/cart', [CartController::class, 'index']); // list cart items
    Route::delete('/cart/{id}', [CartController::class, 'remove']); // remove item

    Route::post('/cart/checkout-success', function (Request $request) {
        $user = $request->user();
        Cart::where('user_id', $user->id)->delete();
        return response()->json(['message' => 'Cart cleared and order completed']);
    });

    // -------------------------
    // STRIPE CHECKOUT
    // -------------------------
    Route::post('/checkout', function (Request $request) {

        $user = $request->user();
        $items = $request->input('items'); // [{id, name, price, quantity}]

        if (empty($items)) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $lineItems = array_map(function ($item) {
            return [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $item['name'],
                    ],
                    'unit_amount' => intval($item['price'] * 100),
                ],
                'quantity' => $item['quantity'],
            ];
        }, $items);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => env('FRONTEND_URL') . '/success',
            'cancel_url' => env('FRONTEND_URL') . '/cart',
        ]);

        return response()->json(['url' => $session->url]);
    });
});

// -------------------------
// PUBLIC PRODUCTS
// -------------------------
Route::get('/products', [ProductController::class, 'index']); // list products
Route::get('/products/{id}', [ProductController::class, 'show']); // single product
Route::get('/products/search', [ProductController::class, 'search']); // search products