<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MentorProfileController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\MeetingController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/mentors/directory', [App\Http\Controllers\DirectoryController::class, 'index']);
    Route::post('/admin/mentors/{profile}/toggle-verify', [MentorProfileController::class, 'toggleVerify']);
    Route::post('/mentors/{id}/favorite', [StudentProfileController::class, 'toggleFavorite']);
    Route::get('/mentor/profile', [App\Http\Controllers\MentorProfileController::class, 'show']);
    Route::put('/mentor/profile', [App\Http\Controllers\MentorProfileController::class, 'update']);
    Route::get('/availabilities', [App\Http\Controllers\AvailabilityController::class, 'index']);
    Route::post('/availabilities', [App\Http\Controllers\AvailabilityController::class, 'store']);
    Route::delete('/availabilities/{id}', [App\Http\Controllers\AvailabilityController::class, 'destroy']);
    Route::get('/mentor/default-availability', [App\Http\Controllers\DefaultAvailabilityController::class, 'index']);
    Route::post('/mentor/default-availability', [App\Http\Controllers\DefaultAvailabilityController::class, 'store']);
    Route::put('/mentor/default-availability/{id}', [DefaultAvailabilityController::class, 'update']);
    Route::get('/mentors/profile/{id}', [DirectoryController::class, 'showPublic']);
    Route::get('/student/favorites', [StudentProfileController::class, 'getFavorites']);
    Route::put('/mentor/default-availability/{id}', [App\Http\Controllers\DefaultAvailabilityController::class, 'update']);
    Route::delete('/mentor/default-availability/{id}', [App\Http\Controllers\DefaultAvailabilityController::class, 'destroy']);
    Route::get('/auth/google', [GoogleCalendarController::class, 'redirectToGoogle']);
    Route::get('/auth/google/callback', [GoogleCalendarController::class, 'handleGoogleCallback']);
    Route::get('/mentor/google-sync', [GoogleCalendarController::class, 'syncAvailability']);
    Route::post('/meetings/request', [MeetingController::class, 'store']);
    Route::get('/student/meetings', [App\Http\Controllers\MeetingController::class, 'studentIndex']);
    Route::get('/mentor/meetings/requests', [MeetingController::class, 'requests']);
    Route::get('/mentor/meetings/upcoming', [MeetingController::class, 'upcoming']);
    Route::post('/mentor/meetings/{id}/{action}', [MeetingController::class, 'handleAction']);
});

Route::get('/mentors/directory', [DirectoryController::class, 'index']);


Route::post('/admin/mentors/{profile}/toggle-verify', function (App\Models\MentorProfile $profile) {
    $profile->is_verified = $profile->is_verified ? 0 : 1;
    $profile->save();

    return response()->json([
        'status' => 'success',
        'is_verified' => $profile->is_verified,
        'mentor' => $profile
    ]);
});

Route::post('/mentors/{id}/upload', function (Request $request, $id) {
    $request->validate([
        'file' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
        'type' => 'required|string'
    ]);

    $profile = \App\Models\MentorProfile::findOrFail($id);

    $path = $request->file('file')->store('mentors', 'public');

    if ($request->type === 'photo') {
        $profile->update(['profile_photo_path' => $path]);
    } else {
        $profile->update(['document_path' => $path]);
    }

    return response()->json(['message' => 'Uploaded successfully', 'path' => $path]);
});

Route::prefix('admin')->group(function () {
    Route::get('/mentors', function () {
return \App\Models\MentorProfile::with('user')->get();    });

    Route::post('/mentors/{id}/toggle-verify', function ($id) {
        $profile = MentorProfile::findOrFail($id);
        $profile->is_verified = !$profile->is_verified;
        $profile->save();

        return response()->json(['message' => 'Status updated', 'is_verified' => $profile->is_verified]);
    });
});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/mentors', [MentorProfileController::class, 'index']);
use App\Http\Controllers\StudentProfileController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/student/profile', [StudentProfileController::class, 'show']);
    Route::put('/student/profile', [StudentProfileController::class, 'update']);
    Route::get('/mentor/profile', [MentorProfileController::class, 'show']);
    Route::put('/mentor/profile', [MentorProfileController::class, 'update']);
});

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'user' => $user
        ]);
    }

    return response()->json(['message' => 'Invalid credentials'], 401);
});
