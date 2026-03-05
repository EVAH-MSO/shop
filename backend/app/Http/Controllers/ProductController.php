<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request)
{
    // Get page size from query or default to 6
    $perPage = $request->query('per_page', 6);

    // Paginate products
    $products = Product::paginate($perPage);

    return response()->json($products);
}

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'nullable|string',
        ]);

        $product = Product::create($request->all());

        return response()->json($product, 201);
    }
}