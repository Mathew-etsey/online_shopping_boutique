<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    /**
     * Initialize a payment transaction.
     */
    public function initializePayment($email, $amount, $reference, $metadata = [])
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
                'Content-Type' => 'application/json',
            ])->withOptions([
                'verify' => false, // Disable SSL verification for local testing only
            ])->post('https://api.paystack.co/transaction/initialize', [
                'email' => $email,
                'amount' => $amount * 100, // Convert to kobo
                'reference' => $reference,
                'callback_url' => env('PAYSTACK_CALLBACK_URL', 'http://localhost:5173/payment'),
                'metadata' => $metadata,
                'currency' => 'GHS'
            ]);

            $data = $response->json();

            if ($response->successful() && $data['status']) {
                return [
                    'success' => true,
                    'authorization_url' => $data['data']['authorization_url'],
                    'reference' => $data['data']['reference']
                ];
            }

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Payment initialization failed'
            ];

        } catch (\Exception $e) {
            Log::error('Paystack initialization failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify a payment transaction.
     */
    public function verifyPayment($reference)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
            ])->withOptions([
                'verify' => false, // Disable SSL verification for local testing only
            ])->get('https://api.paystack.co/transaction/verify/' . $reference);

            $data = $response->json();

            if ($response->successful() && $data['status'] && $data['data']['status'] === 'success') {
                return [
                    'success' => true,
                    'status' => $data['data']['status'],
                    'amount' => $data['data']['amount'] / 100,
                    'reference' => $data['data']['reference']
                ];
            }

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Verification failed'
            ];

        } catch (\Exception $e) {
            Log::error('Paystack verification failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate unique transaction reference.
     */
    public function generateReference()
    {
        return 'BOUTIQUE-' . strtoupper(uniqid());
    }
}