<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MentorProfile;
use Illuminate\Http\JsonResponse;

class MentorProfileController extends Controller
{
    public function index(): JsonResponse
    {
        $mentors = MentorProfile::with('user', 'university')
            ->where('is_verified', true)
            ->get();

        return response()->json($mentors);
    }
}
