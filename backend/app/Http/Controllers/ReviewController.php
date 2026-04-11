<?php

namespace App\Http\Controllers;


use App\Models\Review;
use App\Models\Meeting;
use App\Models\MentorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ReviewController manages the feedback loop between students and mentors.
 * It handles the creation of reviews and synchronises the mentor's average rating.
 */

class ReviewController extends Controller
{

/**
     * Store a newly created review in the database.
     * * This method validates the feedback, ensures a review hasn't already been 
     * submitted for the meeting, and recalculates the mentor's overall rating.
     */
public function store(Request $request)
{
    // Validate the incoming request data
    // Note: unique:reviews,meeting_id ensures a meeting cannot be reviewed twice
    $request->validate([
    'meeting_id' => 'required|exists:meetings,id|unique:reviews,meeting_id',
    'mentor_profile_id' => 'required', 
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|max:500'
    ]);

    // Retrieve the currently authenticated student's ID
    $studentId = \Auth::id();
    if (!$studentId) {
        return response()->json(['message' => 'User not authenticated'], 401);
    }

    try {
        // Initialise and populate the Review model
        $review = new \App\Models\Review();
        $review->meeting_id = $request->meeting_id;
        $review->student_id = $studentId;
        $review->mentor_profile_id = $request->mentor_profile_id;
        $review->rating = (int)$request->rating;
        $review->comment = $request->comment;
        $review->save();
        // Recalculate the average rating for the mentor
        // We fetch the mean of all ratings associated with this mentor's profile
        $avg = \App\Models\Review::where('mentor_profile_id', $request->mentor_profile_id)->avg('rating') ?? 0;
        // Update the mentor's profile with the new rounded average for performance/sorting
        $mentor = \App\Models\MentorProfile::findOrFail($request->mentor_profile_id);
        $mentor->update(['rating_avg' => round((float)$avg, 1)]);

        return response()->json(['message' => 'Review submitted successfully'], 201);
    } catch (\Exception $e) {
        // Provide a detailed error message if the save or update fails
        return response()->json(['message' => 'Backend Error: ' . $e->getMessage()], 500);
    }
    }
}