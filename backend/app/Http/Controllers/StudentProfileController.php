<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentProfile;

/**
 * StudentProfileController manages the personal settings for student users,
 * including their academic goals and a list of preferred (favourited) mentors.
 */

class StudentProfileController extends Controller
{
    /**
     * Retrieve the authenticated student's profile information.
     * * Fetches the record from the database based on the current user's unique ID.
     */
    public function show(Request $request)
        {
        $profile = StudentProfile::where('user_id', $request->user()->id)->first();    
        return response()->json($profile);
        }

    /**
     * Update or create the authenticated user's student profile.
     * * Validates the input data to ensure professional standards are met
     * and returns the updated profile with university details loaded.
     */
    public function update(Request $request)
    {
        $request->validate([
            'target_university' => 'nullable|string|max:255',
            'target_country' => 'nullable|string|max:255',
        ]);

        // Updates the profile if it exists, otherwise creates a new entry for the user
        $profile = \App\Models\StudentProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->only(['target_university', 'target_country'])
        );

        return response()->json(['message' => 'Profile updated successfully!', 'profile' => $profile]);
    }

    public function toggleFavorite($id)
    {
        $user = auth()->user();
        $mentor = \App\Models\MentorProfile::findOrFail($id);
        $status = $user->favorites()->toggle($mentor->id);

        return response()->json([
            'status' => count($status['attached']) > 0 ? 'added' : 'removed',
            'message' => 'Favorite updated successfully'
        ]);
    }

    public function getFavorites()
    {
        $user = auth()->user();
        return response()->json($user->favorites()->get(['mentor_profile_id']));
    }
}

