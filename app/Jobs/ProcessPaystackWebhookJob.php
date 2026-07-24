<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPaystackWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function handle(): void
    {
        $event = $this->payload;

        if ($event['event'] === 'charge.success') {
            $reference = $event['data']['reference'];

            DB::beginTransaction();

            try {
                $payment = Payment::where('transaction_reference', $reference)->first();

                if (!$payment) {
                    Log::error('Payment not found for reference: ' . $reference);
                    DB::rollBack();
                    return;
                }

                $payment->payment_status = 'success';
                $payment->save();

                $order = Order::find($payment->order_id);
                $order->payment_status = 'paid';
                $order->order_status = 'payment_confirmed';
                $order->save();

                DB::commit();

                Log::info('Payment confirmed via webhook job', [
                    'order_id' => $order->id,
                    'reference' => $reference
                ]);

                // Send payment confirmation email (optional)

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Webhook job failed: ' . $e->getMessage());
                throw $e;
            }
        }
    }
}