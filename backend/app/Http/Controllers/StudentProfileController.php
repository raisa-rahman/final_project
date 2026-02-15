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

public function toggleFavorite(Request $request, $mentorId)
{
    $user = $request->user();
    $favorite = $user->favorites()->where('mentor_profile_id', $mentorId)->first();

    if ($favorite) {
        $favorite->delete();
        return response()->json(['status' => 'removed']);
    }

    $user->favorites()->create(['mentor_profile_id' => $mentorId]);
    return response()->json(['status' => 'added']);
}
public function getFavorites(Request $request)
{
    return response()->json($request->user()->favorites);
}
}

