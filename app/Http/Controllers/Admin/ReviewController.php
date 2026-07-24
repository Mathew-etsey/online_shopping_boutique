<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of reviews.
     */
    public function index(Request $request)
    {
        $query = Review::with(['user', 'product', 'order']);

        // Filter by product
        if ($request->has('product_id') && $request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by rating
        if ($request->has('rating') && $request->rating) {
            $query->where('rating', $request->rating);
        }

        // Filter by verified
        if ($request->has('verified') && $request->verified !== null) {
            $query->where('verified_purchase', $request->verified);
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    /**
     * Store a newly created review.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10'
        ]);

        // Check if user already reviewed this product
        $existing = Review::where('user_id', $validated['user_id'])
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this product'
            ], 422);
        }

        // Check if order belongs to user and is completed
        $order = Order::where('id', $validated['order_id'])
            ->where('user_id', $validated['user_id'])
            ->where('order_status', 'completed')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or not completed'
            ], 404);
        }

        // Check if product is in the order
        $orderItem = $order->items()->where('product_id', $validated['product_id'])->first();
        if (!$orderItem) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found in this order'
            ], 404);
        }

        $validated['verified_purchase'] = true;
        $review = Review::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => $review->load(['user', 'product'])
        ], 201);
    }

    /**
     * Display the specified review.
     */
    public function show(string $id)
    {
        $review = Review::with(['user', 'product', 'order'])->find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $review
        ]);
    }

    /**
     * Update the specified review.
     */
    public function update(Request $request, string $id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'sometimes|string|min:10'
        ]);

        $review->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => $review->load(['user', 'product'])
        ]);
    }

    /**
     * Remove the specified review.
     */
    public function destroy(string $id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully'
        ]);
    }

    /**
     * Get product reviews.
     */
    public function productReviews(string $productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $reviews = Review::with(['user'])
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->get();

        $averageRating = $reviews->avg('rating') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product->name,
                'total_reviews' => $reviews->count(),
                'average_rating' => round($averageRating, 1),
                'reviews' => $reviews
            ]
        ]);
    }

    /**
     * Get review statistics.
     */
    public function statistics()
    {
        $stats = [
            'total' => Review::count(),
            'verified' => Review::where('verified_purchase', true)->count(),
            'unverified' => Review::where('verified_purchase', false)->count(),
            'average_rating' => round(Review::avg('rating') ?? 0, 1),
            'rating_breakdown' => [
                '5_star' => Review::where('rating', 5)->count(),
                '4_star' => Review::where('rating', 4)->count(),
                '3_star' => Review::where('rating', 3)->count(),
                '2_star' => Review::where('rating', 2)->count(),
                '1_star' => Review::where('rating', 1)->count()
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}