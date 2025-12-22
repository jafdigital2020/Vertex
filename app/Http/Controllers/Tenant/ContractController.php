<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\User;
use App\Helpers\PermissionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\DataAccessController;

class ContractController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::guard('web')->user();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;

        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $contracts = Contract::where('tenant_id', $tenantId)
            ->with(['user.personalInformation', 'template'])
            ->orderBy('created_at', 'desc')
            ->get();

        $employees = $accessData['employees']
            ->with(['personalInformation', 'employmentDetail'])
            ->get();

        $templates = ContractTemplate::where(function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId)
                ->orWhereNull('tenant_id');
        })
            ->where('is_active', true)
            ->get();

        return view('tenant.contracts.index', compact('contracts', 'employees', 'templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;

        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $employees = $accessData['employees']
            ->with(['personalInformation', 'employmentDetail'])
            ->get();

        $templates = ContractTemplate::where(function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId)
                ->orWhereNull('tenant_id');
        })
            ->where('is_active', true)
            ->get();

        return view('tenant.contracts.create', compact('employees', 'templates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $authUser = $this->authUser();

            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'template_id' => 'nullable|exists:contract_templates,id',
                'contract_type' => 'required|in:Probationary,Regular,Contractual,Project-Based',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'status' => 'nullable|in:Draft,Active,Expired,Terminated',
            ]);

            $employee = User::with(['personalInformation', 'employmentDetail', 'designation', 'department'])
                ->findOrFail($validated['user_id']);

            // Generate contract content from template
            $content = '';
            if (isset($validated['template_id']) && $validated['template_id']) {
                $template = ContractTemplate::findOrFail($validated['template_id']);
                $content = $template->generateContract($employee);
            }

            // Auto-calculate end date for probationary contracts (6 months)
            if ($validated['contract_type'] === 'Probationary' && !isset($validated['end_date'])) {
                $validated['end_date'] = \Carbon\Carbon::parse($validated['start_date'])->addMonths(6);
            }

            $validated['content'] = $content;
            $validated['tenant_id'] = $authUser->tenant_id;
            $validated['status'] = $validated['status'] ?? 'Draft';

            $contract = Contract::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Contract created successfully',
                'data' => $contract->load(['user.personalInformation', 'template'])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Contract Creation Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create contract: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Contract $contract)
    {
        $contract->load(['user.personalInformation', 'template', 'signedBy.personalInformation']);
        return view('tenant.contracts.show', compact('contract'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contract $contract)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;

        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $employees = $accessData['employees']
            ->with(['personalInformation', 'employmentDetail'])
            ->get();

        $templates = ContractTemplate::where(function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId)
                ->orWhereNull('tenant_id');
        })
            ->where('is_active', true)
            ->get();

        return view('tenant.contracts.edit', compact('contract', 'employees', 'templates'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contract $contract)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'template_id' => 'nullable|exists:contract_templates,id',
                'contract_type' => 'required|in:Probationary,Regular,Contractual,Project-Based',
                'content' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'status' => 'required|in:Draft,Active,Expired,Terminated',
            ]);

            // Auto-calculate end date for probationary contracts (6 months)
            if ($validated['contract_type'] === 'Probationary' && !isset($validated['end_date'])) {
                $validated['end_date'] = \Carbon\Carbon::parse($validated['start_date'])->addMonths(6);
            }

            $contract->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Contract updated successfully',
                'data' => $contract->load(['user.personalInformation', 'template'])
            ]);
        } catch (\Exception $e) {
            Log::error('Contract Update Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update contract: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contract $contract)
    {
        try {
            $contract->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Contract deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Contract Deletion Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete contract: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate contract from template
     */
    public function generate(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'template_id' => 'required|exists:contract_templates,id',
            ]);

            $employee = User::with(['personalInformation', 'employmentDetail', 'designation', 'department'])
                ->findOrFail($validated['user_id']);

            $template = ContractTemplate::findOrFail($validated['template_id']);
            $content = $template->generateContract($employee);

            return response()->json([
                'status' => 'success',
                'content' => $content,
                'contract_type' => $template->contract_type
            ]);
        } catch (\Exception $e) {
            Log::error('Contract Generation Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate contract: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sign a contract
     */
    public function sign(Request $request, Contract $contract)
    {
        try {
            $authUser = $this->authUser();

            $contract->update([
                'status' => 'Active',
                'signed_date' => now(),
                'signed_by' => $authUser->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Contract signed successfully',
                'data' => $contract->load(['signedBy.personalInformation'])
            ]);
        } catch (\Exception $e) {
            Log::error('Contract Signing Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to sign contract: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print/Download contract
     */
    public function print(Contract $contract)
    {
        $contract->load(['user.personalInformation', 'template', 'signedBy.personalInformation']);
        return view('tenant.contracts.print', compact('contract'));
    }

    /**
     * View HTML contract template
     */
    public function viewHtml(Contract $contract)
    {
        $contract->load(['user.personalInformation', 'template', 'signedBy.personalInformation']);

        $employee = $contract->user;
        $template = $contract->template;

        // Get contract data
        $contractData = $template ? $template->getContractData($employee, [
            'start_date' => \Carbon\Carbon::parse($contract->start_date)->format('F d, Y'),
            'end_date' => $contract->end_date ? \Carbon\Carbon::parse($contract->end_date)->format('F d, Y') : '',
        ]) : [];

        // Determine which template to use
        $templateView = 'tenant.contract-templates.html.probationary-employment';

        if ($template) {
            switch ($template->contract_type) {
                case 'Regular':
                    $templateView = 'tenant.contract-templates.html.regular-employment';
                    break;
                case 'Probationary':
                default:
                    $templateView = 'tenant.contract-templates.html.probationary-employment';
                    break;
            }
        }

        return view($templateView, ['contractData' => $contractData]);
    }

    /**
     * Preview HTML contract template
     */
    public function previewHtml(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'template_id' => 'required|exists:contract_templates,id',
                'start_date' => 'nullable|date',
            ]);

            $employee = User::with(['personalInformation', 'employmentDetail', 'designation', 'department'])
                ->findOrFail($validated['user_id']);

            $template = ContractTemplate::findOrFail($validated['template_id']);

            $startDate = $validated['start_date'] ?? now()->format('Y-m-d');

            // Get contract data
            $contractData = $template->getContractData($employee, [
                'start_date' => \Carbon\Carbon::parse($startDate)->format('F d, Y'),
            ]);

            // Determine which template to use
            $templateView = 'tenant.contract-templates.html.probationary-employment';

            switch ($template->contract_type) {
                case 'Regular':
                    $templateView = 'tenant.contract-templates.html.regular-employment';
                    break;
                case 'Probationary':
                default:
                    $templateView = 'tenant.contract-templates.html.probationary-employment';
                    break;
            }

            return view($templateView, compact('contractData'));
        } catch (\Exception $e) {
            Log::error('Contract Preview Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to preview contract: ' . $e->getMessage()
            ], 500);
        }
    }
}
