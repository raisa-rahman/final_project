<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MentorProfile;
use Illuminate\Support\Facades\Storage;


class MentorProfileController extends Controller
{
    public function getProfile($id)
{
    $mentor = MentorProfile::with(['user', 'university', 'reviews'])
        ->withCount('reviews') 
        ->findOrFail($id);

    return response()->json($mentor);
}
    public function update(Request $request) {
    $request->validate([
        'subject_expertise' => 'required|string|max:255',
        'university_id'     => 'required|exists:universities,id', 
        'bio'               => 'nullable|string',
    ]);

    $profile = \App\Models\MentorProfile::updateOrCreate(
        ['user_id' => $request->user()->id],
        [
            'subject_expertise' => $request->subject_expertise,
            'university_id'     => $request->university_id,
            'bio'               => $request->bio,
        ]
    );

    $profile->load('university');

    return response()->json(['message' => 'Profile updated!', 'profile' => $profile]);
}

    public function upload(Request $request, MentorProfile $profile){
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

    public function show() {
    $user = auth()->user();
    $profile = MentorProfile::where('user_id', $user->id)
        ->with(['user', 'university', 'reviews']) 
        ->withCount('reviews') 
        ->firstOrFail();
        
    return response()->json($profile); 
}


public function updateStatus(Request $request, $id)
{
    $profile = MentorProfile::findOrFail($id);
    $newStatus = (int) $request->is_verified;

    if ($newStatus === 2) {
        if ($profile->document_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($profile->document_path);
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