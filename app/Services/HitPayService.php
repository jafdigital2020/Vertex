<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Models\PaymentTransaction;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HitPayService
{
    private $client;
    private $apiKey;
    private $salt;
    private $baseUrl;
    private $webhookUrl;

    public function __construct()
    {
        $this->apiKey = config('services.hitpay.api_key');
        $this->salt = config('services.hitpay.salt');
        $this->baseUrl = config('services.hitpay.base_url', 'https://api.hitpayapp.com/');
        $this->webhookUrl = config('services.hitpay.webhook_url');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'verify' => true,
            'headers' => [
                'X-BUSINESS-API-KEY' => $this->apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ]
        ]);
    }

    /**
     * Create payment request
     */
    public function createPaymentRequest($invoice, $returnUrl = null)
    {
        try {
            $data = [
                'amount' => number_format($invoice->amount_due, 2, '.', ''),
                'currency' => 'PHP',
                'reference_number' => $invoice->invoice_number,
                'webhook' => $this->webhookUrl,
                'redirect_url' => $returnUrl ?: route('billing.payment.success'),
                'purpose' => "Payment for Invoice #{$invoice->invoice_number}",
                'name' => $invoice->subscription->tenant->name ?? 'Customer',
                'email' => $invoice->subscription->tenant->tenant_email ?? 'customer@example.com',
            ];

            $response = $this->client->post('v1/payment-requests', [
                'form_params' => $data
            ]);

            $responseData = json_decode($response->getBody(), true);

            if ($response->getStatusCode() === 201 && isset($responseData['id'])) {
                // Create payment transaction record
                $transaction = PaymentTransaction::create([
                    'invoice_id' => $invoice->id,
                    'subscription_id' => $invoice->subscription_id,
                    'payment_gateway' => 'hitpay',
                    'transaction_reference' => $responseData['id'],
                    'amount' => $invoice->amount_due,
                    'currency' => 'PHP',
                    'status' => 'pending',
                    'raw_request' => $data,
                    'raw_response' => $responseData,
                ]);

                return [
                    'success' => true,
                    'payment_url' => $responseData['url'] ?? $responseData['payment_url'],
                    'transaction_id' => $transaction->id,
                    'reference' => $responseData['id'],
                ];
            } else {
                throw new \Exception('Invalid response from HitPay API');
            }
        } catch (RequestException $e) {
            $errorMessage = 'HitPay API Error: ' . $e->getMessage();

            if ($e->hasResponse()) {
                $errorBody = json_decode($e->getResponse()->getBody(), true);
                $errorMessage .= ' - ' . ($errorBody['message'] ?? 'Unknown error');
            }

            Log::error($errorMessage);

            return [
                'success' => false,
                'error' => $errorMessage,
            ];
        } catch (\Exception $e) {
            Log::error('HitPay Payment Creation Failed: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus($paymentId)
    {
        try {
            $response = $this->client->get("v1/payment-requests/{$paymentId}");
            $responseData = json_decode($response->getBody(), true);

            return [
                'success' => true,
                'status' => $responseData['status'],
                'data' => $responseData,
            ];
        } catch (RequestException $e) {
            Log::error('HitPay Status Check Failed: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature($payload, $signature)
    {
        if (!$this->salt || !$signature) {
            return false;
        }

        $computedSignature = hash_hmac('sha256', $payload, $this->salt);
        return hash_equals($signature, $computedSignature);
    }

    /**
     * Process webhook payment
     */
    public function processWebhookPayment($data)
    {
        try {
            $paymentId = $data['payment_id'] ?? $data['id'] ?? null;

            if (!$paymentId) {
                throw new \Exception('No payment ID found in webhook data');
            }

            $transaction = PaymentTransaction::where('transaction_reference', $paymentId)->first();

            if (!$transaction) {
                throw new \Exception('Transaction not found: ' . $paymentId);
            }

            // Update transaction status
            $status = strtolower($data['status']);
            $transaction->update([
                'status' => $status,
                'paid_at' => $status === 'completed' ? now() : null,
                'raw_response' => array_merge($transaction->raw_response ?? [], $data),
            ]);

            // If payment is successful, update invoice
            if ($status === 'completed') {
                $invoice = $transaction->invoice;
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_method' => 'hitpay',
                ]);

                // Update subscription if needed
                $subscription = $invoice->subscription;
                if (in_array($subscription->status, ['expired', 'inactive', 'trial_ended'])) {
                    // Extend subscription based on plan
                    $newEndDate = now()->addDays(30); // or based on plan duration

                    $subscription->update([
                        'status' => 'active',
                        'current_period_end' => $newEndDate,
                        'updated_at' => now(),
                    ]);
                }
            }

            return [
                'success' => true,
                'transaction' => $transaction,
            ];
        } catch (\Exception $e) {
            Log::error('HitPay Webhook Processing Failed: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel payment request
     */
    public function cancelPaymentRequest($paymentId)
    {
        try {
            $response = $this->client->delete("v1/payment-requests/{$paymentId}");

            return [
                'success' => true,
                'data' => json_decode($response->getBody(), true),
            ];
        } catch (RequestException $e) {
            Log::error('HitPay Payment Cancellation Failed: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Refund payment
     */
    public function refundPayment($paymentId, $amount = null)
    {
        try {
            $data = [];
            if ($amount) {
                $data['amount'] = number_format($amount, 2, '.', '');
            }

            $response = $this->client->post("v1/refunds", [
                'form_params' => array_merge([
                    'payment_id' => $paymentId,
                ], $data)
            ]);

            return [
                'success' => true,
                'data' => json_decode($response->getBody(), true),
            ];
        } catch (RequestException $e) {
            Log::error('HitPay Refund Failed: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
