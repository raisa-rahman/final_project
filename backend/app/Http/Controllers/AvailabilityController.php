<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvailabilityController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'available_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        $mentorProfile = Auth::user()->mentorProfile;

        $availability = Availability::create([
            'mentor_profile_id' => $mentorProfile->id,
            'available_date' => $request->available_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_booked' => false
        ]);

        return response()->json(['message' => 'Slot added successfully', 'availability' => $availability]);
    }

    public function index()
    {
        $mentorProfile = Auth::user()->mentorProfile;
        return response()->json($mentorProfile->availabilities()->orderBy('available_date')->get());
    }

    public function destroy($id)
    {
        $availability = Availability::findOrFail($id);
        $availability->delete();
        return response()->json(['message' => 'Slot removed']);
    }
}