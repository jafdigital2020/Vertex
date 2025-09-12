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
            // Generate dynamic purpose based on invoice type
            $purpose = $this->generatePaymentPurpose($invoice);

            $data = [
                'amount' => number_format($invoice->amount_due, 2, '.', ''),
                'currency' => 'PHP',
                'reference_number' => $invoice->invoice_number,
                'webhook' => $this->webhookUrl,
                'redirect_url' => $returnUrl ?: route('billing.payment.success'),
                'purpose' => $purpose,
                'name' => $invoice->tenant->tenant_name ?? 'Customer',
                'email' => $invoice->tenant->tenant_email ?? 'customer@example.com',
            ];

            Log::info('Creating HitPay payment request', [
                'invoice_id' => $invoice->id,
                'invoice_type' => $invoice->invoice_type ?? 'subscription',
                'amount' => $data['amount'],
                'purpose' => $purpose
            ]);

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

                Log::info('HitPay payment request created successfully', [
                    'transaction_id' => $transaction->id,
                    'payment_reference' => $responseData['id']
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
     * Generate payment purpose based on invoice type
     */
    private function generatePaymentPurpose($invoice)
    {
        $invoiceType = $invoice->invoice_type ?? 'subscription';

        switch ($invoiceType) {
            case 'license_overage':
                $count = $invoice->license_overage_count ?? 0;
                return "License Overage Payment - {$count} additional licenses (Invoice #{$invoice->invoice_number})";

            case 'combo':
                $planName = $invoice->subscription->plan->name ?? 'Subscription';
                $overageCount = $invoice->license_overage_count ?? 0;
                return "{$planName} + {$overageCount} License Overage (Invoice #{$invoice->invoice_number})";

            default:
                $planName = $invoice->subscription->plan->name ?? 'Subscription';
                return "Payment for {$planName} (Invoice #{$invoice->invoice_number})";
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

            Log::info('Processing HitPay webhook', [
                'payment_id' => $paymentId,
                'transaction_id' => $transaction->id,
                'status' => $data['status'] ?? 'unknown'
            ]);

            // Map HitPay status
            $status = strtolower($data['status']);
            $mappedStatus = match ($status) {
                'completed', 'succeeded', 'success' => 'paid',
                'failed', 'error', 'cancelled', 'expired' => 'failed',
                'pending', 'processing', 'created' => 'pending',
                'refunded' => 'refunded',
                default => 'pending'
            };

            // Update transaction status
            $transaction->update([
                'status' => $mappedStatus,
                'paid_at' => $mappedStatus === 'paid' ? now() : null,
                'raw_response' => array_merge($transaction->raw_response ?? [], $data),
            ]);

            Log::info('Transaction status updated via webhook', [
                'transaction_id' => $transaction->id,
                'old_status' => $transaction->getOriginal('status'),
                'new_status' => $mappedStatus,
                'hitpay_status' => $status
            ]);

            return [
                'success' => true,
                'transaction' => $transaction,
                'mapped_status' => $mappedStatus,
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
