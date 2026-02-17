<?php

namespace App\Http\Controllers;

use App\Models\DefaultAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DefaultAvailabilityController extends Controller
{

    public function up() {
    Schema::create('default_availabilities', function (Blueprint $table) {
        $table->id();
        $table->foreignId('mentor_profile_id')->constrained()->onDelete('cascade');
        $table->integer('day_of_week'); 
        $table->time('start_time');
        $table->time('end_time');
        $table->timestamps();
    });
}

    public function index()
    {
        return response()->json(Auth::user()->mentorProfile->defaultAvailabilities);
    }

    public function store(Request $request)
    {
        $request->validate([
            'day_of_week' => 'required|integer|between:0,6',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        $mentor = Auth::user()->mentorProfile;

        $slot = $mentor->defaultAvailabilities()->create([
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return response()->json($slot);
    }

public function update(Request $request, $id) {
    $request->validate([
        'day_of_week' => 'required|integer|between:0,6',
        'start_time' => 'required',
        'end_time' => 'required|after:start_time',
    ]);

    $slot = Auth::user()->mentorProfile->defaultAvailabilities()->findOrFail($id);
    $slot->update([
        'day_of_week' => $request->day_of_week,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time,
    ]);

    return response()->json($slot);
}

public function destroy($id)
{
    $slot = Auth::user()->mentorProfile->defaultAvailabilities()->findOrFail($id);
    
    $slot->delete();

    return response()->json(['message' => 'Recurring slot removed successfully']);
}
}