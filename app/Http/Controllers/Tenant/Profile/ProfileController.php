<?php

namespace App\Http\Controllers\Tenant\Profile;

use Exception;
use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\EmployeeFamilyInformation;

class ProfileController extends Controller
{
    // Profile Index
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }

        return Auth::user();
    }

    public function profileIndex(Request $request)
    {
        // Auth User
        $authUser =  $this->authUser();

        // User Relationships
        if ($authUser instanceof \App\Models\User) {
            $authUser->load([
                'personalInformation',
                'employmentDetail.department',
                'employmentDetail.designation',
                'employmentDetail.branch',
                'governmentDetail',
                'employeeBank',
                'family',
                'education',
                'experience',
                'emergency',
                'salaryDetail',
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Profile index',
                'data' => [
                    'users' => $authUser,
                ]
            ]);
        }

        return view('tenant.profile.profile', [
            'users' => $authUser,
        ]);
    }

    // Update Profile Picture
    public function updateProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048', // Max file size 2MB
        ]);

        $authUser =  $this->authUser();

        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');

            $imagePath = $image->store('profile_pictures', 'public');

            if (
                $authUser->personalInformation && $authUser->personalInformation->profile_picture &&
                $authUser->personalInformation->profile_picture !== 'default.png'
            ) {
                Storage::disk('public')->delete($authUser->personalInformation->profile_picture);
            }

            if ($authUser->personalInformation) {
                $authUser->personalInformation->profile_picture = $imagePath;
                $authUser->personalInformation->save();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Profile picture updated successfully!'
        ]);
    }

    // Change Password
    public function changePassword(Request $request)
    {
        $messages = [
            'new_password.required' => 'The new password field is required.',
            'new_password.string' => 'The new password must be a valid string.',
            'new_password.min' => 'The new password must be at least 6 characters.',
            'new_password.confirmed' => 'The new password confirmation does not match.',
            'new_password.regex' => 'The new password must contain at least one uppercase letter and one number.',
        ];

        // Validate the incoming request
        $validated = $request->validate([
            'new_password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*\d).+$/'
            ],
        ], $messages);

        if (!$request->has('new_password_confirmation')) {
            return response()->json([
                'status' => 'error',
                'message' => 'The new password confirmation field is required.',
            ], 422);
        }

        $user = Auth::user();

        if (Auth::guard('global')->check()) {
            $user = Auth::guard('global')->user();
        }

        if ($user instanceof \App\Models\GlobalUser || $user instanceof \App\Models\User) {
            $user->password = Hash::make($validated['new_password']);
            $user->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Password changed successfully!',
            ]);
        }

        // If no user is found
        return response()->json([
            'status' => 'error',
            'message' => 'User not found or invalid user instance.',
        ], 400);
    }


    // Basic Information Update
    public function updateUserBasicInfo(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'phone_number' => 'nullable|string',
            'gender' => 'nullable|string|in:Male,Female',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string',
            'complete_address' => 'nullable|string',
        ]);

        try {
            $user = User::find($validated['user_id']);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found.']);
            }

            $personalInfo = $user->personalInformation;
            if (!$personalInfo) {
                return response()->json(['success' => false, 'message' => 'Personal information not found.']);
            }

            $personalInfo->update([
                'phone_number' => $validated['phone_number'],
                'gender' => $validated['gender'],
                'birth_date' => $validated['birth_date'],
                'birth_place' => $validated['birth_place'],
                'complete_address' => $validated['complete_address'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Basic information updated successfully!',
                'data' => $personalInfo
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    // Personal Information Update
    public function updateUserPersonalInfo(Request $request)
    {
        Log::info('updateUserPersonalInfo called', ['request' => $request->all()]);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'nationality' => 'nullable|string',
            'religion' => 'nullable|string',
            'civil_status' => 'nullable|string',
            'spouse_name' => 'nullable|string',
            'no_of_children' => 'nullable|integer|min:0',
        ]);

        try {
            $user = User::find($validated['user_id']);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found.']);
            }

            $personalInfo = $user->personalInformation;
            if (!$personalInfo) {
                return response()->json(['success' => false, 'message' => 'Personal information not found.']);
            }

            $personalInfo->update([
                'nationality' => $validated['nationality'],
                'religion' => $validated['religion'],
                'civil_status' => $validated['civil_status'],
                'spouse_name' => $validated['spouse_name'],
                'no_of_children' => $validated['no_of_children'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Personal information updated successfully!',
                'data' => $personalInfo
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    // Emergency Contact Update
    public function updateUserEmergencyContact(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'primary_name' => 'required|string',
            'primary_phone_one' => 'required|string',
            'primary_phone_two' => 'nullable|string',
            'primary_relationship' => 'required|string',
            'secondary_name' => 'nullable|string',
            'secondary_phone_one' => 'nullable|string',
            'secondary_phone_two' => 'nullable|string',
            'secondary_relationship' => 'nullable|string',
        ]);

        try {
            $user = User::find($validated['user_id']);
            if (!$user) {
                Log::warning('User not found in updateUserEmergencyContact', ['user_id' => $validated['user_id']]);
                return response()->json(['success' => false, 'message' => 'User not found.']);
            }

            // Create or update emergency contact
            $emergencyContact = $user->emergency()->updateOrCreate(
                ['user_id' => $validated['user_id']],
                [
                    'primary_name' => $validated['primary_name'],
                    'primary_phone_one' => $validated['primary_phone_one'],
                    'primary_phone_two' => $validated['primary_phone_two'],
                    'primary_relationship' => $validated['primary_relationship'],
                    'secondary_name' => $validated['secondary_name'],
                    'secondary_phone_one' => $validated['secondary_phone_one'],
                    'secondary_phone_two' => $validated['secondary_phone_two'],
                    'secondary_relationship' => $validated['secondary_relationship'],
                ]
            );

            Log::info('Emergency contact updated successfully', ['user_id' => $validated['user_id']]);

            return response()->json([
                'status' => 'success',
                'message' => 'Emergency contact updated successfully!',
                'data' => $emergencyContact
            ]);
        } catch (Exception $e) {
            Log::error('Error updating emergency contact', [
                'user_id' => $validated['user_id'] ?? null,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    // Family Information Add
    public function addFamilyInformation(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|array',
            'relationship' => 'required|array',
            'birthdate' => 'required|array',
            'phone_number' => 'array',
        ]);

        $userId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        $authUser = $request->input('user_id');
        $names = $request->input('name');
        $relationships = $request->input('relationship');
        $birthdates = $request->input('birthdate');
        $phones = $request->input('phone_number');

        foreach ($names as $index => $name) {
            $family = EmployeeFamilyInformation::updateOrCreate(
                [
                    'user_id' => $authUser,
                    'name' => $name,
                    'relationship' => $relationships[$index],
                    'birthdate' => $birthdates[$index],
                ],
                [
                    'phone_number' => $phones[$index] ?? null
                ]
            );

            // Log create for each family member
            UserLog::create([
                'user_id' => $userId,
                'global_user_id' => $globalUserId,
                'module' => 'Employee Details (Family)',
                'action' => 'Create',
                'description' => 'Created Family Information: "' . $family->name . '", Relationship: "' . $family->relationship . '", Phone: "' . $family->phone_number . '"',
                'affected_id' => $family->id,
                'old_data' => json_encode($family->getOriginal()),
                'new_data' => json_encode($family->getChanges()),
            ]);
        }

        return response()->json([
            'message' => 'Family information processed successfully',
            'data' => $family
        ], 200);
    }

    // Family Information Update
    public function updateFamilyInformation(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'birthdate' => 'required|date',
        ]);
        $authUser =  $this->authUser();
        $authUserId = $authUser->id;

        $family = EmployeeFamilyInformation::where('user_id', $authUserId)
            ->where('id', $id)
            ->first();

        $oldData = $family->toArray();

        $family->update($validated);

        $empId = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $empId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        UserLog::create([
            'user_id' => $empId,
            'global_user_id' => $globalUserId,
            'module' => 'Employee Details (Family)',
            'action' => 'Update',
            'description' => 'Updated Family Info: Name "' . $family->name . '", Relationship "' . $family->relationship . '"',
            'affected_id' => $family->id,
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($family->getChanges()),
        ]);

        return response()->json([
            'message' => 'Family information updated successfully.',
            'data' => $family,
        ]);
    }
}
