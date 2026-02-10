<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MentorProfile;

class MentorProfileController extends Controller
{
    public function update(Request $request)
{
    $request->validate([
        'subject_expertise' => 'required|string|max:255',
        'bio' => 'nullable|string|max:1000',
    ]);

    $profile = \App\Models\MentorProfile::updateOrCreate(
        ['user_id' => $request->user()->id],
        $request->only(['subject_expertise', 'bio'])
    );

    return response()->json(['message' => 'Profile updated!', 'profile' => $profile]);
}
public function upload(Request $request, MentorProfile $profile)
{
    $request->validate([
        'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        'type' => 'required|string'
    ]);

    if ($request->hasFile('file')) {
        $path = $request->file('file')->store('mentors', 'public');

        if ($request->type === 'photo') {
            $profile->update(['profile_photo_path' => $path]);
        } else {
            $profile->update(['document_path' => $path]);
        }

        return response()->json(['message' => 'File uploaded successfully', 'path' => $path]);
    }

    return response()->json(['message' => 'Upload failed'], 400);
}
public function show(Request $request)
{
    $profile = \App\Models\MentorProfile::where('user_id', $request->user()->id)->first();
    
    if (!$profile) {
        $profile = \App\Models\MentorProfile::create([
            'user_id' => $request->user()->id,
            'subject_expertise' => 'Setup Pending',
            'is_verified' => 0
        ]);
    }
    
    return response()->json($profile);
}
}