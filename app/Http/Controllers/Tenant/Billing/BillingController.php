<?php

namespace App\Http\Controllers\Tenant\Billing;

use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class BillingController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    // Billing Index
    public function billingIndex(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id;

        // Fetch all invoices for this tenant with payments + subscription + branch
        $invoices = Invoice::with(['payments', 'subscription', 'branch'])
            ->whereHas('branch', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->orderByDesc('issued_at')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data'   => $invoices
            ]);
        }

        return view('tenant.billing.billing', [
            'invoices' => $invoices,
            'tenantId' => $tenantId
        ]);
    }


    public function fetchInvoices(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id;

        $perPage = $request->get('per_page', 10);

        $invoices = Invoice::with(['payments', 'subscription', 'branch'])
            ->whereHas('branch', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->orderByDesc('issued_at')
            ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data'   => $invoices
        ]);
    }


    // Add this new method for PDF download
    public function downloadInvoice($id)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id;

        try {
            // Find invoice with relationships and verify tenant access
            $invoice = Invoice::with(['payments', 'subscription', 'branch'])
                ->whereHas('branch', function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId);
                })
                ->findOrFail($id);

            // Bill to logic
            $branch = $invoice->branch;
            $billToEmail = null;
            $billToFullName = null;
            $billToAddress = null;

            if ($branch) {
                $firstEmploymentDetail = \App\Models\EmploymentDetail::where('branch_id', $branch->id)->orderBy('id')->first();
                if ($firstEmploymentDetail) {
                    $firstUser = $firstEmploymentDetail->user;
                    if ($firstUser) {
                        $billToEmail = $firstUser->email;
                        $personalInfo = $firstUser->personalInformation;
                        if ($personalInfo) {
                            $billToFullName = trim(
                                $personalInfo->first_name . ' ' .
                                ($personalInfo->middle_name ? $personalInfo->middle_name . ' ' : '') .
                                $personalInfo->last_name .
                                ($personalInfo->suffix ? ' ' . $personalInfo->suffix : '')
                            );
                        }
                    }
                }
                $billToAddress = $branch->location;
            }

            // Prepare data for PDF
            $data = [
                'invoice' => $invoice,
                'company' => [
                    'name' => 'Timora',
                    'address' => 'Unit D 49th Floor PBCom Tower, 6795 Ayala Avenue, corner V.A. Rufino St, Makati City, Metro Manila, Philippines',
                    'email' => 'support@timora.ph',
                    'logo' => public_path('build/img/timora-logo.png')
                ],
                'bill_to' => [
                    'name' => $billToFullName ?? 'N/A',
                    'email' => $billToEmail ?? 'N/A',
                    'address' => $billToAddress ?? 'N/A'
                ]
            ];

            // Generate PDF
            $pdf = Pdf::loadView('tenant.billing.invoice-pdf', $data);
            $pdf->setPaper('A4', 'portrait');

            // Download the PDF
            return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invoice not found or access denied',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    // Add this method for downloading all invoices
    public function downloadAllInvoices()
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id;

        try {
            // Get all invoices for this tenant
            $invoices = Invoice::with(['payments', 'subscription', 'branch'])
                ->whereHas('branch', function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId);
                })
                ->orderByDesc('issued_at')
                ->get();

            if ($invoices->isEmpty()) {
                return response()->json(['error' => 'No invoices found'], 404);
            }

            // Create a ZIP file with all invoices
            $zip = new \ZipArchive();
            $zipFileName = 'invoices-' . date('Y-m-d-H-i-s') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);

            // Create temp directory if it doesn't exist
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0777, true);
            }

            if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
                foreach ($invoices as $invoice) {
                    // Bill to logic
                    $branch = $invoice->branch;
                    $billToEmail = null;
                    $billToFullName = null;
                    $billToAddress = null;

                    if ($branch) {
                        $firstEmploymentDetail = \App\Models\EmploymentDetail::where('branch_id', $branch->id)->orderBy('id')->first();
                        if ($firstEmploymentDetail) {
                            $firstUser = $firstEmploymentDetail->user;
                            if ($firstUser) {
                                $billToEmail = $firstUser->email;
                                $personalInfo = $firstUser->personalInformation;
                                if ($personalInfo) {
                                    $billToFullName = trim(
                                        $personalInfo->first_name . ' ' .
                                        ($personalInfo->middle_name ? $personalInfo->middle_name . ' ' : '') .
                                        $personalInfo->last_name .
                                        ($personalInfo->suffix ? ' ' . $personalInfo->suffix : '')
                                    );
                                }
                            }
                        }
                        $billToAddress = $branch->location;
                    }

                    $data = [
                        'invoice' => $invoice,
                        'company' => [
                            'name' => 'Timora',
                            'address' => 'Unit D 49th Floor PBCom Tower, 6795 Ayala Avenue, corner V.A. Rufino St, Makati City, Metro Manila, Philippines',
                            'email' => 'support@timora.ph',
                            'logo' => public_path('build/img/timora-logo.png')
                        ],
                        'bill_to' => [
                            'name' => $billToFullName ?? 'N/A',
                            'email' => $billToEmail ?? 'N/A',
                            'address' => $billToAddress ?? 'N/A'
                        ]
                    ];

                    $pdf = Pdf::loadView('tenant.billing.invoice-pdf', $data);
                    $pdfContent = $pdf->output();
                    $zip->addFromString("invoice-{$invoice->invoice_number}.pdf", $pdfContent);
                }
                $zip->close();

                return response()->download($zipPath)->deleteFileAfterSend(true);
            }

            return response()->json(['error' => 'Could not create ZIP file'], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to download invoices',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
