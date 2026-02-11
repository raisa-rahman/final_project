<?php

namespace App\Http\Controllers;

use App\Models\MentorProfile;
use Illuminate\Http\Request;

class DirectoryController extends Controller
{
    public function index(Request $request)
    {
        $query = MentorProfile::with(['user', 'university'])
            ->where('is_verified', 1);

        if ($request->has('subject')) {
            $query->where('subject_expertise', 'like', '%' . $request->subject . '%');
        }

        if ($request->has('university_id')) {
            $query->where('university_id', $request->university_id);
        }

        if ($request->has('country')) {
            $query->where('target_country', $request->country);
        }

        return response()->json($query->get());
    }
}