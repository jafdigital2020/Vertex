<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\BranchAddon;
use App\Models\BranchSubscription;
use App\Models\Branch;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\HitPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BranchAddonController extends Controller
{
    protected $hitPayService;

    public function __construct(HitPayService $hitPayService)
    {
        $this->hitPayService = $hitPayService;
    }

    /**
     * Display the addon purchase page
     */
    public function index()
    {
        $user = Auth::user();
        $branch = $user->branch;

        if (!$branch) {
            return redirect()->back()->with('error', 'No branch associated with your account.');
        }

        // Get active branch subscription
        $subscription = BranchSubscription::where('branch_id', $branch->id)
            ->where('status', 'active')
            ->first();

        // Get all available addons (only 'addon' category, exclude 'upgrade')
        $addons = Addon::where('is_active', true)
            ->where('addon_category', 'addon')
            ->get();

        // Get currently active addons for this branch
        $branchAddons = BranchAddon::where('branch_id', $branch->id)
            ->where('active', true)
            ->with('addon')
            ->get();

        $activeAddons = $branchAddons->pluck('id', 'addon_id')->toArray();

        return view('tenant.addons.purchase', compact('addons', 'branch', 'subscription', 'activeAddons'));
    }

    /**
     * Process addon purchase
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'addon_id' => 'required|exists:addons,id',
            'branch_id' => 'required|exists:branches,id',
            'billing_cycle' => 'required|in:monthly,yearly'
        ]);

        try {
            DB::beginTransaction();

            $addon = Addon::findOrFail($request->addon_id);
            $branch = Branch::findOrFail($request->branch_id);
            $user = Auth::user();

            // Check if addon already exists for this branch (active or pending payment)
            $existingBranchAddon = BranchAddon::where('branch_id', $branch->id)
                ->where('addon_id', $addon->id)
                ->first();

            // If addon exists with pending payment, redirect to billing page
            if ($existingBranchAddon && !$existingBranchAddon->active) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'redirect' => true,
                    'redirect_url' => url('/billing'),
                    'message' => 'You have a pending payment for this addon. Please complete the payment in the billing page.'
                ], 409); // 409 Conflict status code
            }

            // If addon is already active
            if ($existingBranchAddon && $existingBranchAddon->active) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'This addon is already active for your branch.'
                ], 400);
            }

            // Calculate price based on billing cycle
            $price = $addon->price;
            $billingCycle = $request->billing_cycle;

            if ($billingCycle === 'yearly') {
                // Apply 10% discount for yearly billing
                $price = $price * 12 * 0.9;
            }

            // Calculate VAT (12%)
            $vatPercentage = 12;
            $subtotal = $price;
            $vatAmount = $subtotal * ($vatPercentage / 100);
            $totalAmount = $subtotal + $vatAmount;

            // Generate invoice number
            $invoiceNumber = 'INV-ADDON-' . strtoupper(uniqid());

            // Get active branch subscription
            $branchSubscription = BranchSubscription::where('branch_id', $branch->id)
                ->where('status', 'active')
                ->first();

            // Calculate addon period
            $startDate = Carbon::now();
            $endDate = $billingCycle === 'yearly' ? $startDate->copy()->addYear() : $startDate->copy()->addMonth();

            // Create BranchAddon record (inactive until payment confirmed)
            $branchAddon = BranchAddon::create([
                'branch_id' => $branch->id,
                'addon_id' => $addon->id,
                'active' => false, // Will be activated by webhook
                'start_date' => $startDate,
                'end_date' => $endDate,
                'billing_cycle' => $billingCycle,
                'price_paid' => $totalAmount,
                'metadata' => json_encode([
                    'addon_name' => $addon->name,
                    'addon_price' => $addon->price,
                    'subtotal' => $subtotal,
                    'vat_amount' => $vatAmount,
                    'vat_percentage' => $vatPercentage,
                ]),
            ]);

            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'tenant_id' => tenant('id'),
                'branch_id' => $branch->id,
                'branch_subscription_id' => $branchSubscription?->id,
                'invoice_type' => 'addon',
                'amount_due' => $totalAmount,
                'amount_paid' => 0,
                'subscription_amount' => $price,
                'calculated_subtotal' => $subtotal,
                'calculated_vat_percentage' => $vatPercentage,
                'calculated_vat_amount' => $vatAmount,
                'currency' => env('HITPAY_CURRENCY', 'PHP'),
                'status' => 'pending',
                'issued_at' => Carbon::now(),
                'due_date' => Carbon::now()->addDays(7),
                'period_start' => $startDate,
                'period_end' => $endDate,
                'notes' => "Addon Purchase: {$addon->name} - {$billingCycle} billing",
                'metadata' => json_encode([
                    'addon_id' => $addon->id,
                    'addon_name' => $addon->name,
                    'billing_cycle' => $billingCycle,
                    'addon_price' => $addon->price,
                    'branch_addon_id' => $branchAddon->id,
                ]),
            ]);

            // Link invoice to branch addon
            $branchAddon->update(['invoice_id' => $invoice->id]);

            // Create HitPay payment request - redirect to payment status page
            $returnUrl = env('HITPAY_ADDON_PAYMENT_URL', route('addon.payment.status'));

            $hitpayPayload = [
                'amount' => 1,
                'currency' => env('HITPAY_CURRENCY', 'PHP'),
                'email' => $user->email ?? 'customer@example.com',
                'name' => $branch->name ?? 'Customer',
                'phone' => null,
                'purpose' => "Addon: {$addon->name}",
                'reference_number' => $invoiceNumber,
                'redirect_url' => $returnUrl,
                'webhook' => env('HITPAY_WEBHOOK_URL'),
                'send_email' => true,
                'meta' => json_encode([
                    'type' => 'addon',
                    'invoice_id' => $invoice->id,
                    'branch_addon_id' => $branchAddon->id,
                    'addon_id' => $addon->id,
                    'addon_name' => $addon->name,
                    'billing_cycle' => $billingCycle,
                ]),
            ];

            try {
                $client = new \GuzzleHttp\Client();

                $response = $client->request('POST', env('HITPAY_URL'), [
                    'form_params' => $hitpayPayload,
                    'headers' => [
                        'X-BUSINESS-API-KEY' => env('HITPAY_API_KEY'),
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                ]);

                $hitpayData = json_decode($response->getBody(), true);

                // Create payment record
                Payment::create([
                    'tenant_id' => tenant('id'),
                    'branch_subscription_id' => $branchSubscription?->id,
                    'invoice_id' => $invoice->id,
                    'payment_method' => 'hitpay',
                    'payment_gateway' => 'hitpay',
                    'transaction_reference' => $hitpayData['reference_number'] ?? $invoiceNumber,
                    'amount' => $totalAmount,
                    'currency' => env('HITPAY_CURRENCY', 'PHP'),
                    'status' => 'pending',
                    'payment_date' => Carbon::now(),
                    'checkout_url' => $hitpayData['url'] ?? null,
                    'receipt_url' => $hitpayData['receipt_url'] ?? null,
                    'payment_provider' => $hitpayData['payment_provider']['code'] ?? null,
                    'gateway_response' => json_encode($hitpayData),
                    'meta' => json_encode([
                        'type' => 'addon',
                        'branch_addon_id' => $branchAddon->id,
                        'addon_id' => $addon->id,
                        'addon_name' => $addon->name,
                        'billing_cycle' => $billingCycle,
                    ]),
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'checkoutUrl' => $hitpayData['url'] ?? null,
                    'invoice_id' => $invoice->id,
                    'reference' => $hitpayData['reference_number'] ?? $invoiceNumber,
                    'message' => 'Payment created. Complete payment to activate addon.'
                ]);
            } catch (\Exception $e) {
                Log::error('HitPay Payment Creation Failed for Addon', [
                    'error' => $e->getMessage(),
                    'invoice_id' => $invoice->id,
                    'addon_id' => $addon->id
                ]);

                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Payment gateway error: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Addon Purchase Failed', [
                'error' => $e->getMessage(),
                'addon_id' => $request->addon_id,
                'branch_id' => $request->branch_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process addon purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle payment callback from HitPay
     */
    public function paymentCallback(Request $request, $invoiceId)
    {
        try {
            $invoice = Invoice::findOrFail($invoiceId);
            $payment = Payment::where('invoice_id', $invoice->id)->latest()->first();

            if (!$payment) {
                return redirect()->route('addon.payment.status', ['status' => 'failed', 'message' => 'Payment record not found']);
            }

            // Check payment status
            $status = $request->query('status');
            $reference = $request->query('reference');

            // Note: Addon activation is handled by HitPay webhook (processAddonPayment)
            // This callback is just for user redirect and confirmation

            // Redirect to payment status page with status
            return redirect()->route('addon.payment.status', [
                'status' => $status ?? 'pending',
                'reference' => $reference
            ]);
        } catch (\Exception $e) {
            Log::error('Addon Payment Callback Error', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoiceId
            ]);

            return redirect()->route('addon.payment.status', [
                'status' => 'failed',
                'message' => 'An error occurred processing your payment.'
            ]);
        }
    }

    /**
     * Show payment status page for addon purchases
     */
    public function showPaymentStatus(Request $request)
    {
        return view('tenant.addons.payment-status');
    }

    /**
     * Cancel/deactivate an addon
     */
    public function cancel(Request $request)
    {
        $request->validate([
            'branch_addon_id' => 'required|exists:branch_addons,id'
        ]);

        try {
            $branchAddon = BranchAddon::findOrFail($request->branch_addon_id);

            $branchAddon->update([
                'active' => false,
                'end_date' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Addon cancelled successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Addon Cancellation Failed', [
                'error' => $e->getMessage(),
                'branch_addon_id' => $request->branch_addon_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel addon: ' . $e->getMessage()
            ], 500);
        }
    }
}
