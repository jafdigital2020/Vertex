<?php

namespace App\Http\Controllers\Tenant\Recruitment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DataAccessController;
use App\Helpers\PermissionHelper;
use App\Models\JobOffer;
use App\Models\JobApplication;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class JobOfferController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            $user = Auth::guard('global')->user();
        } else {
            $user = Auth::user();
        }

        if ($user && method_exists($user, 'employmentDetail')) {
            $employmentDetail = $user->employmentDetail;
            if ($employmentDetail && isset($employmentDetail->branch_id)) {
                $user->branch_id = $employmentDetail->branch_id;
            } else {
                $user->branch_id = null;
            }
        } else {
            $user->branch_id = null;
        }

        return $user;
    }

    public function index(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(62);

        $offers = JobOffer::with([
            'jobApplication.candidate',
            'jobApplication.jobPosting',
            'department',
            'preparedBy',
            'approvedBy'
        ]);

        if ($request->has('status') && $request->status) {
            $offers->where('status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $offers->whereHas('jobApplication.candidate', function($query) use ($request) {
                $query->where('first_name', 'like', '%' . $request->search . '%')
                      ->orWhere('last_name', 'like', '%' . $request->search . '%');
            });
        }

        $offers = $offers->latest()->paginate(15);

        $statuses = ['draft', 'sent', 'accepted', 'rejected', 'expired', 'withdrawn'];

        return view('tenant.recruitment.offers.index', compact(
            'offers',
            'statuses',
            'permission',
            'authUser'
        ));
    }

    public function create(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(62);

        $jobApplicationId = $request->job_application_id;
        $jobApplication = null;

        if ($jobApplicationId) {
            $jobApplication = JobApplication::with(['candidate', 'jobPosting.department'])->find($jobApplicationId);
        }

        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $departments = $accessData['departments']->get();

        $employmentTypes = ['full-time', 'part-time', 'contract', 'probationary'];
        $salaryTypes = ['monthly', 'annual'];

        return view('tenant.recruitment.offers.create', compact(
            'jobApplication',
            'departments',
            'employmentTypes',
            'salaryTypes',
            'permission',
            'authUser'
        ));
    }

    public function store(Request $request)
    {
        $authUser = $this->authUser();

        $validator = Validator::make($request->all(), [
            'job_application_id' => 'required|exists:job_applications,id',
            'position_title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'salary_offered' => 'required|numeric|min:0',
            'salary_type' => 'required|in:monthly,annual',
            'benefits' => 'nullable|array',
            'start_date' => 'required|date|after_or_equal:today',
            'employment_type' => 'required|in:full-time,part-time,contract,probationary',
            'probation_period_months' => 'nullable|integer|min:1|max:12',
            'terms_conditions' => 'nullable|string',
            'offer_expiry_date' => 'required|date|after:start_date',
            'internal_notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $offerCode = 'OFF-' . strtoupper(Str::random(8));
        while (JobOffer::where('offer_code', $offerCode)->exists()) {
            $offerCode = 'OFF-' . strtoupper(Str::random(8));
        }

        $offer = JobOffer::create([
            'offer_code' => $offerCode,
            'job_application_id' => $request->job_application_id,
            'position_title' => $request->position_title,
            'department_id' => $request->department_id,
            'salary_offered' => $request->salary_offered,
            'salary_type' => $request->salary_type,
            'benefits' => $request->benefits,
            'start_date' => $request->start_date,
            'employment_type' => $request->employment_type,
            'probation_period_months' => $request->probation_period_months,
            'terms_conditions' => $request->terms_conditions,
            'offer_expiry_date' => $request->offer_expiry_date,
            'status' => 'draft',
            'prepared_by' => $authUser->id,
            'internal_notes' => $request->internal_notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job offer created successfully',
            'data' => $offer->load(['jobApplication.candidate', 'department'])
        ]);
    }

    public function show(JobOffer $offer)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(62);

        $offer->load([
            'jobApplication.candidate.education',
            'jobApplication.candidate.experience',
            'jobApplication.jobPosting',
            'department',
            'preparedBy',
            'approvedBy'
        ]);

        return view('tenant.recruitment.offers.show', compact(
            'offer',
            'permission',
            'authUser'
        ));
    }

    public function edit(JobOffer $offer)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(62);

        if (!in_array($offer->status, ['draft', 'sent'])) {
            return redirect()->back()->with('error', 'Cannot edit offer in current status');
        }

        $offer->load(['jobApplication.candidate', 'jobApplication.jobPosting']);

        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $departments = $accessData['departments']->get();

        $employmentTypes = ['full-time', 'part-time', 'contract', 'probationary'];
        $salaryTypes = ['monthly', 'annual'];

        return view('tenant.recruitment.offers.edit', compact(
            'offer',
            'departments',
            'employmentTypes',
            'salaryTypes',
            'permission',
            'authUser'
        ));
    }

    public function update(Request $request, JobOffer $offer)
    {
        if (!in_array($offer->status, ['draft'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit offer in current status'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'position_title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'salary_offered' => 'required|numeric|min:0',
            'salary_type' => 'required|in:monthly,annual',
            'benefits' => 'nullable|array',
            'start_date' => 'required|date|after_or_equal:today',
            'employment_type' => 'required|in:full-time,part-time,contract,probationary',
            'probation_period_months' => 'nullable|integer|min:1|max:12',
            'terms_conditions' => 'nullable|string',
            'offer_expiry_date' => 'required|date|after:start_date',
            'internal_notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $offer->update([
            'position_title' => $request->position_title,
            'department_id' => $request->department_id,
            'salary_offered' => $request->salary_offered,
            'salary_type' => $request->salary_type,
            'benefits' => $request->benefits,
            'start_date' => $request->start_date,
            'employment_type' => $request->employment_type,
            'probation_period_months' => $request->probation_period_months,
            'terms_conditions' => $request->terms_conditions,
            'offer_expiry_date' => $request->offer_expiry_date,
            'internal_notes' => $request->internal_notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job offer updated successfully',
            'data' => $offer
        ]);
    }

    public function send(JobOffer $offer)
    {
        if ($offer->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only send offers in draft status'
            ], 400);
        }

        $offer->markAsSent();
        $offer->jobApplication()->update(['status' => 'offer_made']);

        return response()->json([
            'success' => true,
            'message' => 'Job offer sent successfully'
        ]);
    }

    public function accept(Request $request, JobOffer $offer)
    {
        if ($offer->status !== 'sent') {
            return response()->json([
                'success' => false,
                'message' => 'Can only accept offers that have been sent'
            ], 400);
        }

        if ($offer->is_expired) {
            return response()->json([
                'success' => false,
                'message' => 'Offer has expired'
            ], 400);
        }

        $offer->markAsAccepted($request->notes);

        return response()->json([
            'success' => true,
            'message' => 'Job offer accepted successfully'
        ]);
    }

    public function reject(Request $request, JobOffer $offer)
    {
        if ($offer->status !== 'sent') {
            return response()->json([
                'success' => false,
                'message' => 'Can only reject offers that have been sent'
            ], 400);
        }

        $offer->markAsRejected($request->notes);

        return response()->json([
            'success' => true,
            'message' => 'Job offer rejected'
        ]);
    }

    public function withdraw(Request $request, JobOffer $offer)
    {
        if (!in_array($offer->status, ['draft', 'sent'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot withdraw offer in current status'
            ], 400);
        }

        $offer->withdraw($request->notes);

        return response()->json([
            'success' => true,
            'message' => 'Job offer withdrawn successfully'
        ]);
    }

    public function approve(JobOffer $offer)
    {
        $authUser = $this->authUser();

        if ($offer->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only approve draft offers'
            ], 400);
        }

        $offer->update(['approved_by' => $authUser->id]);

        return response()->json([
            'success' => true,
            'message' => 'Job offer approved successfully'
        ]);
    }

    public function generateOfferLetter(JobOffer $offer)
    {
        $offer->load(['jobApplication.candidate', 'department']);

        $pdf = \PDF::loadView('tenant.recruitment.offers.letter', compact('offer'));
        
        $filename = 'offer_letter_' . $offer->offer_code . '.pdf';
        $path = 'offers/' . $filename;
        
        Storage::disk('public')->put($path, $pdf->output());
        
        $offer->update(['offer_letter_path' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Offer letter generated successfully',
            'download_url' => asset('storage/' . $path)
        ]);
    }

    public function downloadOfferLetter(JobOffer $offer)
    {
        if (!$offer->offer_letter_path || !Storage::disk('public')->exists($offer->offer_letter_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Offer letter not found'
            ], 404);
        }

        return Storage::disk('public')->download($offer->offer_letter_path, 'offer_letter_' . $offer->offer_code . '.pdf');
    }

    public function expiredOffers()
    {
        $offers = JobOffer::expired()
            ->with(['jobApplication.candidate', 'jobApplication.jobPosting'])
            ->get();

        foreach ($offers as $offer) {
            $offer->update(['status' => 'expired']);
        }

        return response()->json([
            'success' => true,
            'message' => count($offers) . ' offers marked as expired',
            'data' => $offers
        ]);
    }

    public function statistics()
    {
        $stats = [
            'total_offers' => JobOffer::count(),
            'draft_offers' => JobOffer::byStatus('draft')->count(),
            'sent_offers' => JobOffer::byStatus('sent')->count(),
            'accepted_offers' => JobOffer::byStatus('accepted')->count(),
            'rejected_offers' => JobOffer::byStatus('rejected')->count(),
            'expired_offers' => JobOffer::byStatus('expired')->count(),
            'this_month' => JobOffer::whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])->count(),
            'acceptance_rate' => JobOffer::byStatus('accepted')->count() / max(JobOffer::whereIn('status', ['accepted', 'rejected'])->count(), 1) * 100
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}