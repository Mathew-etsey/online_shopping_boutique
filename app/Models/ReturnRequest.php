<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'product_id',
        'type',
        'reason',
        'status',
        'admin_notes',
        'resolution'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getStatusColor()
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'declined' => 'red',
            'completed' => 'blue',
            default => 'gray'
        };
    }
}