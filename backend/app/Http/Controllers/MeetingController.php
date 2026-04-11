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
/**
 * MeetingController manages the lifecycle of a mentorship session,
 * including booking, approvals, rescheduling, and history tracking.
 */
class MeetingController extends Controller
{
    /**
     * Store a new meeting request in the database.
     * Validates input, generates a unique Jitsi meeting link, 
     * and sets the initial status to 'pending'.
     */
public function store(Request $request)
{
    // Ensure all required booking details are present and logical
    $request->validate([
        'mentor_profile_id' => 'required|exists:mentor_profiles,id',
        'request_date' => 'required|date|after_or_equal:today',
        'start_time' => 'required',
        'end_time' => 'required|after:start_time',
    ]);

    $mentor = \App\Models\MentorProfile::with('user')->findOrFail($request->mentor_profile_id);
    $student = Auth::user(); 
    // Format date for the URL-friendly meeting room name
    $formattedDate = \Carbon\Carbon::parse($request->request_date)->format('d-m-y');
    // Create a slug-based room name for the Jitsi video call
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

/**
     * Retrieve all meetings associated with the authenticated student.
     * Includes mentor details and any existing reviews.
     */

public function studentIndex()
{
    return Meeting::where('student_id', Auth::id())
        ->where('status', '!=', 'declined') 
        ->with(['mentorProfile.user', 'review'])
        ->get()
        ->map(function ($meeting) {
            return [
                'id' => $meeting->id,
                'mentor_name' => $meeting->mentorProfile->user->name,
                'mentor_profile_id' => $meeting->mentor_profile_id,
                'date' => $meeting->date,
                'start_time' => $meeting->start_time,
                'end_time' => $meeting->end_time,
                'status' => $meeting->status,
                'meeting_link' => $meeting->meeting_link,
                // 2. Map the review data so the frontend can see it
                'review' => $meeting->review,               
                'message' => $meeting->message, // ADD THIS LINE
            ];
        });
}

/**
     * Fetch pending meeting requests for the logged-in mentor.
     */
public function requests()
{
    $user = Auth::user();
    if (!$user || !$user->mentorProfile) {
        return response()->json([]); 
    }

    $mentorId = $user->mentorProfile->id;
    return Meeting::where('mentor_profile_id', $mentorId)
        ->where('status', 'pending')
        ->with('student') 
        ->get();
}

/**
     * Fetch approved/confirmed meetings for the mentor's schedule.
     * Ordered chronologically to show the next upcoming session first.
     */
public function upcoming()
{
    $user = Auth::user();

    if (!$user || !$user->mentorProfile) {
        return response()->json([]); 
    }

    $mentorId = $user->mentorProfile->id;
    return Meeting::where('mentor_profile_id', $mentorId)
        ->where('status', 'approved')
        ->with('student') 
        ->orderBy('date', 'asc')
        ->orderBy('start_time', 'asc')
        ->get()
        ->map(function ($meeting) {
            return [
                'id' => $meeting->id,
                'student_name' => $meeting->student->name, 
                'date' => $meeting->date,
                'start_time' => $meeting->start_time,
                'end_time' => $meeting->end_time,
                'meeting_link' => $meeting->meeting_link, 
                'status' => $meeting->status,
                'message' => $meeting->message,
            ];
        });
}

/**
     * Update a meeting status to 'approved'.
     */

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

/**
     * Route a request to either approval or decline logic based on the action parameter.
     */
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

/**
     * Change the date/time of an existing meeting.
     * This resets the meeting status to 'pending', requiring a fresh approval.
     */
public function reschedule(Request $request, $id)
{
    
    $request->validate([
        'date' => 'required|date|after_or_equal:today',
        'start_time' => 'required|date_format:H:i', 
        'end_time' => 'required|date_format:H:i|after:start_time', 
    ]);

    $meeting = Meeting::findOrFail($id);
    // Ensure only the involved student or mentor can reschedule
    $isStudent = Auth::id() === $meeting->student_id;
    $isMentor = Auth::user()->mentorProfile && Auth::user()->mentorProfile->id === $meeting->mentor_profile_id;

    if (!$isStudent && !$isMentor) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $mentor = $meeting->mentorProfile->user;
    $student = $meeting->student; 
    $formattedDate = \Carbon\Carbon::parse($request->date)->format('d-m-y');
    // Regenerate the link to reflect the new date
    $roomName = "StudentCircus-" . Str::slug($mentor->name) . "-" . Str::slug($student->name) . "-" . $formattedDate;
    $newLink = "https://meet.jit.si/" . $roomName;

    $meeting->update([
        'date' => $request->date,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time, 
        'status' => 'pending', 
        'meeting_link' => $newLink
    ]);

    return response()->json(['message' => 'Reschedule successful. Status reset to pending.']);
}

/**
     * Cancel a meeting by setting its status to 'declined'.
     */
public function cancel($id)
{
    $meeting = Meeting::findOrFail($id);
    
    // Authorisation check for both participants
    if (Auth::id() !== $meeting->student_id && Auth::user()->mentorProfile->id !== $meeting->mentor_profile_id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $meeting->update(['status' => 'declined']); 

    return response()->json(['message' => 'Meeting cancelled successfully']);
}

/**
     * Retrieve the mentor's history of meetings.
     * Includes past dates, declined sessions, and completed sessions.
     */
public function past(Request $request)
{
    $mentorProfile = $request->user()->mentorProfile;
    
    if (!$mentorProfile) {
        return response()->json([]);
    }

    return \App\Models\Meeting::where('mentor_profile_id', $mentorProfile->id)
        ->where(function ($query) {
            $query->where('date', '<', now()->toDateString())
                  ->orWhere('status', 'declined')
                  ->orWhere('status', 'completed');
        })
        ->with(['student', 'review']) 
        ->orderBy('date', 'desc')
        ->get()
        ->map(function ($meeting) {
            return [
                'id' => $meeting->id,
                'student_name' => $meeting->student ? $meeting->student->name : 'Unknown Student',
                'date' => $meeting->date,
                'start_time' => $meeting->start_time,
                'end_time' => $meeting->end_time,
                'status' => $meeting->status,
                'review' => $meeting->review, 
                'message' => $meeting->message,
            ];
        });
}
}

