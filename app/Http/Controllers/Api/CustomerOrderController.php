<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Payment;
use App\Helpers\OrderHelper;
use App\Jobs\SendOrderConfirmationJob;  // ← ADD THIS IMPORT
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['items.product', 'payment'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function show(Request $request, $id)
    {
        $order = Order::with(['items.product', 'payment'])
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function publicShow($id)
    {
        $order = Order::with(['items.product', 'payment'])->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'nullable|exists:users,id',
                'guest_name' => 'nullable|string|max:255',
                'guest_email' => 'nullable|email|max:255',
                'guest_phone' => 'nullable|string|max:20',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'delivery_method' => 'required|in:pickup,delivery,express',
                'delivery_address' => 'nullable|string',
                'delivery_zone' => 'nullable|string',
                'order_notes' => 'nullable|string'
            ]);

            Log::info('Order request data:', $validated);

            DB::beginTransaction();

            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
            }

            $orderNumber = OrderHelper::generateOrderNumber();
            $estimatedDate = $this->calculateEstimatedDate($validated['delivery_method']);

            $orderData = [
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount,
                'delivery_method' => $validated['delivery_method'],
                'delivery_address' => $validated['delivery_address'] ?? null,
                'delivery_zone' => $validated['delivery_zone'] ?? null,
                'estimated_delivery_date' => $estimatedDate,
                'order_notes' => $validated['order_notes'] ?? null,
                'order_status' => 'pending',
                'payment_status' => 'pending'
            ];

            // ✅ FIX: Always save guest data if provided
            if (!empty($validated['user_id'])) {
                $orderData['user_id'] = $validated['user_id'];
                Log::info('User order:', ['user_id' => $validated['user_id']]);
            }

            // ✅ FIX: Save guest data when provided
            if (!empty($validated['guest_name'])) {
                $orderData['guest_name'] = $validated['guest_name'];
                $orderData['guest_email'] = $validated['guest_email'] ?? 'guest@example.com';
                $orderData['guest_phone'] = $validated['guest_phone'] ?? 'N/A';
                Log::info('Guest order:', [
                    'name' => $validated['guest_name'],
                    'email' => $validated['guest_email'] ?? 'guest@example.com'
                ]);
            }

            // ✅ FIX: If neither user_id nor guest_name, set default guest
            if (empty($orderData['user_id']) && empty($orderData['guest_name'])) {
                $orderData['guest_name'] = 'Guest';
                $orderData['guest_email'] = 'guest@example.com';
                $orderData['guest_phone'] = 'N/A';
                Log::info('Default guest order created');
            }

            $order = Order::create($orderData);

            foreach ($validated['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);

                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->decrement('stock_quantity', $item['quantity']);
                }
            }

            Payment::create([
                'order_id' => $order->id,
                'amount' => $totalAmount,
                'payment_method' => 'paystack',
                'transaction_reference' => $orderNumber,
                'payment_status' => 'pending'
            ]);

            DB::commit();

            Log::info('Order created successfully:', ['order_id' => $order->id]);

            // ✅ SEND ORDER CONFIRMATION EMAIL
            SendOrderConfirmationJob::dispatch($order);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order->load(['items.product'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function convertGuestToUser(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'user_id' => 'required|exists:users,id'
        ]);

        $order = Order::find($validated['order_id']);

        if ($order->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'This order already has a user associated'
            ], 422);
        }

        $order->update([
            'user_id' => $validated['user_id']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order linked to user account successfully',
            'data' => $order
        ]);
    }

    private function calculateEstimatedDate($deliveryMethod)
    {
        $now = now();
        if ($now->hour >= 14) {
            $now->addDay();
        }

        return match ($deliveryMethod) {
            'pickup' => $now->addDays(3),
            'delivery' => $now->addDays(5),
            'express' => $now->addDays(1),
            default => $now->addDays(5)
        };
    }
}