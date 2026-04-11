<?php

namespace App\Http\Controllers;

use App\Models\MentorProfile;
use Illuminate\Http\Request;

//DirectoryController handles the public-facing retrieval of mentor profiles.

class DirectoryController extends Controller{
    /**
     * Initialise the mentor directory with optional filtering.
     * * This method fetches all verified mentors along with their associated 
     * user and university details, plus aggregated review statistics.
     */
    public function index(Request $request) {
        // Start the query builder with necessary relationships and aggregates
    $query = MentorProfile::with(['user', 'university'])
        ->withCount('reviews')
        ->withAvg('reviews', 'rating')
        ->where('is_verified', 1);

    // Filter by subject expertise if a valid value is provided
    if ($request->filled('subject') && $request->subject !== 'undefined') {
        $query->where('subject_expertise', 'like', '%' . $request->subject . '%');
    }
    
    // Filter by target country if a valid value is provided
    if ($request->filled('country') && $request->country !== 'undefined') {
        $query->where('target_country', $request->country);
    }

    // Return the filtered list as a JSON response
    return response()->json($query->get());
}

/**
     * Display the public profile for a specific mentor.
     * * Retrieves detailed information for a single verified mentor, 
     * ensuring that unauthorised or unverified profiles remain hidden.
     */
    public function showPublic($id)
{
    $mentor = MentorProfile::with(['user', 'university', 'reviews'])
        ->withAvg('reviews', 'rating') 
        ->withCount('reviews') 
        ->where('is_verified', 1) // Ensure we only show verified mentors
        ->findOrFail($id); // Throws a 404 if the mentor is not found

    return response()->json($mentor);
}
}