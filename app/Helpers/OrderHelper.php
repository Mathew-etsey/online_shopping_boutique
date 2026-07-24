<?php

namespace App\Helpers;

use App\Models\Order;

class OrderHelper
{
    public static function generateOrderNumber()
    {
        $prefix = 'ORD';
        $year = date('Y');
        $month = date('m');
        
        // Get the last order number
        $lastOrder = Order::orderBy('id', 'desc')->first();
        
        if ($lastOrder && $lastOrder->order_number) {
            // Extract the sequence number from last order
            $lastNumber = explode('-', $lastOrder->order_number);
            $sequence = intval(end($lastNumber)) + 1;
        } else {
            $sequence = 1;
        }
        
        // Format: ORD-2026-07-001
        return $prefix . '-' . $year . '-' . $month . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}