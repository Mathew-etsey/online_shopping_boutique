<?php

namespace App\Jobs;

use App\Mail\OrderReadyForPickupMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReadyForPickupJob implements ShouldQueue
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
                Log::error('No email found for ready for pickup', ['order_id' => $this->order->id]);
                return;
            }

            Mail::to($email)->send(new OrderReadyForPickupMail($this->order));
            Log::info('Ready for pickup email sent', [
                'order_id' => $this->order->id,
                'email' => $email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send ready for pickup email: ' . $e->getMessage(), [
                'order_id' => $this->order->id
            ]);
        }
    }
}