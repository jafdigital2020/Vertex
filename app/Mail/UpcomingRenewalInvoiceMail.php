<?php

namespace App\Mail;

use App\Models\BranchSubscription;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UpcomingRenewalInvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Invoice $invoice;
    public BranchSubscription $subscription;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, BranchSubscription $subscription)
    {
        $this->invoice = $invoice;
        $this->subscription = $subscription;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Use branch name if available, otherwise fallback to tenant name
        $branchName = $this->subscription->branch->name ?? null;
        $tenantName = $this->subscription->tenant->name ?? null;
        $subjectPrefix = $branchName ? "Branch: {$branchName}" : ($tenantName ? "Tenant: {$tenantName}" : "Subscription");

        return new Envelope(
            subject: "{$subjectPrefix} – Upcoming Renewal Invoice {$this->invoice->invoice_number}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'tenant.email.invoice.upcoming-renewal-invoice',
            with: [
                'invoice' => $this->invoice,
                'subscription' => $this->subscription,
                'branch' => $this->subscription->branch ?? null,
                'tenant' => $this->subscription->tenant ?? null,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
