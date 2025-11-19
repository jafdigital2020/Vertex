<?php

namespace App\Http\Controllers\Tenant\Payroll;

use App\Models\OtTemplate;
use Illuminate\Http\Request;
use App\Models\OtTemplateRate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CustomOtRateController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    public function otRateIndex(Request $request)
    {
        $user = $this->authUser();
        $tenant = $user->tenant;

        $otTemplates = OtTemplate::with('otTemplateRates')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'ot_templates' => $otTemplates,
            ]);
        }

        return view('tenant.payroll.payroll-items.custom-ot', [
            'tenant' => $tenant,
            'otTemplates' => $otTemplates,
        ]);
    }

    public function getTemplateRates($id)
    {
        try {
            $template = OtTemplate::with('otTemplateRates')->findOrFail($id);

            return response()->json([
                'success' => true,
                'template' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'rates' => 'required|array|min:1',
            'rates.*.type' => 'required|string',
            'rates.*.normal' => 'required|numeric|min:0',
            'rates.*.overtime' => 'required|numeric|min:0',
            'rates.*.night_differential' => 'required|numeric|min:0',
            'rates.*.night_differential_overtime' => 'required|numeric|min:0',
        ]);

        $user = $this->authUser();

        try {
            // Create OT Template
            $otTemplate = OtTemplate::create([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status,
                'created_by_type' => get_class($user),
                'created_by_id' => $user->id,
                'updated_by_type' => get_class($user),
                'updated_by_id' => $user->id,
            ]);

            // Create OT Template Rates
            foreach ($request->rates as $rate) {
                OtTemplateRate::create([
                    'ot_template_id' => $otTemplate->id,
                    'type' => $rate['type'],
                    'normal' => $rate['normal'],
                    'overtime' => $rate['overtime'],
                    'night_differential' => $rate['night_differential'],
                    'night_differential_overtime' => $rate['night_differential_overtime'],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'OT Template created successfully',
                'data' => $otTemplate->load('otTemplateRates')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create OT Template: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'rate_id' => 'required|exists:ot_template_rates,id',
            'normal' => 'required|numeric|min:0',
            'overtime' => 'required|numeric|min:0',
            'night_differential' => 'required|numeric|min:0',
            'night_differential_overtime' => 'required|numeric|min:0',
        ]);

        try {
            $rate = OtTemplateRate::findOrFail($request->rate_id);

            $rate->update([
                'normal' => $request->normal,
                'overtime' => $request->overtime,
                'night_differential' => $request->night_differential,
                'night_differential_overtime' => $request->night_differential_overtime,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OT Rate updated successfully',
                'data' => $rate
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update OT Rate: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'rate_id' => 'required|exists:ot_template_rates,id',
        ]);

        try {
            $rate = OtTemplateRate::findOrFail($request->rate_id);
            $rate->delete();

            return response()->json([
                'success' => true,
                'message' => 'OT Rate deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete OT Rate: ' . $e->getMessage()
            ], 500);
        }
    }
}
