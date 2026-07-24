<?php

namespace App\Jobs;

use App\Mail\ReviewRequestMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReviewRequestJob implements ShouldQueue
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
            Mail::to($this->order->user->email)->send(new ReviewRequestMail($this->order));
            Log::info('Review request email sent', ['order_id' => $this->order->id]);
        } catch (\Exception $e) {
            Log::error('Failed to send review request email: ' . $e->getMessage(), [
                'order_id' => $this->order->id
            ]);
        }
    }
}