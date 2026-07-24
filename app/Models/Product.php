<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'stock_quantity'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get stock status badge.
     */
    public function getStockStatus()
    {
        if ($this->stock_quantity <= 0) {
            return [
                'status' => 'out_of_stock',
                'label' => 'Out of Stock',
                'color' => 'red',
                'icon' => '❌'
            ];
        } elseif ($this->stock_quantity <= 10) {
            return [
                'status' => 'low_stock',
                'label' => 'Low Stock (' . $this->stock_quantity . ' left)',
                'color' => 'yellow',
                'icon' => '⚠️'
            ];
        } else {
            return [
                'status' => 'in_stock',
                'label' => 'In Stock',
                'color' => 'green',
                'icon' => '✅'
            ];
        }
    }

    /**
     * Check if product is in stock.
     */
    public function isInStock()
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Check if product is low stock.
     */
    public function isLowStock()
    {
        return $this->stock_quantity > 0 && $this->stock_quantity <= 10;
    }
}