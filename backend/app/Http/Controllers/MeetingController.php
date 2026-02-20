<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\ConferenceData;
use Google\Service\Calendar\ConferenceSolutionKey;
use Google\Service\Calendar\CreateConferenceRequest;

class MeetingController extends Controller
{
public function store(Request $request)
{
    $request->validate([
        'mentor_profile_id' => 'required|exists:mentor_profiles,id',
        'request_date' => 'required|date|after_or_equal:today',
        'start_time' => 'required',
        'end_time' => 'required|after:start_time',
    ]);

    $mentor = \App\Models\MentorProfile::with('user')->findOrFail($request->mentor_profile_id);
    $student = Auth::user(); 

    $formattedDate = \Carbon\Carbon::parse($request->request_date)->format('d-m-y');

    $roomName = "StudentCircus-" . 
                Str::slug($mentor->user->name) . "-" . 
                Str::slug($student->name) . "-" . 
                $formattedDate;
    
    $jitsiLink = "https://meet.jit.si/" . $roomName;

    $meeting = Meeting::create([
        'student_id' => $student->id, 
        'mentor_profile_id' => $request->mentor_profile_id,
        'date' => $request->request_date,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time,
        'message' => $request->message,
        'status' => 'pending',
        'meeting_link' => $jitsiLink
    ]);

    return response()->json(['message' => 'Request sent successfully', 'meeting' => $meeting], 201);
}

public function studentIndex()
{
    return Meeting::where('student_id', Auth::id())
        ->with('mentorProfile.user')
        ->get()
        ->map(function ($meeting) {
            return [
                'id' => $meeting->id,
                'mentor_name' => $meeting->mentorProfile->user->name,
                'date' => $meeting->date,
                'start_time' => $meeting->start_time,
                'status' => $meeting->status,
                'meeting_link' => $meeting->meeting_link
            ];
        });
}

public function requests()
{
    $mentorId = Auth::user()->mentorProfile->id;
    return Meeting::where('mentor_profile_id', $mentorId)
        ->where('status', 'pending')
        ->with('student') 
        ->get();
}

public function upcoming()
{
    $mentorId = Auth::user()->mentorProfile->id;
    return Meeting::where('mentor_profile_id', $mentorId)
        ->where('status', 'approved')
        ->with('student') 
        ->get()
        ->map(function ($meeting) {
            return [
                'id' => $meeting->id,
                'student_name' => $meeting->student->name, 
                'date' => $meeting->date,
                'start_time' => $meeting->start_time,
                'end_time' => $meeting->end_time,
                'meeting_link' => $meeting->meeting_link, 
            ];
        });
}

public function approve($id)
{
    $mentor = Auth::user()->mentorProfile;
    
    $meeting = Meeting::where('id', $id)
        ->where('mentor_profile_id', $mentor->id)
        ->firstOrFail();

    $meeting->update(['status' => 'approved']);

    return response()->json([
        'message' => 'Meeting approved!',
        'link' => $meeting->meeting_link,
        'status' => 'approved'
    ]);
}

public function handleAction($id, $action)
{
    $meeting = Meeting::findOrFail($id);
    
    if ($action === 'approve') {
        return $this->approve($id);
    } elseif ($action === 'decline') {
        $meeting->update(['status' => 'declined']);
        return response()->json(['message' => 'Meeting declined']);
    }
    
    return response()->json(['message' => 'Invalid action provided'], 400);
}
}