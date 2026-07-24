<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaystackService;
use App\Jobs\ProcessPaystackWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
    }

    /**
     * Initialize payment for an order.
     */
    public function initialize(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::with('user')->find($validated['order_id']);

        // Check if order can be paid
        if (!in_array($order->order_status, ['pending', 'payment_confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be paid for at this stage'
            ], 422);
        }

        // Check if payment already exists
        $existingPayment = Payment::where('order_id', $order->id)
            ->where('payment_status', 'pending')
            ->first();

        if ($existingPayment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment already initiated for this order',
                'reference' => $existingPayment->transaction_reference
            ], 422);
        }

        // Generate reference
        $reference = $this->paystackService->generateReference();

        // Initialize payment
        $result = $this->paystackService->initializePayment(
            $order->user->email,
            $order->total_amount,
            $reference,
            ['order_id' => $order->id, 'user_id' => $order->user_id]
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 500);
        }

        // Save payment record
        Payment::create([
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'payment_method' => 'paystack',
            'transaction_reference' => $reference,
            'payment_status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment initialized successfully',
            'data' => [
                'authorization_url' => $result['authorization_url'],
                'reference' => $reference,
                'order_id' => $order->id,
                'amount' => $order->total_amount
            ]
        ]);
    }

    /**
     * Verify payment after callback.
     */
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string'
        ]);

        $reference = $validated['reference'];

        // Check if payment exists
        $payment = Payment::where('transaction_reference', $reference)->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment record not found'
            ], 404);
        }

        // Verify payment
        $result = $this->paystackService->verifyPayment($reference);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 500);
        }

        if ($result['status'] === 'success') {
            DB::beginTransaction();

            try {
                // Update payment status
                $payment->payment_status = 'success';
                $payment->save();

                // Update order
                $order = Order::find($payment->order_id);
                $order->payment_status = 'paid';
                $order->order_status = 'payment_confirmed';
                $order->save();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Payment verified successfully',
                    'data' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => 'success',
                        'amount' => $result['amount']
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Payment verification failed: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed: ' . $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment verification failed',
            'data' => [
                'status' => $result['status']
            ]
        ]);
    }

    /**
     * Handle Paystack webhook.
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $event = json_decode($payload, true);

        Log::info('Paystack webhook received', ['event' => $event['event'] ?? 'unknown']);

        // Dispatch webhook processing to queue
        ProcessPaystackWebhookJob::dispatch($event);

        return response()->json(['status' => 'success']);
    }

    /**
     * Get payment status.
     */
    public function status(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string'
        ]);

        $payment = Payment::where('transaction_reference', $validated['reference'])->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $payment->payment_status,
                'amount' => $payment->amount,
                'order_id' => $payment->order_id,
                'reference' => $payment->transaction_reference,
                'created_at' => $payment->created_at
            ]
        ]);
    }
}