<?php

namespace App\Http\Controllers\Tenant\Billing;

use Exception;
use App\Models\Tenant;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    /**
     * Receive invoice/order data from external server
     *
     * This endpoint accepts invoice/order data from an external payment/billing system
     * and creates or updates invoices in the local database.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function receiveExternalInvoice(Request $request)
    {
        try {
            // Log the incoming request for debugging
            Log::info('External invoice received', [
                'payload' => $request->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'tenant_id' => 'required|integer|exists:tenants,id',
                'subscription_id' => 'nullable|integer',
                'upgrade_plan_id' => 'nullable|integer',
                'invoice_type' => 'required|string|in:subscription,license_overage,combo,plan_upgrade',
                'license_overage_count' => 'nullable|integer|min:0',
                'license_overage_rate' => 'nullable|numeric|min:0',
                'license_overage_amount' => 'nullable|numeric|min:0',
                'subscription_amount' => 'nullable|numeric|min:0',
                'invoice_number' => 'required|string|unique:invoices,invoice_number',
                'amount_due' => 'required|numeric|min:0',
                'amount_paid' => 'nullable|numeric|min:0',
                'currency' => 'nullable|string|max:3',
                'due_date' => 'required|date',
                'status' => 'required|string|in:pending,paid,overdue,canceled',
                'issued_at' => 'nullable|date',
                'paid_at' => 'nullable|date',
                'period_start' => 'nullable|date',
                'period_end' => 'nullable|date',
                'consolidated_into_invoice_id' => 'nullable|integer',
                'unused_overage_count' => 'nullable|integer|min:0',
                'unused_overage_amount' => 'nullable|numeric|min:0',
                'gross_overage_count' => 'nullable|integer|min:0',
                'gross_overage_amount' => 'nullable|numeric|min:0',
                'implementation_fee' => 'nullable|numeric|min:0',
                'vat_amount' => 'nullable|numeric|min:0',
                'external_order_id' => 'nullable|string',
                'external_reference' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                Log::warning('External invoice validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'payload' => $request->all(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();

            // Check if tenant exists and is active
            $tenant = Tenant::find($validated['tenant_id']);
            if (!$tenant) {
                Log::error('Tenant not found for external invoice', [
                    'tenant_id' => $validated['tenant_id'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Tenant not found',
                ], 404);
            }

            // Begin database transaction
            DB::beginTransaction();

            try {
                // Prepare invoice data
                $invoiceData = [
                    'tenant_id' => $validated['tenant_id'],
                    'subscription_id' => $validated['subscription_id'] ?? null,
                    'upgrade_plan_id' => $validated['upgrade_plan_id'] ?? null,
                    'invoice_type' => $validated['invoice_type'],
                    'license_overage_count' => $validated['license_overage_count'] ?? null,
                    'license_overage_rate' => $validated['license_overage_rate'] ?? null,
                    'license_overage_amount' => $validated['license_overage_amount'] ?? null,
                    'subscription_amount' => $validated['subscription_amount'] ?? null,
                    'invoice_number' => $validated['invoice_number'],
                    'amount_due' => $validated['amount_due'],
                    'amount_paid' => $validated['amount_paid'] ?? 0.00,
                    'currency' => $validated['currency'] ?? 'PHP',
                    'due_date' => $validated['due_date'],
                    'status' => $validated['status'],
                    'issued_at' => $validated['issued_at'] ?? now(),
                    'paid_at' => $validated['paid_at'] ?? null,
                    'period_start' => $validated['period_start'] ?? null,
                    'period_end' => $validated['period_end'] ?? null,
                    'consolidated_into_invoice_id' => $validated['consolidated_into_invoice_id'] ?? null,
                    'unused_overage_count' => $validated['unused_overage_count'] ?? null,
                    'unused_overage_amount' => $validated['unused_overage_amount'] ?? null,
                    'gross_overage_count' => $validated['gross_overage_count'] ?? null,
                    'gross_overage_amount' => $validated['gross_overage_amount'] ?? null,
                    'implementation_fee' => $validated['implementation_fee'] ?? null,
                    'vat_amount' => $validated['vat_amount'] ?? null,
                ];

                // Create the invoice
                $invoice = Invoice::create($invoiceData);

                // Log additional metadata if provided (not part of the model)
                if (isset($validated['external_order_id']) || isset($validated['external_reference']) || isset($validated['notes'])) {
                    Log::info('External invoice metadata', [
                        'invoice_id' => $invoice->id,
                        'external_order_id' => $validated['external_order_id'] ?? null,
                        'external_reference' => $validated['external_reference'] ?? null,
                        'notes' => $validated['notes'] ?? null,
                    ]);
                }

                DB::commit();

                Log::info('External invoice created successfully', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'tenant_id' => $invoice->tenant_id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Invoice created successfully',
                    'data' => [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'tenant_id' => $invoice->tenant_id,
                        'amount_due' => $invoice->amount_due,
                        'status' => $invoice->status,
                        'created_at' => $invoice->created_at,
                    ],
                ], 201);
            } catch (Exception $e) {
                DB::rollBack();

                Log::error('Failed to create invoice from external source', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'payload' => $validated,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create invoice: ' . $e->getMessage(),
                ], 500);
            }
        } catch (Exception $e) {
            Log::error('External invoice endpoint error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
            ], 500);
        }
    }

    public function receiveOrderInvoice(Request $request)
    {
        try {
            $order = $request->input('order', []);
            $orderItems = $request->input('order_items', []);

            $validator = Validator::make($order, [
                'order_number'   => 'required|string|unique:invoices,invoice_number',
                'payment_status' => 'required|string|in:paid,pending,failed,canceled',
                'paid_amount'    => 'required|numeric|min:0',
                'paid_at'        => 'nullable|date',
                'subtotal'       => 'required|numeric|min:0',
                'tax'            => 'nullable|numeric|min:0',
                'total_amount'   => 'required|numeric|min:0',
                'client_name'    => 'nullable|string|max:255',
                'client_email'   => 'nullable|email',
                'domain_name'    => 'nullable|string|max:255',
                'service_type'   => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            DB::beginTransaction();

            $invoiceData = [
                'tenant_id' => $request->input('tenant_id') ?? null,
                'invoice_type' => 'custom_order', // Fixed invoice type
                'invoice_number' => $data['order_number'],
                'amount_due' => $data['total_amount'],
                'amount_paid' => $data['paid_amount'],
                'currency' => 'PHP',
                'due_date' => now()->addDays(7),
                'status' => $data['payment_status'] === 'paid' ? 'paid' : 'pending',
                'issued_at' => $data['paid_at'] ?? now(),
                'paid_at' => $data['paid_at'] ?? null,
                'vat_amount' => $data['tax'] ?? 0,
                'subscription_amount' => $data['subtotal'] ?? 0,
            ];

            $invoice = Invoice::create($invoiceData);

            // Create invoice items from order_items only for custom_order
            if ($invoiceData['invoice_type'] === 'custom_order') {
                foreach ($orderItems as $item) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'rate' => $item['rate'],
                        'amount' => $item['amount'],
                        'period' => null,
                        'metadata' => [
                            'original_order_item_id' => $item['id'] ?? null,
                            'source' => 'order_webhook'
                        ],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice created from order successfully',
                'data' => $invoice->load('items'),
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create invoice from order', [
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
            ], 500);
        }
    }

    public function getInvoiceItems($invoiceId)
    {
        $invoice = Invoice::with('items')->findOrFail($invoiceId);

        if ($invoice->invoice_type === 'custom_order' || $invoice->invoice_type === 'implementation_fee') {
            return response()->json($invoice->items);
        }

        return response()->json([]);
    }
}
