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

        $meeting = Meeting::findOrFail($request->meeting_id);

        $review = Review::create([
            'student_id' => Auth::id(),
            'meeting_id' => $meeting->id,
            'mentor_profile_id' => $meeting->mentor_profile_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        $mentor = MentorProfile::findOrFail($meeting->mentor_profile_id);
        $average = Review::where('mentor_profile_id', $mentor->id)->avg('rating');
        $mentor->update(['rating_avg' => round($average, 1)]);
        return response()->json(['message' => 'Review saved successfully'], 201);
    }
}