<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Meeting;
use App\Models\MentorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
public function store(Request $request)
{
    $request->validate([
        'meeting_id' => 'required|exists:meetings,id',
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string'
    ]);

    $meeting = \App\Models\Meeting::findOrFail($request->meeting_id);
    $mentorId = $meeting->mentor_profile_id; 

    \App\Models\Review::create([
        'student_id' => \Illuminate\Support\Facades\Auth::id(),
        'meeting_id' => $meeting->id,
        'mentor_profile_id' => $mentorId,
        'rating' => $request->rating,
        'comment' => $request->comment,
    ]);

    $mentor = \App\Models\MentorProfile::findOrFail($mentorId);
    $newAverage = \App\Models\Review::where('mentor_profile_id', $mentorId)->avg('rating');
    $mentor->update(['rating_avg' => round($newAverage, 2)]);

    return response()->json(['message' => 'Feedback saved and profile updated'], 201);
}
}