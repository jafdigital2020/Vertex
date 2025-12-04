<?php

namespace App\Http\Controllers\Tenant\Recruitment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DataAccessController;
use App\Helpers\PermissionHelper;
use App\Models\Candidate;
use App\Models\CandidateEducation;
use App\Models\CandidateExperience;
use App\Models\JobPosting;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CandidateController extends Controller
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
        $permission = PermissionHelper::get(59);

        $candidates = Candidate::with(['education', 'experience', 'applications.jobPosting']);

        if ($request->has('status') && $request->status) {
            $candidates->where('status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $candidates->where(function($query) use ($request) {
                $query->where('first_name', 'like', '%' . $request->search . '%')
                      ->orWhere('last_name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%')
                      ->orWhere('candidate_code', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('skills') && $request->skills) {
            $candidates->whereJsonContains('skills', $request->skills);
        }

        $candidates = $candidates->active()->latest()->paginate(15);

        $statuses = ['new', 'screening', 'interview', 'evaluation', 'offer', 'hired', 'rejected'];
        $departments = Department::all();

        return view('tenant.recruitment.candidates.index', compact(
            'candidates',
            'statuses',
            'departments',
            'permission',
            'authUser'
        ));
    }

    public function create()
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(59);

        $jobPostings = JobPosting::open()->get();

        return view('tenant.recruitment.candidates.create', compact(
            'jobPostings',
            'permission',
            'authUser'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:candidates,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'nationality' => 'nullable|string|max:100',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'linkedin_profile' => 'nullable|url',
            'summary' => 'nullable|string',
            'skills' => 'nullable|array',
            'current_position' => 'nullable|string|max:255',
            'current_company' => 'nullable|string|max:255',
            'current_salary' => 'nullable|numeric|min:0',
            'expected_salary' => 'nullable|numeric|min:0',
            'availability' => 'required|in:immediate,2_weeks,1_month,negotiable',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:1024'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $candidateCode = 'CND-' . strtoupper(Str::random(8));
        while (Candidate::where('candidate_code', $candidateCode)->exists()) {
            $candidateCode = 'CND-' . strtoupper(Str::random(8));
        }

        $candidateData = [
            'candidate_code' => $candidateCode,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'nationality' => $request->nationality,
            'marital_status' => $request->marital_status,
            'linkedin_profile' => $request->linkedin_profile,
            'summary' => $request->summary,
            'skills' => $request->skills,
            'current_position' => $request->current_position,
            'current_company' => $request->current_company,
            'current_salary' => $request->current_salary,
            'expected_salary' => $request->expected_salary,
            'availability' => $request->availability,
            'source_type' => 'manual_entry'
        ];

        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('candidates/resumes', 'public');
            $candidateData['resume_path'] = $resumePath;
        }

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('candidates/photos', 'public');
            $candidateData['photo_path'] = $photoPath;
        }

        $candidate = Candidate::create($candidateData);

        if ($request->has('education') && is_array($request->education)) {
            foreach ($request->education as $edu) {
                CandidateEducation::create([
                    'candidate_id' => $candidate->id,
                    'institution' => $edu['institution'] ?? '',
                    'degree' => $edu['degree'] ?? '',
                    'field_of_study' => $edu['field_of_study'] ?? null,
                    'start_year' => $edu['start_year'] ?? null,
                    'end_year' => $edu['end_year'] ?? null,
                    'is_current' => $edu['is_current'] ?? false,
                    'grade' => $edu['grade'] ?? null,
                    'description' => $edu['description'] ?? null
                ]);
            }
        }

        if ($request->has('experience') && is_array($request->experience)) {
            foreach ($request->experience as $exp) {
                CandidateExperience::create([
                    'candidate_id' => $candidate->id,
                    'company' => $exp['company'] ?? '',
                    'position' => $exp['position'] ?? '',
                    'description' => $exp['description'] ?? null,
                    'start_date' => $exp['start_date'] ?? null,
                    'end_date' => $exp['end_date'] ?? null,
                    'is_current' => $exp['is_current'] ?? false,
                    'location' => $exp['location'] ?? null,
                    'achievements' => $exp['achievements'] ?? null
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Candidate created successfully',
            'data' => $candidate->load(['education', 'experience'])
        ]);
    }

    public function show(Candidate $candidate)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(59);

        $candidate->load([
            'education',
            'experience',
            'applications.jobPosting.department',
            'applications.interviews',
            'applications.offers'
        ]);

        return view('tenant.recruitment.candidates.show', compact(
            'candidate',
            'permission',
            'authUser'
        ));
    }

    public function edit(Candidate $candidate)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(59);

        $candidate->load(['education', 'experience']);

        return view('tenant.recruitment.candidates.edit', compact(
            'candidate',
            'permission',
            'authUser'
        ));
    }

    public function update(Request $request, Candidate $candidate)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:candidates,email,' . $candidate->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'nationality' => 'nullable|string|max:100',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'linkedin_profile' => 'nullable|url',
            'summary' => 'nullable|string',
            'skills' => 'nullable|array',
            'current_position' => 'nullable|string|max:255',
            'current_company' => 'nullable|string|max:255',
            'current_salary' => 'nullable|numeric|min:0',
            'expected_salary' => 'nullable|numeric|min:0',
            'availability' => 'required|in:immediate,2_weeks,1_month,negotiable',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:1024',
            'status' => 'nullable|in:new,screening,interview,evaluation,offer,hired,rejected'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $candidateData = [
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'nationality' => $request->nationality,
            'marital_status' => $request->marital_status,
            'linkedin_profile' => $request->linkedin_profile,
            'summary' => $request->summary,
            'skills' => $request->skills,
            'current_position' => $request->current_position,
            'current_company' => $request->current_company,
            'current_salary' => $request->current_salary,
            'expected_salary' => $request->expected_salary,
            'availability' => $request->availability
        ];

        if ($request->status) {
            $candidateData['status'] = $request->status;
        }

        if ($request->hasFile('resume')) {
            if ($candidate->resume_path) {
                Storage::disk('public')->delete($candidate->resume_path);
            }
            $candidateData['resume_path'] = $request->file('resume')->store('candidates/resumes', 'public');
        }

        if ($request->hasFile('photo')) {
            if ($candidate->photo_path) {
                Storage::disk('public')->delete($candidate->photo_path);
            }
            $candidateData['photo_path'] = $request->file('photo')->store('candidates/photos', 'public');
        }

        $candidate->update($candidateData);

        return response()->json([
            'success' => true,
            'message' => 'Candidate updated successfully',
            'data' => $candidate->load(['education', 'experience'])
        ]);
    }

    public function destroy(Candidate $candidate)
    {
        if ($candidate->applications()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete candidate with applications'
            ], 400);
        }

        if ($candidate->resume_path) {
            Storage::disk('public')->delete($candidate->resume_path);
        }
        if ($candidate->photo_path) {
            Storage::disk('public')->delete($candidate->photo_path);
        }

        $candidate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Candidate deleted successfully'
        ]);
    }

    public function export(Request $request)
    {
        $candidates = Candidate::with(['education', 'experience']);

        if ($request->has('status') && $request->status) {
            $candidates->where('status', $request->status);
        }

        $candidates = $candidates->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Candidate Code', 'Full Name', 'Email', 'Phone', 'Status',
            'Current Position', 'Current Company', 'Expected Salary',
            'Years of Experience', 'Education', 'Skills', 'Applied Date'
        ];

        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($candidates as $candidate) {
            $data = [
                $candidate->candidate_code,
                $candidate->full_name,
                $candidate->email,
                $candidate->phone,
                ucfirst($candidate->status),
                $candidate->current_position,
                $candidate->current_company,
                $candidate->expected_salary,
                $candidate->years_of_experience,
                $candidate->education->pluck('degree')->join(', '),
                is_array($candidate->skills) ? implode(', ', $candidate->skills) : '',
                $candidate->created_at->format('Y-m-d')
            ];

            $sheet->fromArray($data, null, 'A' . $row);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'candidates_export_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
        $tempPath = storage_path('app/temp/' . $fileName);

        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    public function updateStatus(Request $request, Candidate $candidate)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:new,screening,interview,evaluation,offer,hired,rejected',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $candidate->update([
            'status' => $request->status,
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Candidate status updated successfully'
        ]);
    }
}