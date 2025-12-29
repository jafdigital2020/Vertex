<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Tenant;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\UpcomingRenewalInvoiceMail;

class GenerateManualInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-manual 
                            {tenant_id : The tenant ID to generate invoice for}
                            {amount : The invoice amount}
                            {--details= : Invoice details/description}
                            {--discount=0 : Discount percentage (0-100)}
                            {--due-days=7 : Days until invoice is due (default: 7)}
                            {--send-email : Send email notification to tenant}
                            {--vat=12 : VAT percentage (default: 12%)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually generate an invoice with custom amount and details';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->argument('tenant_id');
        $amount = (float) $this->argument('amount');
        $details = $this->option('details');
        $discount = (float) $this->option('discount');
        $dueDays = (int) $this->option('due-days');
        $sendEmail = $this->option('send-email');
        $vatPercentage = (float) $this->option('vat');

        // Validate discount percentage
        if ($discount < 0 || $discount > 100) {
            $this->error("Discount must be between 0 and 100.");
            return self::FAILURE;
        }

        // Validate tenant exists
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            $this->error("Tenant with ID {$tenantId} not found.");
            return self::FAILURE;
        }

        // Get subscription if exists
        $subscription = Subscription::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();

        if (!$subscription) {
            $this->warn("No active subscription found for tenant {$tenantId}. Invoice will be created without subscription reference.");
        }

        // Calculate discount, VAT and total
        $originalAmount = $amount;
        $discountAmount = round(($originalAmount * $discount) / 100, 2);
        $subtotal = $originalAmount - $discountAmount;
        $vatAmount = round(($subtotal * $vatPercentage) / 100, 2);
        $totalAmount = $subtotal + $vatAmount;

        // Prepare table data
        $tableData = [
            ['Tenant ID', $tenantId],
            ['Tenant Name', $tenant->company_name ?? $tenant->id],
            ['Original Amount', 'PHP ' . number_format($originalAmount, 2)],
        ];

        // Add discount row only if discount is applied
        if ($discount > 0) {
            $tableData[] = ['Discount (' . $discount . '%)', '- PHP ' . number_format($discountAmount, 2)];
        }

        $tableData = array_merge($tableData, [
            ['Subtotal', 'PHP ' . number_format($subtotal, 2)],
            ['VAT (' . $vatPercentage . '%)', 'PHP ' . number_format($vatAmount, 2)],
            ['Total Amount', 'PHP ' . number_format($totalAmount, 2)],
            ['Details', $details ?: 'N/A'],
            ['Due In', $dueDays . ' days'],
            ['Send Email', $sendEmail ? 'Yes' : 'No'],
        ]);

        // Confirm with user
        $this->info("Invoice Details:");
        $this->table(['Field', 'Value'], $tableData);

        if (!$this->confirm('Do you want to create this invoice?', true)) {
            $this->info('Invoice creation cancelled.');
            return self::SUCCESS;
        }

        try {
            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber('manual');

            // Prepare full details with discount info
            $fullDetails = $details ?: 'Manual Invoice';
            if ($discount > 0) {
                $fullDetails .= " | Discount: {$discount}% (PHP " . number_format($discountAmount, 2) . ") | Original Amount: PHP " . number_format($originalAmount, 2);
            }

            // Create invoice
            $invoice = Invoice::create([
                'tenant_id' => $tenantId,
                'subscription_id' => $subscription?->id,
                'invoice_type' => 'implementation_fee',
                'invoice_number' => $invoiceNumber,
                'subscription_amount' => $subtotal,
                'subtotal' => $subtotal,
                'vat_percentage' => $vatPercentage,
                'vat_amount' => $vatAmount,
                'amount_due' => $totalAmount,
                'currency' => 'PHP',
                'status' => 'pending',
                'due_date' => now()->addDays($dueDays),
                'issued_at' => now(),
                'period_start' => now()->startOfDay(),
                'period_end' => now()->addDays($dueDays)->endOfDay(),
            ]);

            $this->info("✅ Invoice {$invoice->invoice_number} created successfully!");

            // Create invoice item with details
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $fullDetails,
                'quantity' => 1,
                'rate' => $subtotal,
                'amount' => $subtotal,
            ]);

            Log::info('Manual invoice created', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'tenant_id' => $tenantId,
                'original_amount' => $originalAmount,
                'discount_percentage' => $discount,
                'discount_amount' => $discountAmount,
                'subtotal' => $subtotal,
                'vat_amount' => $vatAmount,
                'amount_due' => $totalAmount,
                'details' => $fullDetails,
            ]);

            // Send email if requested
            if ($sendEmail && $subscription) {
                $this->sendInvoiceEmail($invoice, $subscription, $tenant);
            } elseif ($sendEmail && !$subscription) {
                $this->warn('Cannot send email: No active subscription found for this tenant.');
            }

            $this->newLine();
            $this->info('Invoice Summary:');
            $this->line("  Invoice Number: {$invoice->invoice_number}");
            if ($discount > 0) {
                $this->line("  Original Amount: PHP " . number_format($originalAmount, 2));
                $this->line("  Discount ({$discount}%): - PHP " . number_format($discountAmount, 2));
            }
            $this->line("  Subtotal: PHP " . number_format($subtotal, 2));
            $this->line("  VAT ({$vatPercentage}%): PHP " . number_format($vatAmount, 2));
            $this->line("  Amount Due: PHP " . number_format($invoice->amount_due, 2));
            $this->line("  Status: {$invoice->status}");
            $this->line("  Due Date: {$invoice->due_date->format('Y-m-d')}");
            if ($discount > 0) {
                $this->line("  Note: {$fullDetails}");
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to create invoice: " . $e->getMessage());
            Log::error('Failed to create manual invoice', [
                'tenant_id' => $tenantId,
                'amount' => $amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Generate invoice number for manual invoices
     */
    private function generateInvoiceNumber($type = 'manual')
    {
        $prefix = 'MAN'; // Manual invoice prefix
        $date = now()->format('Ymd');

        $lastInvoice = Invoice::where('invoice_number', 'like', "{$prefix}-{$date}%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -3);
            $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '001';
        }

        return "{$prefix}-{$date}-{$nextNumber}";
    }

    /**
     * Send invoice email notification
     */
    private function sendInvoiceEmail($invoice, $subscription, $tenant)
    {
        $recipient = $tenant->globalUsers?->first()?->email;

        if ($recipient) {
            try {
                Mail::to($recipient)->queue(new UpcomingRenewalInvoiceMail($invoice, $subscription));
                $this->info("✉ Invoice email queued to {$recipient}");

                Log::info('Manual invoice email queued', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'recipient_email' => $recipient,
                    'tenant_id' => $tenant->id,
                ]);
            } catch (\Exception $e) {
                $this->error("Failed to send email: " . $e->getMessage());
                Log::error('Failed to send manual invoice email', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            $this->warn("No email found for tenant {$tenant->id}");
        }
    }
}
