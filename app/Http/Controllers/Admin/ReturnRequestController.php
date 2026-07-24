<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Models\Order;
use Illuminate\Http\Request;

class ReturnRequestController extends Controller
{
    /**
     * Display a listing of return requests.
     */
    public function index(Request $request)
    {
        $query = ReturnRequest::with(['user', 'order', 'product']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $returnRequests = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $returnRequests
        ]);
    }

    /**
     * Store a newly created return request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:return,exchange',
            'reason' => 'required|string|min:10'
        ]);

        // Check if order belongs to user
        $order = Order::where('id', $validated['order_id'])
            ->where('user_id', $validated['user_id'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found for this user'
            ], 404);
        }

        // Check if already requested
        $existing = ReturnRequest::where('order_id', $validated['order_id'])
            ->where('product_id', $validated['product_id'])
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'A return request already exists for this item'
            ], 422);
        }

        $returnRequest = ReturnRequest::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Return request submitted successfully',
            'data' => $returnRequest->load(['user', 'order', 'product'])
        ], 201);
    }

    /**
     * Display the specified return request.
     */
    public function show(string $id)
    {
        $returnRequest = ReturnRequest::with(['user', 'order', 'product'])->find($id);

        if (!$returnRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Return request not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $returnRequest
        ]);
    }

    /**
     * Update the specified return request (admin only).
     */
    public function update(Request $request, string $id)
    {
        $returnRequest = ReturnRequest::find($id);

        if (!$returnRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Return request not found'
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,approved,declined,completed',
            'admin_notes' => 'nullable|string',
            'resolution' => 'nullable|string'
        ]);

        $returnRequest->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Return request updated successfully',
            'data' => $returnRequest->load(['user', 'order', 'product'])
        ]);
    }

    /**
     * Remove the specified return request.
     */
    public function destroy(string $id)
    {
        $returnRequest = ReturnRequest::find($id);

        if (!$returnRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Return request not found'
            ], 404);
        }

        $returnRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Return request deleted successfully'
        ]);
    }

    /**
     * Get return statistics for dashboard.
     */
    public function statistics()
    {
        $stats = [
            'total' => ReturnRequest::count(),
            'pending' => ReturnRequest::where('status', 'pending')->count(),
            'approved' => ReturnRequest::where('status', 'approved')->count(),
            'declined' => ReturnRequest::where('status', 'declined')->count(),
            'completed' => ReturnRequest::where('status', 'completed')->count(),
            'returns' => ReturnRequest::where('type', 'return')->count(),
            'exchanges' => ReturnRequest::where('type', 'exchange')->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Update return status (shortcut for admin).
     */
    public function updateStatus(Request $request, string $id)
    {
        $returnRequest = ReturnRequest::find($id);

        if (!$returnRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Return request not found'
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,declined,completed',
            'admin_notes' => 'nullable|string'
        ]);

        $returnRequest->status = $validated['status'];
        if (isset($validated['admin_notes'])) {
            $returnRequest->admin_notes = $validated['admin_notes'];
        }
        $returnRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Return status updated successfully',
            'data' => $returnRequest
        ]);
    }
}