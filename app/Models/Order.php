<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'order_number',
        'total_amount',
        'delivery_method',
        'delivery_address',
        'delivery_zone',
        'estimated_delivery_date',
        'order_notes',
        'order_status',
        'payment_status',
        'cancelled_at',
        'cancelled_reason'
    ];

    protected $casts = [
        'estimated_delivery_date' => 'date',
        'cancelled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Check if order is a guest order.
     */
    public function isGuestOrder()
    {
        return is_null($this->user_id) && !is_null($this->guest_email);
    }

    /**
     * Get the customer name (either user or guest).
     */
    public function getCustomerNameAttribute()
    {
        if ($this->user) {
            return $this->user->name;
        }
        return $this->guest_name ?? 'Guest';
    }

    /**
     * Get the customer email (either user or guest).
     */
    public function getCustomerEmailAttribute()
    {
        if ($this->user) {
            return $this->user->email;
        }
        return $this->guest_email ?? 'No email';
    }

    /**
     * Get the customer phone (either user or guest).
     */
    public function getCustomerPhoneAttribute()
    {
        if ($this->user) {
            return $this->user->phone;
        }
        return $this->guest_phone ?? 'No phone';
    }

    // Helper method to check if order is cancellable
    public function isCancellable()
    {
        return in_array($this->order_status, ['pending', 'payment_confirmed']) && is_null($this->cancelled_at);
    }

    // Helper method to check if order is completed
    public function isCompleted()
    {
        return $this->order_status === 'completed';
    }

    // Helper method to get status color
    public function getStatusColor()
    {
        return match ($this->order_status) {
            'pending' => 'yellow',
            'payment_confirmed' => 'blue',
            'processing' => 'orange',
            'ready_for_pickup' => 'purple',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }
}