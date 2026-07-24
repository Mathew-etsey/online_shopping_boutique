<?php

namespace App\Jobs;

use App\Mail\OrderStatusUpdateMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(): void
    {
        try {
            // ✅ FIX: Get email from user OR guest
            $email = $this->order->user->email ?? $this->order->guest_email ?? null;

            if (!$email) {
                Log::error('No email found for order status update', ['order_id' => $this->order->id]);
                return;
            }

            Mail::to($email)->send(new OrderStatusUpdateMail($this->order));
            Log::info('Order status update email sent', [
                'order_id' => $this->order->id,
                'email' => $email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send status update email: ' . $e->getMessage(), [
                'order_id' => $this->order->id
            ]);
        }
    }
}