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

        // Get all available addons
        $addons = Addon::where('is_active', true)->get();

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

            // Check if user has permission to purchase for this branch
            $user = Auth::user();
            if ($user->branch_id != $branch->id && !$user->hasRole('Admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to purchase addons for this branch.'
                ], 403);
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

            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'tenant_id' => tenant('id'),
                'branch_id' => $branch->id,
                'branch_subscription_id' => $branchSubscription?->id,
                'invoice_type' => 'addon',
                'amount_due' => $totalAmount,
                'subscription_amount' => $price,
                'calculated_subtotal' => $subtotal,
                'calculated_vat_percentage' => $vatPercentage,
                'calculated_vat_amount' => $vatAmount,
                'currency' => 'PHP',
                'status' => 'pending',
                'due_date' => Carbon::now()->addDays(7),
                'notes' => "Addon Purchase: {$addon->name} - {$billingCycle} billing",
                'metadata' => json_encode([
                    'addon_id' => $addon->id,
                    'addon_name' => $addon->name,
                    'billing_cycle' => $billingCycle,
                    'addon_price' => $addon->price,
                ]),
            ]);

            // Create HitPay payment request
            $returnUrl = route('addon.payment.callback', ['invoice' => $invoice->id]);

            $paymentData = [
                'amount' => number_format($totalAmount, 2, '.', ''),
                'currency' => 'PHP',
                'reference_number' => $invoiceNumber,
                'webhook' => config('services.hitpay.webhook_url'),
                'redirect_url' => $returnUrl,
                'purpose' => "Addon: {$addon->name}",
                'name' => $branch->name ?? 'Customer',
                'email' => $user->email ?? 'customer@example.com',
            ];

            try {
                $client = new \GuzzleHttp\Client([
                    'base_uri' => config('services.hitpay.base_url', 'https://api.hitpayapp.com/'),
                    'timeout' => 30,
                    'verify' => true,
                    'headers' => [
                        'X-BUSINESS-API-KEY' => config('services.hitpay.api_key'),
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Accept' => 'application/json',
                    ]
                ]);

                $response = $client->post('v1/payment-requests', [
                    'form_params' => $paymentData
                ]);

                $responseData = json_decode($response->getBody(), true);

                if ($response->getStatusCode() === 201 && isset($responseData['id'])) {
                    // Create payment record
                    Payment::create([
                        'tenant_id' => tenant('id'),
                        'branch_subscription_id' => $branchSubscription?->id,
                        'invoice_id' => $invoice->id,
                        'payment_method' => 'hitpay',
                        'transaction_id' => $responseData['id'],
                        'amount' => $totalAmount,
                        'currency' => 'PHP',
                        'status' => 'pending',
                        'payment_date' => Carbon::now(),
                        'metadata' => json_encode([
                            'payment_url' => $responseData['url'] ?? $responseData['payment_url'],
                            'raw_response' => $responseData,
                        ]),
                    ]);

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'payment_url' => $responseData['url'] ?? $responseData['payment_url'],
                        'invoice_id' => $invoice->id,
                        'reference' => $responseData['id'],
                        'message' => 'Redirecting to payment gateway...'
                    ]);
                } else {
                    throw new \Exception('Invalid response from HitPay API');
                }
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
                return redirect()->route('addons.purchase')
                    ->with('error', 'Payment record not found.');
            }

            // Check payment status
            $status = $request->query('status');
            $reference = $request->query('reference');

            if ($status === 'completed' && $payment->status !== 'completed') {
                DB::beginTransaction();

                // Update payment status
                $payment->update([
                    'status' => 'completed',
                    'metadata' => json_encode(array_merge(
                        json_decode($payment->metadata, true) ?? [],
                        ['callback_data' => $request->all()]
                    ))
                ]);

                // Update invoice status
                $invoice->update([
                    'status' => 'paid',
                    'amount_paid' => $invoice->amount_due,
                    'paid_date' => Carbon::now(),
                ]);

                // Activate the addon
                $metadata = json_decode($invoice->metadata, true);
                $addonId = $metadata['addon_id'] ?? null;
                $billingCycle = $metadata['billing_cycle'] ?? 'monthly';

                if ($addonId) {
                    $startDate = Carbon::now();
                    $endDate = $billingCycle === 'yearly'
                        ? $startDate->copy()->addYear()
                        : $startDate->copy()->addMonth();

                    // Check if addon already exists for this branch
                    $branchAddon = BranchAddon::where('branch_id', $invoice->branch_id)
                        ->where('addon_id', $addonId)
                        ->first();

                    if ($branchAddon) {
                        // Update existing addon
                        $branchAddon->update([
                            'active' => true,
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                        ]);
                    } else {
                        // Create new branch addon
                        BranchAddon::create([
                            'branch_id' => $invoice->branch_id,
                            'addon_id' => $addonId,
                            'active' => true,
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                        ]);
                    }
                }

                DB::commit();

                return redirect()->route('addons.purchase')
                    ->with('success', 'Addon purchased successfully! Your new features are now active.');
            } elseif ($status === 'failed' || $status === 'canceled') {
                $payment->update(['status' => $status]);
                $invoice->update(['status' => 'failed']);

                return redirect()->route('addons.purchase')
                    ->with('error', 'Payment was ' . $status . '. Please try again.');
            }

            return redirect()->route('addons.purchase')
                ->with('info', 'Payment status: ' . ($status ?? 'pending'));
        } catch (\Exception $e) {
            Log::error('Addon Payment Callback Error', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoiceId
            ]);

            return redirect()->route('addons.purchase')
                ->with('error', 'An error occurred processing your payment.');
        }
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
            $user = Auth::user();

            // Check permissions
            if ($user->branch_id != $branchAddon->branch_id && !$user->hasRole('Admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to cancel this addon.'
                ], 403);
            }

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
