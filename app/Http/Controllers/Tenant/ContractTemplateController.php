<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ContractTemplate;
use App\Helpers\PermissionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ContractTemplateController extends Controller
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
    public function index()
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;

        $templates = ContractTemplate::where('tenant_id', $tenantId)
            ->orWhereNull('tenant_id')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tenant.contract-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tenant.contract-templates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $authUser = $this->authUser();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'contract_type' => 'required|in:Probationary,Regular,Contractual,Project-Based',
                'template_source' => 'required|in:pdf,text',
                'content' => 'nullable|string',
                'pdf_template_path' => 'nullable|string',
                'pdf_fillable_content' => 'nullable|string',
                'is_active' => 'nullable',
            ]);

            // Handle is_active checkbox
            $validated['is_active'] = $request->has('is_active') ? true : false;
            $validated['tenant_id'] = $authUser->tenant_id;

            // Remove template_source as it's not a database field
            unset($validated['template_source']);

            $template = ContractTemplate::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Contract template created successfully',
                'data' => $template
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Contract Template Creation Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create contract template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ContractTemplate $contractTemplate)
    {
        return view('tenant.contract-templates.show', compact('contractTemplate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContractTemplate $contractTemplate)
    {
        return view('tenant.contract-templates.edit', compact('contractTemplate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContractTemplate $contractTemplate)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'contract_type' => 'required|in:Probationary,Regular,Contractual,Project-Based',
                'content' => 'nullable|string',
                'pdf_template_path' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $contractTemplate->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Contract template updated successfully',
                'data' => $contractTemplate
            ]);
        } catch (\Exception $e) {
            Log::error('Contract Template Update Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update contract template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContractTemplate $contractTemplate)
    {
        try {
            $contractTemplate->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Contract template deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Contract Template Deletion Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete contract template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get template content for preview
     */
    public function preview(Request $request, ContractTemplate $contractTemplate)
    {
        $sampleData = [
            'employee_full_name' => 'Juan Dela Cruz',
            'date_hired' => now()->format('F d, Y'),
            'probationary_end_date' => now()->addMonths(6)->format('F d, Y'),
            'employee_id' => 'EMP-001',
            'position' => 'Software Developer',
            'department' => 'IT Department',
            'current_date' => now()->format('F d, Y'),
        ];

        $content = $contractTemplate->content;
        foreach ($sampleData as $placeholder => $value) {
            $content = str_replace('{{' . $placeholder . '}}', $value, $content);
        }

        return response()->json([
            'status' => 'success',
            'content' => $content
        ]);
    }

    /**
     * Toggle template active status
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $template = ContractTemplate::findOrFail($id);
            $template->is_active = $request->input('is_active', 0);
            $template->save();

            $statusText = $template->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'status' => 'success',
                'message' => "Contract template {$statusText} successfully"
            ]);
        } catch (\Exception $e) {
            Log::error('Contract Template Status Toggle Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update template status: ' . $e->getMessage()
            ], 500);
        }
    }
}
