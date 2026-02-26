<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentProfile;

class StudentProfileController extends Controller
{
public function show(Request $request)
{
$profile = StudentProfile::where('user_id', $request->user()->id)->first();    
return response()->json($profile);
}

public function update(Request $request)
{
    $request->validate([
        'target_university' => 'nullable|string|max:255',
        'target_country' => 'nullable|string|max:255',
    ]);

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

