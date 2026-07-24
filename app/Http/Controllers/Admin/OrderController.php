<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Helpers\OrderHelper;
use App\Jobs\SendOrderConfirmationJob;
use App\Jobs\SendOrderStatusUpdateJob;
use App\Jobs\SendReadyForPickupJob;
use App\Jobs\SendReviewRequestJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.product', 'payment']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('order_status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search by order number or customer name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', "%{$search}%")
                                ->orWhere('email', 'LIKE', "%{$search}%")
                                ->orWhere('phone', 'LIKE', "%{$search}%");
                  });
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'delivery_method' => 'required|in:pickup,delivery,express',
            'delivery_address' => 'required_if:delivery_method,delivery,express|nullable|string',
            'delivery_zone' => 'nullable|string',
            'order_notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Calculate total amount
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
            }

            // Generate order number
            $orderNumber = OrderHelper::generateOrderNumber();

            // Calculate estimated delivery date
            $estimatedDate = $this->calculateEstimatedDate($validated['delivery_method']);

            // Create order
            $order = Order::create([
                'user_id' => $validated['user_id'],
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount,
                'delivery_method' => $validated['delivery_method'],
                'delivery_address' => $validated['delivery_address'] ?? null,
                'delivery_zone' => $validated['delivery_zone'] ?? null,
                'estimated_delivery_date' => $estimatedDate,
                'order_notes' => $validated['order_notes'] ?? null,
                'order_status' => 'pending',
                'payment_status' => 'pending'
            ]);

            // Create order items
            foreach ($validated['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);

                // Reduce product stock
                $product = Product::find($item['product_id']);
                $product->decrement('stock_quantity', $item['quantity']);
            }

            DB::commit();

            // Dispatch order confirmation email to queue
            SendOrderConfirmationJob::dispatch($order);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order->load(['user', 'items.product'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified order.
     */
    public function show(string $id)
    {
        $order = Order::with(['user', 'items.product', 'payment'])->find($id);

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

    /**
     * Update order status.
     */
    public function update(Request $request, string $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $validated = $request->validate([
            'order_status' => 'sometimes|in:pending,payment_confirmed,processing,ready_for_pickup,completed,cancelled',
            'payment_status' => 'sometimes|in:pending,paid,failed',
            'delivery_method' => 'sometimes|in:pickup,delivery,express',
            'delivery_address' => 'nullable|string',
            'delivery_zone' => 'nullable|string',
            'order_notes' => 'nullable|string'
        ]);

        // If order is being cancelled
        if (isset($validated['order_status']) && $validated['order_status'] === 'cancelled') {
            $validated['cancelled_at'] = now();
            $validated['cancelled_reason'] = $request->cancelled_reason ?? 'Cancelled by admin';
        }

        $order->update($validated);

        // Dispatch status update email to queue
        SendOrderStatusUpdateJob::dispatch($order);

        // If status is ready_for_pickup, dispatch ready for pickup email
        if (isset($validated['order_status']) && $validated['order_status'] === 'ready_for_pickup') {
            SendReadyForPickupJob::dispatch($order);
        }

        // If status is completed, dispatch review request email
        if (isset($validated['order_status']) && $validated['order_status'] === 'completed') {
            SendReviewRequestJob::dispatch($order);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully',
            'data' => $order->load(['user', 'items.product'])
        ]);
    }

    /**
     * Cancel the specified order.
     */
    public function destroy(string $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Check if order can be cancelled
        if (!$order->isCancellable()) {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be cancelled at this stage'
            ], 422);
        }

        // Restore stock
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->increment('stock_quantity', $item->quantity);
            }
        }

        $order->update([
            'order_status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_reason' => 'Cancelled by customer'
        ]);

        // Dispatch cancellation email to queue
        SendOrderStatusUpdateJob::dispatch($order);

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully'
        ]);
    }

    /**
     * Calculate estimated delivery date based on method.
     */
    private function calculateEstimatedDate($deliveryMethod)
    {
        $now = now();
        
        // If order is after 2pm, start from next day
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

    /**
     * Get order statistics for dashboard.
     */
    public function statistics()
    {
        $today = now()->toDateString();

        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('order_status', 'pending')->count(),
            'processing_orders' => Order::where('order_status', 'processing')->count(),
            'completed_orders' => Order::where('order_status', 'completed')->count(),
            'cancelled_orders' => Order::where('order_status', 'cancelled')->count(),
            'today_orders' => Order::whereDate('created_at', $today)->count(),
            'total_revenue' => Order::where('order_status', 'completed')->sum('total_amount'),
            'today_revenue' => Order::whereDate('created_at', $today)->where('order_status', 'completed')->sum('total_amount')
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Update order status (shortcut method for admin).
     */
    public function updateStatus(Request $request, string $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,payment_confirmed,processing,ready_for_pickup,completed,cancelled'
        ]);

        // If cancelling, restore stock
        if ($validated['status'] === 'cancelled' && $order->order_status !== 'cancelled') {
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('stock_quantity', $item->quantity);
                }
            }
            $order->cancelled_at = now();
            $order->cancelled_reason = $request->reason ?? 'Cancelled by admin';
        }

        $order->order_status = $validated['status'];
        $order->save();

        // Dispatch status update email to queue
        SendOrderStatusUpdateJob::dispatch($order);

        // If status is ready_for_pickup, dispatch ready for pickup email
        if ($validated['status'] === 'ready_for_pickup') {
            SendReadyForPickupJob::dispatch($order);
        }

        // If status is completed, dispatch review request email
        if ($validated['status'] === 'completed') {
            SendReviewRequestJob::dispatch($order);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data' => $order
        ]);
    }
}