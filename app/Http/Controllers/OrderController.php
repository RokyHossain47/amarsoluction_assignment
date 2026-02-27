<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Order_item;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['items.product'])->get();
        return response()->json(['orders' => $orders], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'status' => 'sometimes|string|in:pending,confirmed,cancelled',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $order = DB::transaction(function () use ($request) {
                $order = Order::create([
                    'customer_name' => $request->customer_name,
                    'total_amount' => 0,
                    'status' => $request->status ?? 'pending',
                ]);

                $total = 0;

                foreach ($request->items as $item) {
                    $product = Product::findOrFail($item['product_id']);

                    if ($product->stock_quantity < $item['quantity']) {
                        throw new \Exception("Insufficient stock for product id {$product->id}");
                    }

                    $unitPrice = (float) $product->price;
                    $subtotal = $unitPrice * (int) $item['quantity'];

                    $orderItem = Order_item::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ]);

                    // decrement stock
                    $product->stock_quantity = $product->stock_quantity - $item['quantity'];
                    $product->save();

                    $total += $subtotal;
                }

                $order->total_amount = $total;
                $order->save();

                return $order->load('items.product');
            });

            return response()->json(['message' => 'Order created', 'order' => $order], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating order: ' . $e->getMessage()], 400);
        }
    }

    public function show($id)
    {
        $order = Order::with(['items.product'])->find($id);

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json(['order' => $order], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,confirmed,cancelled',
        ]);

        try {
            $updated = DB::transaction(function () use ($request, $id) {
                $order = Order::with('items')->find($id);

                if (! $order) {
                    return null;
                }

                $oldStatus = $order->status;
                $newStatus = $request->status;

                if ($oldStatus === $newStatus) {
                    return $order->load('items.product');
                }

                if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                    foreach ($order->items as $item) {
                        $product = Product::lockForUpdate()->find($item->product_id);
                        if ($product) {
                            $product->stock_quantity += $item->quantity;
                            $product->save();
                        }
                    }
                }

                if ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
                    foreach ($order->items as $item) {
                        $product = Product::lockForUpdate()->find($item->product_id);
                        if (! $product) {
                            throw new \Exception("Product {$item->product_id} not found");
                        }
                        if ($product->stock_quantity < $item->quantity) {
                            throw new \Exception("Insufficient stock to reactivate order for product id {$product->id}");
                        }
                        $product->stock_quantity -= $item->quantity;
                        $product->save();
                    }
                }

                $order->status = $newStatus;
                $order->save();

                return $order->load('items.product');
            });

            if ($updated === null) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            return response()->json(['message' => 'Order status updated', 'order' => $updated], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating status: ' . $e->getMessage()], 400);
        }
    }
}
