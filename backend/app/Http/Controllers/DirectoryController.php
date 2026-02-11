<?php

namespace App\Http\Controllers;

use App\Models\MentorProfile;
use Illuminate\Http\Request;

class DirectoryController extends Controller
{
public function index(Request $request)
{
    $query = MentorProfile::with(['user'])
        ->where('is_verified', 1);

    if ($request->filled('subject')) {
        $query->where('subject_expertise', 'like', '%' . $request->subject . '%');
    }

    if ($request->filled('country')) {
        $query->where('target_country', $request->country);
    }

    $results = $query->get();
    
    \Log::info("Found " . $results->count() . " verified mentors.");

    return response()->json($results);
}
}