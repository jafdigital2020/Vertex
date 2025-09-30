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

class PaymentController extends Controller
{
    private $hitPayService;

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

            // If this is a subscription invoice with license overage, handle consolidation
            $subscription = $invoice->subscription;
            if ($subscription && $invoice->invoice_type === 'subscription') {
                $this->updateSubscription($subscription, $invoice);

                // ✅ NEW: Mark consolidated license overage invoices as paid
                if ($invoice->license_overage_count > 0) {
                    $this->markConsolidatedInvoicesAsPaid($subscription->tenant_id, $invoice);
                }
            }

            // Update transaction
            $transaction->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

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

            // ✅ IMPORTANT: Don't upgrade license count after overage payment

            $subscription->update([
                'status' => 'active',
                'payment_status' => 'paid',
                'subscription_end' => $newEndDate,
                'renewed_at' => now(),
                'next_renewal_date' => $newEndDate,
                'active_license' => $currentActiveLicense, // ✅ Keep same base license count
                'amount_paid' => $currentAmountPaid, // ✅ Keep same plan price
            ]);

            Log::info('Subscription renewed without changing base license count', [
                'subscription_id' => $subscription->id,
                'new_end_date' => $newEndDate->toDateString(),
                'active_license' => $currentActiveLicense, // ✅ Unchanged
                'amount_paid' => $currentAmountPaid, // ✅ Unchanged
                'invoice_had_overage' => $invoice->license_overage_count > 0,
                'overage_count_paid' => $invoice->license_overage_count ?? 0
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update subscription: ' . $e->getMessage());
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
}
