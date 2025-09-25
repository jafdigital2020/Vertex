<?php

namespace App\Console\Commands;

use App\Mail\UpcomingRenewalInvoiceMail;
use App\Models\BranchSubscription;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\SubscriptionBillingService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class GenerateInvoices extends Command
{
    protected $signature = 'invoices:generate';
    protected $description = 'Generate branch subscription invoices 7 days before next_renewal_date and email recipients';

    public function handle()
    {
        $today      = Carbon::today();
        $targetDate = $today->copy()->addDays(7);

        $branchSubs = BranchSubscription::with(['branch'])
            ->where('status', 'active')
            ->whereDate('next_renewal_date', '=', $targetDate)
            ->get();

        if ($branchSubs->isEmpty()) {
            $this->warn("No branch subscriptions due in 7 days (target: {$targetDate->toDateString()}).");
            return self::SUCCESS;
        }

        /** @var SubscriptionBillingService $billing */
        $billing = app(SubscriptionBillingService::class);

        foreach ($branchSubs as $sub) {
            // derive next billing period
            if (method_exists($sub, 'getNextPeriod')) {
                $nextPeriod = $sub->getNextPeriod();
            } else {
                $current    = method_exists($sub, 'getCurrentPeriod')
                    ? $sub->getCurrentPeriod()
                    : $this->deriveCurrentPeriodFallback($sub);
                $nextPeriod = $this->deriveNextPeriodFallback($sub, $current);
            }

            // check if invoice already exists
            $existing = Invoice::query()
                ->where('branch_subscription_id', $sub->id)
                ->where('invoice_type', 'branch_subscription')
                ->where('period_start', $nextPeriod['start'])
                ->where('period_end', $nextPeriod['end'])
                ->first();

            if ($existing) {
                $this->line("Skip branch sub {$sub->id}: invoice exists ({$existing->invoice_number})");
                $invoice = $existing;
            } else {
                try {
                    $invoice = $billing->createBranchSubscriptionRenewalInvoice($sub);
                    $this->info("Created branch invoice {$invoice->invoice_number} for branch subscription {$sub->id}");
                } catch (\Throwable $e) {
                    $this->error("Failed to create invoice for branch subscription {$sub->id}: " . $e->getMessage());
                    Log::error("Failed to create branch renewal invoice", [
                        'subscription_id' => $sub->id,
                        'tenant_id'       => $sub->tenant_id,
                        'branch_id'       => $sub->branch_id,
                        'error'           => $e->getMessage(),
                    ]);
                    continue;
                }
            }

            // ✅ ensure payment exists for this invoice
            $this->ensurePaymentForInvoice($invoice);

            // send invoice email
            $this->sendInvoiceEmail($billing, $invoice, $sub);
        }

        return self::SUCCESS;
    }

    /** Email all active users in the branch (adjust to restrict by role if desired). */
    private function sendInvoiceEmail(
        SubscriptionBillingService $billing,
        Invoice $invoice,
        BranchSubscription $subscription
    ): void {
        $recipients = $billing->getBranchRecipientEmails($subscription->tenant_id, $subscription->branch_id);

        if (empty($recipients)) {
            $this->warn("No recipient emails found for tenant {$subscription->tenant_id}, branch {$subscription->branch_id}");
            Log::warning("No recipients for branch invoice", [
                'tenant_id' => $subscription->tenant_id,
                'branch_id' => $subscription->branch_id,
                'invoice_id' => $invoice->id,
            ]);
            return;
        }

        foreach ($recipients as $email) {
            try {
                Mail::to($email)->queue(new UpcomingRenewalInvoiceMail($invoice, $subscription));
                $this->info("Queued {$invoice->invoice_number} to {$email}");
            } catch (\Throwable $e) {
                $this->error("Failed to send {$invoice->invoice_number} to {$email}: " . $e->getMessage());
                Log::error("Invoice email failed", [
                    'invoice_id' => $invoice->id,
                    'email'      => $email,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        Log::info("Branch invoice email batch queued", [
            'invoice_id'     => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'invoice_type'   => $invoice->invoice_type,
            'amount_due'     => $invoice->amount_due,
            'recipients'     => $recipients,
            'branch_name'    => $subscription->branch->name ?? null,
            'tenant_id'      => $subscription->tenant_id,
            'branch_id'      => $subscription->branch_id,
            'subscription_id' => $subscription->id
        ]);
    }

    /* ---------------- Fallback period derivation (only if model lacks helpers) --------------- */

    private function deriveCurrentPeriodFallback(BranchSubscription $sub): array
    {
        $now     = Carbon::now();
        $billing = strtolower((string) $sub->billing_period); // 'monthly' | 'yearly'

        if ($billing === 'yearly') {
            $start = Carbon::parse($sub->subscription_start)->startOfDay();
            while ($start->copy()->addYear()->lte($now)) {
                $start->addYear();
            }
            $end = $start->copy()->addYear()->subDay()->endOfDay();
        } else {
            $start = $now->copy()->startOfMonth();
            $end   = $now->copy()->endOfMonth();
        }

        return ['start' => $start->toDateString(), 'end' => $end->toDateString()];
    }

    private function deriveNextPeriodFallback(BranchSubscription $sub, array $current): array
    {
        $billing = strtolower((string) $sub->billing_period);
        $start   = Carbon::parse($current['start']);
        $end     = Carbon::parse($current['end']);

        if ($billing === 'yearly') {
            $nextStart = $start->copy()->addYear()->toDateString();
            $nextEnd   = $end->copy()->addYear()->toDateString();
        } else {
            $nextStart = $start->copy()->addMonthNoOverflow()->toDateString();
            $nextEnd   = Carbon::parse($nextStart)->endOfMonth()->toDateString();
        }

        return ['start' => $nextStart, 'end' => $nextEnd];
    }

    private function ensurePaymentForInvoice(Invoice $invoice): void
    {
        $exists = Payment::where('branch_subscription_id', $invoice->branch_subscription_id)
            ->where('transaction_reference', $invoice->invoice_number)
            ->exists();

        if ($exists) {
            $this->line("Skip payment: already exists for invoice {$invoice->invoice_number}");
            return;
        }

        try {
            // 🔹 Create HitPay checkout session
            $hitpayData = $this->createHitpayPayment(
                $invoice->amount_due,
                [
                    'email'        => $invoice->branch->email ?? null, // adjust based on branch/subscription data
                    'phone_number' => $invoice->branch->mobile_number ?? null,
                    'plan_slug'    => 'renewal',
                ],
                $invoice->branch->name ?? 'Branch Subscriber'
            );

            if (!$hitpayData || empty($hitpayData['id'])) {
                $this->error("HitPay payment creation failed for invoice {$invoice->invoice_number}");
                return;
            }

            // 🔹 Store payment record with HitPay checkout URL
            Payment::create([
                'branch_subscription_id' => $invoice->branch_subscription_id,
                'amount'                 => $invoice->amount_due,
                'currency'               => $invoice->currency ?? env('HITPAY_CURRENCY', 'PHP'),
                'status'                 => 'pending',
                'payment_gateway'        => 'hitpay',
                'transaction_reference'  => $invoice->invoice_number,
                'gateway_response'       => $hitpayData,
                'checkout_url'           => $hitpayData['url'] ?? null,
                'payment_provider'       => 'hitpay',
                'meta'                   => [
                    'type'       => 'renewal',
                    'invoice_id' => $invoice->id,
                    'hitpay_id'  => $hitpayData['id'] ?? null,
                ],
            ]);

            $this->info("Created HitPay renewal payment for invoice {$invoice->invoice_number}");
        } catch (Throwable $e) {
            $this->error("Failed to create HitPay payment for invoice {$invoice->invoice_number}: " . $e->getMessage());
            Log::error("HitPay payment creation failed", [
                'invoice_id'        => $invoice->id,
                'subscription_id'   => $invoice->branch_subscription_id,
                'error'             => $e->getMessage(),
            ]);
        }
    }


    private function createHitpayPayment($amount, array $request, string $buyerName, $invoice = null, $subscription = null)
    {
        $buyerEmail   = $request['email'] ?? null;
        $buyerPhone   = $request['phone_number'] ?? null;
        $purpose      = 'Subscription renewal payment';
        $redirectUrl  = env('HITPAY_REDIRECT_URL', config('app.url') . '/payment-success');
        $webhookUrl   = env('HITPAY_WEBHOOK_URL');

        try {
            $client = new \GuzzleHttp\Client();

            $hitpayPayload = [
                'amount'           => round($amount, 2),
                'currency'         => env('HITPAY_CURRENCY', 'PHP'),
                'email'            => $buyerEmail,
                'name'             => $buyerName,
                'phone'            => $buyerPhone,
                'purpose'          => $purpose,
                'reference_number' =>  $invoice?->invoice_number,
                'redirect_url'     => $redirectUrl,
                'webhook'          => $webhookUrl,
                'send_email'       => true,

                // 👇 meta is critical for unified webhook
                'meta' => json_encode([
                    'type'            => 'monthly-starter',
                    'invoice_id'      => $invoice->id ?? null,
                    'subscription_id' => $subscription->id ?? null,
                ]),
            ];

            $response = $client->request('POST', env('HITPAY_URL'), [
                'form_params' => $hitpayPayload,
                'headers'     => [
                    'X-BUSINESS-API-KEY' => env('HITPAY_API_KEY'),
                    'Content-Type'       => 'application/x-www-form-urlencoded',
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('HitPay API call failed', ['exception' => $e]);
            return null;
        }
    }
}
