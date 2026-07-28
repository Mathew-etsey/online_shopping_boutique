<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Services\CloudinaryService;  // ← ADDED
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    protected $cloudinary;  // ← ADDED

    public function __construct(CloudinaryService $cloudinary)  // ← ADDED
    {
        $this->cloudinary = $cloudinary;
    }

    /**
     * Display a listing of the products.
     */
    public function index()
    {
        $products = Product::with(['category', 'images'])->get();
        
        $products->each(function ($product) {
            $product->stock_status = $product->getStockStatus();
        });
        
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get featured products (for homepage).
     */
    public function featured()
    {
        $products = Product::with(['category', 'images'])
            ->where('is_featured', true)
            ->where('stock_quantity', '>', 0)
            ->take(6)
            ->get();

        $products->each(function ($product) {
            $product->stock_status = $product->getStockStatus();
        });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get new arrivals (latest products).
     */
    public function newArrivals()
    {
        $products = Product::with(['category', 'images'])
            ->where('stock_quantity', '>', 0)
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        $products->each(function ($product) {
            $product->stock_status = $product->getStockStatus();
        });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'stock_quantity' => 'required|integer|min:0',
                'is_featured' => 'boolean',
            ]);

            $product = Product::create($validated);

            // Handle image upload manually with Cloudinary support
            if ($request->hasFile('images')) {
                $file = $request->file('images');
                if ($file->isValid() && in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                    
                    // Check if we should use Cloudinary or local storage
                    if ($this->shouldUseCloudinary()) {
                        // Upload to Cloudinary
                        $imageUrl = $this->cloudinary->upload($file, 'products');
                        $product->images()->create([
                            'image_url' => $imageUrl
                        ]);
                    } else {
                        // Upload to local storage
                        $path = $file->store('products', 'public');
                        $product->images()->create([
                            'image_url' => $path
                        ]);
                    }
                }
            }

            $product->stock_status = $product->getStockStatus();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product->load(['category', 'images'])
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Product creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product.
     */
    public function show(string $id)
    {
        $product = Product::with(['category', 'images', 'variations', 'reviews'])->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $product->stock_status = $product->getStockStatus();

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $validated = $request->validate([
                'category_id' => 'sometimes|exists:categories,id',
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'price' => 'sometimes|numeric|min:0',
                'stock_quantity' => 'sometimes|integer|min:0',
                'is_featured' => 'boolean',
            ]);

            $product->update($validated);

            // Handle image upload manually with Cloudinary support
            if ($request->hasFile('images')) {
                $file = $request->file('images');
                
                if ($file->isValid() && in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                    // Delete old images (works for both local and Cloudinary)
                    foreach ($product->images as $oldImage) {
                        $this->deleteImage($oldImage->image_url);
                        $oldImage->delete();
                    }
                    
                    // Upload new image
                    if ($this->shouldUseCloudinary()) {
                        $imageUrl = $this->cloudinary->upload($file, 'products');
                        $product->images()->create([
                            'image_url' => $imageUrl
                        ]);
                    } else {
                        $path = $file->store('products', 'public');
                        $product->images()->create([
                            'image_url' => $path
                        ]);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid image file. Please upload JPG, PNG, GIF, or WEBP.',
                    ], 422);
                }
            }

            $product->stock_status = $product->getStockStatus();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product->load(['category', 'images'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Product update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(string $id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // Delete images (works for both local and Cloudinary)
            foreach ($product->images as $image) {
                $this->deleteImage($image->image_url);
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Product deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if we should use Cloudinary
     */
    protected function shouldUseCloudinary(): bool  // ← ADDED
    {
        return config('filesystems.default') === 'cloudinary' || 
               env('FILESYSTEM_DISK') === 'cloudinary';
    }

    /**
     * Delete an image (works for both local and Cloudinary)
     */
    protected function deleteImage(string $imageUrl): void  // ← ADDED
    {
        // Check if it's a Cloudinary URL
        if (str_contains($imageUrl, 'cloudinary.com')) {
            $publicId = $this->cloudinary->getPublicId($imageUrl);
            $this->cloudinary->delete($publicId);
        } else {
            // Local storage
            if (Storage::disk('public')->exists($imageUrl)) {
                Storage::disk('public')->delete($imageUrl);
            }
        }
    }
}