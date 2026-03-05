<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cartItems = Cart::with('product')
            ->where('user_id', $request->user()->id)
            ->get();
        return response()->json($cartItems);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem = Cart::updateOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $request->product_id],
            ['quantity' => $request->quantity]
        );

        return response()->json($cartItem);
    }

    public function remove(Request $request, $id)
    {
        $cartItem = Cart::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
        $cartItem->delete();
        return response()->json(['message' => 'Item removed']);
    }
}