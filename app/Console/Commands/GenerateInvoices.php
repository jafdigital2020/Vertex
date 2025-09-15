<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\LicenseOverageService;
use App\Mail\UpcomingRenewalInvoiceMail;

class GenerateInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoices 7 days before next_renewal_date and email tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today      = Carbon::today();
        $targetDate = $today->copy()->addDays(7);

        // Eager-load tenant.globalUser para ready ang email
        $subs = Subscription::with(['tenant.globalUsers', 'plan'])
            ->where('status', 'active')
            ->whereDate('next_renewal_date', '=', $targetDate)
            ->get();

        if ($subs->isEmpty()) {
            $this->warn("No subscriptions due in 7 days (target: {$targetDate->toDateString()}).");
            return self::SUCCESS;
        }

        $licenseService = app(LicenseOverageService::class);

        foreach ($subs as $sub) {
            $periodStart = Carbon::parse($sub->next_renewal_date)->startOfDay();
            $periodEnd   = $sub->billing_cycle === 'yearly'
                ? $periodStart->copy()->addYear()
                : $periodStart->copy()->addMonth();

            // Prevent duplicate invoice for the same period
            $existing = Invoice::where('subscription_id', $sub->id)
                ->whereDate('period_start', $periodStart)
                ->whereDate('period_end', $periodEnd)
                ->first();

            if ($existing) {
                $this->line("Skip sub {$sub->id}: invoice already exists for {$periodStart->toDateString()}–{$periodEnd->toDateString()}");
                continue;
            }

            // Create consolidated invoice (subscription + any license overage)
            $invoice = $licenseService->createConsolidatedRenewalInvoice($sub);

            // Compute amount
            $base     = optional($sub->plan)->price ?? $sub->amount_paid ?? 0;
            $discount = property_exists($sub, 'discount') ? ($sub->discount ?? 0) : 0;
            $tax      = property_exists($sub, 'tax') ? ($sub->tax ?? 0) : 0;
            $amountDue = max(0, $base + $tax - $discount);

            $invoice = Invoice::create([
                'subscription_id' => $sub->id,
                'tenant_id'       => $sub->tenant_id,
                'invoice_number'  => 'INV-' . strtoupper(uniqid()),
                'amount_due'      => $amountDue,
                'amount_paid'     => 0,
                'currency'        => $sub->currency ?? 'PHP',
                'due_date'        => $periodStart,   // due on renewal day
                'status'          => 'pending',
                'issued_at'       => now(),
                'paid_at'         => null,
                'period_start'    => $periodStart,
                'period_end'      => $periodEnd,
            ]);

            // Send to GlobalUser email (not Tenant)
            $recipient = $sub->tenant?->globalUsers?->first()?->email;

            if ($recipient) {
                Mail::to($recipient)->queue(new UpcomingRenewalInvoiceMail($invoice, $sub));
                $this->info("Invoice {$invoice->invoice_number} queued to {$recipient}");
                Log::info("Invoice email queued", [
                    'invoice_number' => $invoice->invoice_number,
                    'recipient_email' => $recipient,
                    'tenant_id' => $sub->tenant_id,
                    'subscription_id' => $sub->id
                ]);
            } else {
                $this->warn("No Global User email found for tenant {$sub->tenant_id}");
                Log::warning("No email found for invoice", [
                    'tenant_id' => $sub->tenant_id,
                    'subscription_id' => $sub->id,
                    'invoice_number' => $invoice->invoice_number
                ]);
            }
        }

        return self::SUCCESS;
    }
}
