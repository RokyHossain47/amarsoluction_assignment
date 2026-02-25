<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(Request $request)
    {
        if ($request->header('apikey') !== '123') {
            abort(401, 'Unauthorized');
        }
    }
    public function products(Request $request)
    {
        $products = Products::get();
        return response()->json([
            'products' => $products,
        ]);
    }

    public function addProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'sku' => 'required|string|unique:products,sku',
            'price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
        ]);

        $product = Products::create([
            'name' => $request->name,
            'sku' => $request->sku,
            'price' => $request->price,
            'stock_quantity' => $request->stock_quantity,
        ]);

        return response()->json([
            'message' => 'Product added successfully',
            'product' => $product,
        ], 201);
    }   
}
