<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Display a listing of wishlist items for a user.
     */
    public function index(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $wishlist = Wishlist::with(['product', 'product.category', 'product.images'])
            ->where('user_id', $request->user_id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $wishlist
        ]);
    }

    /**
     * Store a newly created wishlist item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id'
        ]);

        // Check if already in wishlist
        $existing = Wishlist::where('user_id', $validated['user_id'])
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Product already in wishlist'
            ], 422);
        }

        $wishlist = Wishlist::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Added to wishlist',
            'data' => $wishlist->load(['product'])
        ], 201);
    }

    /**
     * Remove the specified wishlist item.
     */
    public function destroy(string $id)
    {
        $wishlist = Wishlist::find($id);

        if (!$wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Wishlist item not found'
            ], 404);
        }

        $wishlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Removed from wishlist'
        ]);
    }

    /**
     * Toggle wishlist (add/remove).
     */
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id'
        ]);

        $existing = Wishlist::where('user_id', $validated['user_id'])
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'success' => true,
                'message' => 'Removed from wishlist',
                'action' => 'removed'
            ]);
        } else {
            $wishlist = Wishlist::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'Added to wishlist',
                'action' => 'added',
                'data' => $wishlist
            ], 201);
        }
    }

    /**
     * Check if product is in user's wishlist.
     */
    public function check(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id'
        ]);

        $exists = Wishlist::where('user_id', $validated['user_id'])
            ->where('product_id', $validated['product_id'])
            ->exists();

        return response()->json([
            'success' => true,
            'in_wishlist' => $exists
        ]);
    }
}