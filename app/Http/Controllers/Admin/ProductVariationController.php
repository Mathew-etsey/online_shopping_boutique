<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariation;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductVariationController extends Controller
{
    /**
     * Display a listing of variations for a product.
     */
    public function index(Request $request)
    {
        $productId = $request->query('product_id');
        
        if ($productId) {
            $variations = ProductVariation::where('product_id', $productId)->get();
        } else {
            $variations = ProductVariation::with('product')->get();
        }

        return response()->json([
            'success' => true,
            'data' => $variations
        ]);
    }

    /**
     * Store a newly created variation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'size' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'quantity' => 'required|integer|min:0'
        ]);

        $variation = ProductVariation::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Variation created successfully',
            'data' => $variation
        ], 201);
    }

    /**
     * Display the specified variation.
     */
    public function show(string $id)
    {
        $variation = ProductVariation::with('product')->find($id);

        if (!$variation) {
            return response()->json([
                'success' => false,
                'message' => 'Variation not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $variation
        ]);
    }

    /**
     * Update the specified variation.
     */
    public function update(Request $request, string $id)
    {
        $variation = ProductVariation::find($id);

        if (!$variation) {
            return response()->json([
                'success' => false,
                'message' => 'Variation not found'
            ], 404);
        }

        $validated = $request->validate([
            'size' => 'sometimes|string|max:50',
            'color' => 'sometimes|string|max:50',
            'quantity' => 'sometimes|integer|min:0'
        ]);

        $variation->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Variation updated successfully',
            'data' => $variation
        ]);
    }

    /**
     * Remove the specified variation.
     */
    public function destroy(string $id)
    {
        $variation = ProductVariation::find($id);

        if (!$variation) {
            return response()->json([
                'success' => false,
                'message' => 'Variation not found'
            ], 404);
        }

        $variation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Variation deleted successfully'
        ]);
    }
}