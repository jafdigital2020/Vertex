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
        $today = Carbon::today();
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
            $nextPeriod = $sub->getNextPeriod();

            // ✅ FIXED: Check if consolidated renewal invoice already exists
            $existingRenewalInvoice = Invoice::where('subscription_id', $sub->id)
                ->where('tenant_id', $sub->tenant_id)
                ->where('invoice_type', 'subscription')
                ->where('period_start', $nextPeriod['start'])
                ->where('period_end', $nextPeriod['end'])
                ->first();

            if ($existingRenewalInvoice) {
                $this->line("Skip sub {$sub->id}: consolidated renewal invoice already exists ({$existingRenewalInvoice->invoice_number})");

                // ✅ STILL SEND EMAIL: Even if invoice exists, send email if not sent yet
                $this->sendInvoiceEmail($existingRenewalInvoice, $sub);
                continue;
            }

            try {
                // ✅ USE CONSOLIDATED: Use LicenseOverageService to create consolidated invoice
                $invoice = $licenseService->createConsolidatedRenewalInvoice($sub);

                $this->info("Created consolidated invoice {$invoice->invoice_number} for subscription {$sub->id}");

                // ✅ SEND EMAIL: Send email for the consolidated invoice
                $this->sendInvoiceEmail($invoice, $sub);
            } catch (\Exception $e) {
                $this->error("Failed to create consolidated invoice for subscription {$sub->id}: " . $e->getMessage());
                Log::error("Failed to create consolidated renewal invoice", [
                    'subscription_id' => $sub->id,
                    'tenant_id' => $sub->tenant_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return self::SUCCESS;
    }

    private function sendInvoiceEmail($invoice, $subscription)
    {
        // Send to GlobalUser email (not Tenant)
        $recipient = $subscription->tenant?->globalUsers?->first()?->email;

        if ($recipient) {
            try {
                Mail::to($recipient)->queue(new UpcomingRenewalInvoiceMail($invoice, $subscription));
                $this->info("Invoice {$invoice->invoice_number} email queued to {$recipient}");

                Log::info("Consolidated invoice email queued", [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_type' => $invoice->invoice_type,
                    'amount_due' => $invoice->amount_due,
                    'license_overage_count' => $invoice->license_overage_count ?? 0,
                    'recipient_email' => $recipient,
                    'tenant_id' => $subscription->tenant_id,
                    'subscription_id' => $subscription->id
                ]);
            } catch (\Exception $e) {
                $this->error("Failed to send email for invoice {$invoice->invoice_number}: " . $e->getMessage());
                Log::error("Failed to send invoice email", [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            $this->warn("No Global User email found for tenant {$subscription->tenant_id}");
            Log::warning("No email found for consolidated invoice", [
                'tenant_id' => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number
            ]);
        }
    }
}
