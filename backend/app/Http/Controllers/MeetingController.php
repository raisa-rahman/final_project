<?php
namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $meeting = Meeting::create([
            'student_id' => Auth::id(), 
            'mentor_profile_id' => $request->mentor_profile_id,
            'date' => $request->request_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'message' => $request->message,
            'status' => 'pending' 
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
                'status' => $meeting->status
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
            ];
        });
}

public function handleAction($id, $action)
{
    $meeting = Meeting::findOrFail($id);
    
    if ($action === 'approve') {
        $meeting->update(['status' => 'approved']);
    } elseif ($action === 'decline') {
        $meeting->update(['status' => 'declined']);
    }
    
    return response()->json(['message' => 'Meeting status updated']);
}

  public function approve($id)
{
    $mentorProfileId = Auth::user()->mentorProfile->id;
    
    $meeting = Meeting::where('id', $id)
        ->where('mentor_profile_id', $mentorProfileId)
        ->firstOrFail();

    $meeting->update(['status' => 'approved']);

    return response()->json(['message' => 'Meeting accepted', 'meeting' => $meeting]);
}
}