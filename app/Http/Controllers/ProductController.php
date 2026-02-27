<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    //
    public function addProducts(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'sku' => 'required|string|unique:products,sku',
            'price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
        ]);

        try {
            $product = new Product();
            $product->name = $request->name;
            $product->sku = $request->sku;
            $product->price = $request->price;
            $product->stock_quantity = $request->stock_quantity;
            $product->save();

            return response()->json(['message' => 'Product added successfully', 'product' => $product], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error adding product: ' . $e->getMessage()], 500);
        }
    }

    public function getProducts()
    {
        $products = Product::all();
        return response()->json(['products' => $products], 200);
    }

    public function updateProducts(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:products,id',
            'name' => 'sometimes|required|string',
            'sku' => 'sometimes|required|string|unique:products,sku,' . $request->id,
            'price' => 'sometimes|required|numeric',
            'stock_quantity' => 'sometimes|required|integer',
        ]);

        try {
            $product = Product::find($request->id);
            if ($request->has('name')) {
                $product->name = $request->name;
            }
            if ($request->has('sku')) {
                $product->sku = $request->sku;
            }
            if ($request->has('price')) {
                $product->price = $request->price;
            }
            if ($request->has('stock_quantity')) {
                $product->stock_quantity = $request->stock_quantity;
            }
            $product->save();

            return response()->json(['message' => 'Product updated successfully', 'product' => $product], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating product: ' . $e->getMessage()], 500);
        }
    }

    public function deleteProducts(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:products,id',
        ]);

        try {
            $product = Product::find($request->id);
            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }
            $product->delete();
            return response()->json(['message' => 'Product deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting product: ' . $e->getMessage()], 500);
        }
    }
}
