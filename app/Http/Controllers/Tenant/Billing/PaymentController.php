<?php

namespace App\Http\Controllers\Tenant\Billing;

use Carbon\Carbon;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Services\HitPayService;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    private $hitPayService;
    private $centralAdminApiUrl;

    // Status mapping constants - Map external statuses to your enum values
    const HITPAY_STATUS_MAPPING = [
        'completed' => 'paid',
        'succeeded' => 'paid',
        'success' => 'paid',
        'failed' => 'failed',
        'error' => 'failed',
        'cancelled' => 'failed',
        'pending' => 'pending',
        'processing' => 'pending',
        'created' => 'pending',
        'refunded' => 'refunded',
        'expired' => 'failed',
    ];

    /**
     * Map external payment status to our enum values
     */
    private function mapPaymentStatus($externalStatus)
    {
        return self::HITPAY_STATUS_MAPPING[strtolower($externalStatus)] ?? 'pending';
    }

    public function __construct(HitPayService $hitPayService)
    {
        $this->hitPayService = $hitPayService;
        $this->centralAdminApiUrl = config('services.central_admin.api_url', 'https://api-admin.timora.ph');
    }

    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    public function initiatePayment(Request $request, $invoiceId)
    {
        try {
            $authUser = $this->authUser();
            $invoice = Invoice::where('id', $invoiceId)
                ->where('tenant_id', $authUser->tenant_id)
                ->where('status', '!=', 'paid')
                ->with(['subscription.tenant'])
                ->first();

            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found or already paid'
                ], 404);
            }

            Log::info('Initiating payment for invoice', [
                'invoice_id' => $invoice->id,
                'invoice_type' => $invoice->invoice_type ?? 'subscription',
                'amount' => $invoice->amount_due,
                'tenant_id' => $authUser->tenant_id
            ]);

            $result = $this->hitPayService->createPaymentRequest(
                $invoice,
                route('billing.payment.return', ['invoice' => $invoice->id])
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'payment_url' => $result['payment_url'],
                    'transaction_id' => $result['transaction_id'],
                    'message' => 'Payment request created successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create payment request: ' . $result['error']
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Payment initiation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Payment initiation failed'
            ], 500);
        }
    }

    /**
     * Handle payment return
     */

    public function paymentReturn(Request $request, $invoiceId)
    {
        try {
            $invoice = Invoice::with(['subscription', 'paymentTransactions'])->find($invoiceId);

            if (!$invoice) {
                return redirect()->route('billing.index')->with('error', 'Invoice not found');
            }

            // Get the latest transaction for this invoice
            $transaction = $invoice->paymentTransactions()->latest()->first();

            if ($transaction) {
                // Check payment status from HitPay
                $statusResult = $this->hitPayService->getPaymentStatus($transaction->transaction_reference);

                if ($statusResult['success']) {
                    $paymentStatus = strtolower($statusResult['status']);

                    // Map HitPay status
                    $mappedStatus = match ($paymentStatus) {
                        'completed', 'succeeded', 'success' => 'paid',
                        'failed', 'error' => 'failed',
                        default => 'pending'
                    };

                    // Update transaction status
                    $transaction->update([
                        'status' => $mappedStatus,
                        'last_status_check' => now(),
                    ]);

                    // If payment completed, update invoice and subscription
                    if (in_array($paymentStatus, ['completed', 'succeeded', 'success'])) {
                        $this->updateInvoiceAndSubscription($invoice, $transaction);

                        return redirect()->route('billing.index')
                            ->with('success', 'Payment completed successfully!');
                    } else if (in_array($paymentStatus, ['failed', 'error'])) {
                        return redirect()->route('billing.index')
                            ->with('error', 'Payment failed. Please try again.');
                    }
                }
            }

            return redirect()->route('billing.index')
                ->with('warning', 'Payment status could not be verified. Please contact support.');
        } catch (\Exception $e) {
            Log::error('Payment return processing failed: ' . $e->getMessage());

            return redirect()->route('billing.index')
                ->with('error', 'Payment processing failed. Please contact support.');
        }
    }

    private function updateInvoiceAndSubscription($invoice, $transaction)
    {
        try {
            // Update main invoice
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => 'hitpay',
                'amount_paid' => $invoice->amount_due,
            ]);

            // If this is a subscription or implementation fee invoice, update subscription
            $subscription = $invoice->subscription;
            if ($subscription) {
                // Handle different invoice types
                if ($invoice->invoice_type === 'subscription') {
                    $this->updateSubscription($subscription, $invoice);

                    // ✅ NEW: Mark consolidated license overage invoices as paid
                    if ($invoice->license_overage_count > 0) {
                        $this->markConsolidatedInvoicesAsPaid($subscription->tenant_id, $invoice);
                    }
                } elseif ($invoice->invoice_type === 'implementation_fee') {
                    // ✅ NEW: Update implementation fee paid in subscription
                    $this->updateImplementationFeePaid($subscription, $invoice);
                } elseif ($invoice->invoice_type === 'plan_upgrade') {
                    // ✅ Handle plan upgrade
                    $this->processPlanUpgrade($subscription, $invoice);
                }
            }

            // Update transaction
            $transaction->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // sync to central admin (public API, no auth required)
            $this->trySyncToCentralAdmin($transaction);

            Log::info('Invoice payment processed successfully', [
                'invoice_id' => $invoice->id,
                'invoice_type' => $invoice->invoice_type,
                'amount_paid' => $invoice->amount_due,
                'includes_consolidated_overage' => $invoice->license_overage_count > 0
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update invoice/subscription: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ✅ NEW: Mark existing unpaid license overage invoices as paid when consolidated invoice is paid
     */
    private function markConsolidatedInvoicesAsPaid($tenantId, $paidInvoice)
    {
        // Find invoices that were consolidated into this payment
        $consolidatedInvoices = Invoice::where('tenant_id', $tenantId)
            ->where('status', 'consolidated')
            ->where('consolidated_into_invoice_id', $paidInvoice->id)
            ->get();

        foreach ($consolidatedInvoices as $consolidatedInvoice) {
            $consolidatedInvoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'amount_paid' => $consolidatedInvoice->amount_due
            ]);

            Log::info('Consolidated license overage invoice marked as paid', [
                'consolidated_invoice_id' => $consolidatedInvoice->id,
                'consolidated_invoice_number' => $consolidatedInvoice->invoice_number,
                'paid_via_invoice_id' => $paidInvoice->id,
                'overage_amount' => $consolidatedInvoice->license_overage_amount
            ]);
        }

        // Mark ALL pending license overage invoices as consolidated
        $allPendingOverageInvoices = Invoice::where('tenant_id', $tenantId)
            ->where('invoice_type', 'license_overage')
            ->where('status', 'pending')
            ->where('id', '!=', $paidInvoice->id)
            ->get();

        foreach ($allPendingOverageInvoices as $overageInvoice) {
            $overageInvoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'amount_paid' => $overageInvoice->amount_due,
                'consolidated_into_invoice_id' => $paidInvoice->id,
                'notes' => 'Consolidated and paid via ' . $paidInvoice->invoice_number
            ]);

            Log::info('Pending license overage invoice marked as consolidated and paid', [
                'overage_invoice_id' => $overageInvoice->id,
                'overage_invoice_number' => $overageInvoice->invoice_number,
                'paid_via_invoice_id' => $paidInvoice->id,
                'overage_count' => $overageInvoice->license_overage_count
            ]);
        }

        if ($allPendingOverageInvoices->count() > 0 || $consolidatedInvoices->count() > 0) {
            $totalOverageLicenses = $consolidatedInvoices->sum('license_overage_count') +
                $allPendingOverageInvoices->sum('license_overage_count');

            Log::info('All license overage consolidated and subscription renewed', [
                'tenant_id' => $tenantId,
                'total_overage_licenses_consolidated' => $totalOverageLicenses,
                'consolidated_invoices_count' => $consolidatedInvoices->count(),
                'pending_invoices_converted_count' => $allPendingOverageInvoices->count(),
                'paid_via_invoice_id' => $paidInvoice->id
            ]);
        }
    }

    /**
     * Update subscription after payment
     */
    private function updateSubscription($subscription, $invoice)
    {
        try {
            $billingCycle = $subscription->billing_cycle ?? 'monthly';
            $currentEndDate = $subscription->subscription_end
                ? Carbon::parse($subscription->subscription_end)
                : now();

            $baseDate = $currentEndDate->gt(now()) ? $currentEndDate : now();

            $newEndDate = match ($billingCycle) {
                'yearly' => $baseDate->copy()->addYear(),
                'quarterly' => $baseDate->copy()->addMonths(3),
                default => $baseDate->copy()->addMonth(),
            };

            // ✅ FIXED: Don't change active_license or amount_paid

            $currentActiveLicense = $subscription->active_license ?? 0;
            $currentAmountPaid = $subscription->amount_paid ?? 0;
            $currentImplementationFeePaid = $subscription->implementation_fee_paid ?? 0;

            // ✅ NEW: Track implementation fee payment
            $newImplementationFeePaid = $currentImplementationFeePaid;
            if ($invoice->implementation_fee > 0) {
                $newImplementationFeePaid += $invoice->implementation_fee;
            }

            // ✅ IMPORTANT: Don't upgrade license count after overage payment

            $subscription->update([
                'status' => 'active',
                'payment_status' => 'paid',
                'subscription_end' => $newEndDate,
                'renewed_at' => now(),
                'next_renewal_date' => $newEndDate,
                'active_license' => $currentActiveLicense, // ✅ Keep same base license count
                'amount_paid' => $currentAmountPaid, // ✅ Keep same plan price
                'implementation_fee_paid' => $newImplementationFeePaid, // ✅ Track implementation fee
            ]);

            Log::info('Subscription renewed without changing base license count', [
                'subscription_id' => $subscription->id,
                'new_end_date' => $newEndDate->toDateString(),
                'active_license' => $currentActiveLicense, // ✅ Unchanged
                'amount_paid' => $currentAmountPaid, // ✅ Unchanged
                'implementation_fee_paid' => $newImplementationFeePaid,
                'invoice_had_overage' => $invoice->license_overage_count > 0,
                'overage_count_paid' => $invoice->license_overage_count ?? 0,
                'invoice_implementation_fee' => $invoice->implementation_fee ?? 0
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update subscription: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * ✅ NEW: Update implementation fee paid in subscription
     */
    private function updateImplementationFeePaid($subscription, $invoice)
    {
        try {
            $currentImplementationFeePaid = $subscription->implementation_fee_paid ?? 0;
            $invoiceImplementationFee = $invoice->implementation_fee ?? $invoice->amount_due ?? 0;

            $newImplementationFeePaid = $currentImplementationFeePaid + $invoiceImplementationFee;

            $subscription->update([
                'implementation_fee_paid' => $newImplementationFeePaid,
            ]);

            Log::info('Implementation fee updated in subscription', [
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
                'previous_impl_fee_paid' => $currentImplementationFeePaid,
                'invoice_impl_fee' => $invoiceImplementationFee,
                'new_impl_fee_paid' => $newImplementationFeePaid,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update implementation fee: ' . $e->getMessage(), [
                'subscription_id' => $subscription->id,
                'invoice_id' => $invoice->id
            ]);
            throw $e;
        }
    }

    /**
     * ✅ NEW: Process plan upgrade after payment
     */
    private function processPlanUpgrade($subscription, $invoice)
    {
        try {
            $newPlanId = $invoice->upgrade_plan_id;

            if (!$newPlanId) {
                Log::error('No upgrade plan ID found in invoice', [
                    'invoice_id' => $invoice->id,
                    'subscription_id' => $subscription->id
                ]);
                return;
            }

            $newPlan = \App\Models\Plan::find($newPlanId);

            if (!$newPlan) {
                Log::error('New plan not found', [
                    'plan_id' => $newPlanId,
                    'invoice_id' => $invoice->id
                ]);
                return;
            }

            // Get current plan for logging
            $oldPlan = $subscription->plan;
            $oldPlanId = $subscription->plan_id;

            // ✅ Calculate new subscription_end and next_renewal_date based on billing cycle
            $billingCycle = $newPlan->billing_cycle ?? 'monthly';
            $currentEndDate = $subscription->subscription_end
                ? Carbon::parse($subscription->subscription_end)
                : now();

            // Add period based on billing cycle
            $newEndDate = match ($billingCycle) {
                'yearly' => now()->copy()->addYear(),
                'quarterly' => now()->copy()->addMonths(3),
                default => now()->copy()->addMonth(),
            };

            // Update subscription to new plan
            $subscription->update([
                'plan_id' => $newPlan->id,
                'implementation_fee_paid' => $newPlan->implementation_fee ?? 0,
                'active_license' => max((($newPlan->employee_minimum ?? $newPlan->license_limit ?? 0) - 1), 0),
                'amount_paid' => $newPlan->price,
                'payment_status' => 'paid', // ✅ Mark as paid
                'subscription_end' => $newEndDate, // ✅ Extend subscription period
                'next_renewal_date' => $newEndDate, // ✅ Set next renewal date
                'renewed_at' => now(), // ✅ Track when renewal happened
                'billing_cycle' => $newPlan->billing_cycle,
            ]);

            Log::info('Plan upgraded successfully after payment', [
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
                'old_plan_id' => $oldPlanId,
                'old_plan_name' => $oldPlan->name ?? 'Unknown',
                'old_subscription_end' => $currentEndDate->toDateString(),
                'new_plan_id' => $newPlan->id,
                'new_plan_name' => $newPlan->name,
                'new_employee_limit' => $newPlan->employee_limit,
                'billing_cycle' => $billingCycle,
                'new_subscription_end' => $newEndDate->toDateString(),
                'new_next_renewal_date' => $newEndDate->toDateString(),
                'payment_status' => 'paid',
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process plan upgrade: ' . $e->getMessage(), [
                'subscription_id' => $subscription->id,
                'invoice_id' => $invoice->id,
                'upgrade_plan_id' => $invoice->upgrade_plan_id ?? null
            ]);
            throw $e;
        }
    }

    /**
     * Handle HitPay webhook
     */
    public function webhook(Request $request)
    {
        try {
            $payload = $request->getContent();
            $signature = $request->header('X-Signature');

            Log::info('Webhook received', [
                'payload' => $payload,
                'signature' => $signature
            ]);

            // Verify webhook signature
            if (!$this->hitPayService->verifyWebhookSignature($payload, $signature)) {
                Log::warning('Invalid HitPay webhook signature');
                return response('Unauthorized', 401);
            }

            $data = json_decode($payload, true);

            if (!$data) {
                Log::error('Invalid JSON payload in webhook');
                return response('Invalid payload', 400);
            }

            // Process the payment through HitPayService
            $result = $this->hitPayService->processWebhookPayment($data);

            if ($result['success']) {
                $transaction = $result['transaction'];

                // If payment is completed, update invoice and subscription
                if ($result['mapped_status'] === 'paid') {
                    $this->updateInvoiceAndSubscription($transaction->invoice, $transaction);
                }

                Log::info('Webhook processed successfully', [
                    'transaction_id' => $transaction->id,
                    'mapped_status' => $result['mapped_status']
                ]);

                return response('OK', 200);
            } else {
                Log::error('Webhook processing failed: ' . $result['error']);
                return response('Processing failed', 500);
            }
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response('Server error', 500);
        }
    }

    /**
     * Success page
     */
    public function success()
    {
        return view('tenant.billing.payment-success');
    }

    public function checkStatus($transactionId)
    {
        try {
            $transaction = PaymentTransaction::find($transactionId);

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            $result = $this->hitPayService->getPaymentStatus($transaction->transaction_reference);

            if ($result['success']) {
                // Map HitPay status to your enum values
                $mappedStatus = match (strtolower($result['status'])) {
                    'completed', 'succeeded', 'success' => 'paid',
                    'failed', 'error' => 'failed',
                    'pending', 'processing' => 'pending',
                    'refunded' => 'refunded',
                    default => 'pending'
                };

                // Update transaction status with enum-compliant value
                $transaction->update([
                    'status' => $mappedStatus, // ✅ Only use enum values
                    'last_status_check' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'hitpay_status' => $result['status'],
                    'mapped_status' => $mappedStatus,
                    'transaction' => $transaction->fresh(),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to check payment status'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Status check failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Status check failed'
            ], 500);
        }
    }

    /**
     * ✅ NEW: Try to sync to central admin (public API, no authentication)
     */
    private function trySyncToCentralAdmin(PaymentTransaction $transaction)
    {
        // ✅ Skip if central admin URL is not configured
        if (empty($this->centralAdminApiUrl)) {
            Log::info('Central admin sync skipped - URL not configured', [
                'transaction_id' => $transaction->id
            ]);

            $transaction->update([
                'central_admin_sync_status' => 'skipped',
                'central_admin_response' => json_encode([
                    'message' => 'Central admin URL not configured',
                    'skipped_at' => now()->toISOString()
                ])
            ]);

            return;
        }

        try {
            $invoice = $transaction->invoice;
            $subscription = $invoice->subscription;
            $tenant = $subscription->tenant;

            // Map HitPay status to central admin format
            $centralStatus = match ($transaction->status) {
                'paid' => 'completed',
                'failed' => 'failed',
                'pending' => 'pending',
                'refunded' => 'refunded',
                default => 'pending'
            };

            $payload = [
                'invoice_id' => $invoice->invoice_number,
                'subscription_id' => $subscription->subscription_code ?? "SUB-{$subscription->id}",
                'domain_name' => $tenant->tenant_url ?? "{$tenant->tenant_code}.timora.ph",
                'payment_gateway' => 'hitpay',
                'transaction_reference' => $transaction->transaction_reference,
                'amount' => (float) $transaction->amount,
                'currency' => $transaction->currency ?? 'PHP',
                'status' => $centralStatus,
                'paid_at' => $transaction->paid_at?->toISOString(),
                'raw_request' => $transaction->request_payload ?? json_encode([
                    'amount' => (float) $transaction->amount,
                    'currency' => $transaction->currency ?? 'PHP',
                    'reference' => $invoice->invoice_number
                ]),
                'raw_response' => $transaction->response_payload ?? json_encode([
                    'status' => $centralStatus,
                    'txn_id' => $transaction->transaction_reference
                ]),
                'retry_count' => $transaction->retry_count ?? 0,
                'last_status_check' => $transaction->last_status_check?->toISOString() ?? now()->toISOString(),
                // Additional metadata
                'tenant_id' => $tenant->id,
                'tenant_code' => $tenant->tenant_code,
                'invoice_type' => $invoice->invoice_type,
                'license_count' => $subscription->active_license,
                'billing_cycle' => $subscription->billing_cycle,
                // ✅ NEW: Add source identifier
                'source_system' => 'vertex_tenant',
                'source_url' => request()->getSchemeAndHttpHost(),
            ];

            Log::info('Attempting central admin sync (public API)', [
                'transaction_id' => $transaction->id,
                'invoice_id' => $invoice->invoice_number,
                'central_admin_url' => $this->centralAdminApiUrl . '/api/subscription-payments',
                'tenant_code' => $tenant->tenant_code
            ]);

            // ✅ NEW: Call public API without authentication
            $response = Http::timeout(15)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'User-Agent' => 'Vertex-Tenant-System/1.0',
                ])
                ->post($this->centralAdminApiUrl . '/api/subscription-payments', $payload);

            if ($response->successful()) {
                $responseData = $response->json();

                $transaction->update([
                    'central_admin_synced_at' => now(),
                    'central_admin_response' => json_encode($responseData),
                    'central_admin_sync_status' => 'success'
                ]);

                Log::info('✅ Central admin sync successful (public API)', [
                    'transaction_id' => $transaction->id,
                    'central_admin_id' => $responseData['id'] ?? null,
                    'invoice_id' => $invoice->invoice_number,
                    'response_status' => $response->status()
                ]);

                return [
                    'success' => true,
                    'central_admin_id' => $responseData['id'] ?? null,
                    'response' => $responseData
                ];
            } else {
                Log::warning('⚠️ Central admin sync failed (public API)', [
                    'transaction_id' => $transaction->id,
                    'status_code' => $response->status(),
                    'response_body' => $response->body(),
                    'invoice_id' => $invoice->invoice_number
                ]);

                $transaction->update([
                    'central_admin_sync_status' => 'failed',
                    'central_admin_response' => json_encode([
                        'error' => $response->body(),
                        'status_code' => $response->status(),
                        'failed_at' => now()->toISOString()
                    ])
                ]);

                return [
                    'success' => false,
                    'error' => 'HTTP ' . $response->status() . ': ' . $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::warning('⚠️ Central admin sync exception (public API)', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'central_admin_url' => $this->centralAdminApiUrl
            ]);

            $transaction->update([
                'central_admin_sync_status' => 'failed',
                'central_admin_response' => json_encode([
                    'error' => $e->getMessage(),
                    'exception' => true,
                    'failed_at' => now()->toISOString()
                ])
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * ✅ NEW: Check central admin configuration (public API)
     */
    public function checkCentralAdminConfig()
    {
        $hasApiUrl = !empty($this->centralAdminApiUrl);

        $status = 'not_configured';
        $healthCheck = null;

        if ($hasApiUrl) {
            try {
                // ✅ Test connection to central admin (public endpoint)
                $response = Http::timeout(10)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'User-Agent' => 'Vertex-Tenant-System/1.0',
                    ])
                    ->get($this->centralAdminApiUrl . '/api/health');

                if ($response->successful()) {
                    $status = 'available';
                    $healthCheck = 'API is accessible';
                } else {
                    $status = 'unreachable';
                    $healthCheck = 'HTTP ' . $response->status();
                }
            } catch (\Exception $e) {
                $status = 'unreachable';
                $healthCheck = 'Connection failed: ' . $e->getMessage();
            }
        }

        return response()->json([
            'configured' => $hasApiUrl,
            'api_url' => $hasApiUrl ? $this->centralAdminApiUrl : 'Not configured',
            'authentication' => 'public',
            'status' => $status,
            'health_check' => $healthCheck,
            'last_checked' => now()->toISOString()
        ]);
    }

    /**
     * ✅ NEW: Retry failed syncs (public API)
     */
    public function retryFailedSyncs(Request $request)
    {
        if (empty($this->centralAdminApiUrl)) {
            return response()->json([
                'success' => false,
                'message' => 'Central admin URL not configured. Please set CENTRAL_ADMIN_API_URL in your .env file.'
            ], 400);
        }

        try {
            $failedTransactions = PaymentTransaction::where('status', 'paid')
                ->where(function ($query) {
                    $query->where('central_admin_sync_status', 'failed')
                        ->orWhereNull('central_admin_synced_at');
                })
                ->with(['invoice.subscription.tenant'])
                ->limit(10)
                ->get();

            $results = [
                'processed' => 0,
                'successful' => 0,
                'failed' => 0,
                'details' => []
            ];

            foreach ($failedTransactions as $transaction) {
                $results['processed']++;

                $syncResult = $this->trySyncToCentralAdmin($transaction);
                $transaction->refresh();

                if ($transaction->central_admin_sync_status === 'success') {
                    $results['successful']++;
                } else {
                    $results['failed']++;
                }

                $results['details'][] = [
                    'transaction_id' => $transaction->id,
                    'invoice_number' => $transaction->invoice->invoice_number,
                    'status' => $transaction->central_admin_sync_status,
                    'error' => $syncResult['error'] ?? null
                ];
            }

            return response()->json([
                'success' => true,
                'message' => "Retry completed: {$results['successful']} successful, {$results['failed']} failed",
                'results' => $results,
                'api_type' => 'public'
            ]);
        } catch (\Exception $e) {
            Log::error('Central admin sync retry failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Retry failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NEW: Manual sync for specific transaction (public API)
     */
    public function syncTransactionToCentralAdmin($transactionId)
    {
        try {
            $transaction = PaymentTransaction::with(['invoice.subscription.tenant'])
                ->find($transactionId);

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            $syncResult = $this->trySyncToCentralAdmin($transaction);

            return response()->json([
                'success' => $syncResult['success'],
                'message' => $syncResult['success']
                    ? 'Transaction synced successfully to public API'
                    : 'Sync failed: ' . $syncResult['error'],
                'transaction_id' => $transaction->id,
                'central_admin_id' => $syncResult['central_admin_id'] ?? null,
                'api_type' => 'public'
            ]);
        } catch (\Exception $e) {
            Log::error('Manual central admin sync failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    // public function createTestTransaction()
    // {
    //     try {
    //         // Create a test tenant if needed
    //         $tenant = \App\Models\Tenant::first();
    //         if (!$tenant) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'No tenant found. Please create a tenant first.'
    //             ], 404);
    //         }

    //         // Create a test subscription if needed
    //         $subscription = \App\Models\Subscription::where('tenant_id', $tenant->id)->first();
    //         if (!$subscription) {
    //             $subscription = \App\Models\Subscription::create([
    //                 'tenant_id' => $tenant->id,
    //                 'subscription_code' => 'SUB-TEST-' . uniqid(),
    //                 'plan_name' => 'Test Plan',
    //                 'active_license' => 10,
    //                 'billing_cycle' => 'monthly',
    //                 'amount_paid' => 5000.00,
    //                 'status' => 'active',
    //                 'subscription_start' => now(),
    //                 'subscription_end' => now()->addMonth()
    //             ]);
    //         }

    //         // Create a test invoice
    //         $invoice = \App\Models\Invoice::create([
    //             'tenant_id' => $tenant->id,
    //             'subscription_id' => 3,
    //             'invoice_number' => 'INV-TEST-' . uniqid(),
    //             'invoice_type' => 'subscription',
    //             'amount_due' => 5000.00,
    //             'tax_amount' => 600.00,
    //             'total_amount' => 5600.00,
    //             'status' => 'pending',
    //             'due_date' => now()->addDays(7),
    //             'invoice_date' => now()
    //         ]);

    //         // Create a test payment transaction
    //         $transaction = PaymentTransaction::create([
    //             'invoice_id' => $invoice->id,
    //             'subscription_id' => $subscription->id,
    //             'transaction_reference' => 'TEST_TXN_' . uniqid(),
    //             'amount' => (float) $invoice->total_amount,
    //             'currency' => 'PHP',
    //             'status' => 'paid',
    //             'paid_at' => now(),
    //             'payment_gateway' => 'hitpay',
    //             'request_payload' => json_encode([
    //                 'amount' => (float) $invoice->total_amount,
    //                 'currency' => 'PHP',
    //                 'reference' => $invoice->invoice_number,
    //                 'test' => true
    //             ]),
    //             'response_payload' => json_encode([
    //                 'status' => 'completed',
    //                 'txn_id' => 'TEST_TXN_' . uniqid(),
    //                 'test' => true
    //             ])
    //         ]);

    //         // Try to sync to central admin
    //         $syncResult = $this->trySyncToCentralAdmin($transaction);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Test transaction created and sync attempted',
    //             'data' => [
    //                 'tenant_id' => $tenant->id,
    //                 'subscription_id' => $subscription->id,
    //                 'invoice_id' => $invoice->id,
    //                 'transaction_id' => $transaction->id,
    //                 'transaction_reference' => $transaction->transaction_reference
    //             },
    //             'sync_result' => $syncResult,
    //             'central_admin_status' => $transaction->fresh()->central_admin_sync_status,
    //             'central_admin_response' => $transaction->fresh()->central_admin_response
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Test transaction creation failed: ' . $e->getMessage());

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Test transaction creation failed: ' . $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ], 500);
    //     }
    // }
}
