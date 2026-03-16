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
    'meeting_id' => 'required|exists:meetings,id|unique:reviews,meeting_id',
    'mentor_profile_id' => 'required', 
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|max:500'
    ]);

    $studentId = \Auth::id();
    if (!$studentId) {
        return response()->json(['message' => 'User not authenticated'], 401);
    }

    try {
        $review = new \App\Models\Review();
        $review->meeting_id = $request->meeting_id;
        $review->student_id = $studentId;
        $review->mentor_profile_id = $request->mentor_profile_id;
        $review->rating = (int)$request->rating;
        $review->comment = $request->comment;
        $review->save();
        $avg = \App\Models\Review::where('mentor_profile_id', $request->mentor_profile_id)->avg('rating') ?? 0;
        $mentor = \App\Models\MentorProfile::findOrFail($request->mentor_profile_id);
        $mentor->update(['rating_avg' => round((float)$avg, 1)]);

        return response()->json(['message' => 'Review submitted successfully'], 201);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Backend Error: ' . $e->getMessage()], 500);
    }
    }
}