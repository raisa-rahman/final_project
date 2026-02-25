<?php

namespace App\Http\Controllers;

use App\Models\MentorProfile;
use Illuminate\Http\Request;

class DirectoryController extends Controller{
    public function index(Request $request) {
        $query = MentorProfile::with(['user'])->where('is_verified', 1);
        if ($request->filled('subject') && $request->subject !== 'undefined') {
            $query->where('subject_expertise', 'like', '%' . $request->subject . '%');
        }
        if ($request->filled('country') && $request->country !== 'undefined') {
            $query->where('target_country', $request->country);
        }
        return response()->json($query->get());

    return MentorProfile::with('user')
        ->where('is_verified', 1)
        ->withCount('reviews') 
        ->get(); 

    }
    public function showPublic($id)
{
    $mentor = MentorProfile::with(['user', 'university', 'reviews'])
        ->withAvg('reviews', 'rating') 
        ->withCount('reviews') 
        ->where('is_verified', 1)
        ->findOrFail($id);

    return response()->json($mentor);
}
}