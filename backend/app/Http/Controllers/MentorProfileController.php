<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MentorProfile;
use Illuminate\Support\Facades\Storage;

/**
 * MentorProfileController manages the creation, retrieval, and verification
 * of mentor-specific data and associated file uploads.
 */

class MentorProfileController extends Controller
{

    /**
     * Retrieve a specific mentor's profile by ID.
     * * Fetches associated user details, university information, and reviews,
     * including a total count of all reviews received.
     */
    public function getProfile($id)
    {
        $mentor = MentorProfile::with(['user', 'university', 'reviews'])
            ->withCount('reviews') 
            ->findOrFail($id);

        return response()->json($mentor);
    }


    /**
     * Update or create the authenticated user's mentor profile.
     * * Validates the input data to ensure professional standards are met
     * and returns the updated profile with university details loaded.
     */
    public function update(Request $request) {
        $request->validate([
            'subject_expertise' => 'required|string|max:255',
            'university_id'     => 'required|exists:universities,id', 
            'current_role'      => 'nullable|string|max:255',
            'company'           => 'nullable|string|max:255',
            'graduation_year'   => 'nullable|integer|min:1900|max:'.(date('Y') + 10),
            'bio'               => 'nullable|string',
            'interests'         => 'nullable|string',
            'roadmap'           => 'nullable|string',
            'ask_me_about'      => 'nullable|string',
        ]);

        // Updates existing profile or creates a new entry if one doesn't exist for the user
        $profile = MentorProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'subject_expertise' => $request->subject_expertise,
                'university_id'     => $request->university_id,
                'current_role'      => $request->current_role,
                'company'           => $request->company,
                'graduation_year'   => $request->graduation_year,
                'bio'               => $request->bio,
                'interests'         => $request->interests,
                'roadmap'           => $request->roadmap,
                'ask_me_about'      => $request->ask_me_about,
            ]
        );

        $profile->load('university');

        return response()->json(['message' => 'Profile updated!', 'profile' => $profile]);
    }

    /**
     * Handle file uploads for profile photos or verification documents.
     * * Files are stored within the 'public' disk under the 'mentors' directory.
     */
    public function upload(Request $request, MentorProfile $profile){
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'type' => 'required|string'
        ]);

        if ($request->hasFile('file')) {
            // Store the file and obtain the path
            $path = $request->file('file')->store('mentors', 'public');

            // Categorise the file based on the provided 'type'
            if ($request->type === 'photo') {
                $profile->update(['profile_photo_path' => $path]);
            } else {
                $profile->update(['document_path' => $path]);
            }
            return response()->json(['message' => 'File uploaded successfully', 'path' => $path]);
        }
        return response()->json(['message' => 'Upload failed'], 400);
    }

    /**
     * Display the profile of the currently authenticated mentor.
     */
    public function show() {
        $user = auth()->user();
        $profile = MentorProfile::where('user_id', $user->id)
            ->with(['user', 'university', 'reviews']) 
            ->withCount('reviews') 
            ->firstOrFail();
            
        return response()->json($profile); 
    }

    /**
     * Update the verification status of a mentor.
     * * If the status is set to 'rejected' (2), the associated verification 
     * document is deleted from storage to maintain data privacy.
     */
    public function updateStatus(Request $request, $id)
    {
        $profile = MentorProfile::findOrFail($id);
        $newStatus = (int) $request->is_verified;

        // If status is 2 (e.g., Rejected/Reset), remove the verification document

        if ($newStatus === 2) {
            if ($profile->document_path) {
                Storage::disk('public')->delete($profile->document_path);
            }
            $profile->document_path = null;
        }

        $profile->is_verified = $newStatus;
        $profile->save(); 

        return response()->json([
            'is_verified' => $profile->is_verified,
            'document_path' => $profile->document_path
        ]);
    }
}