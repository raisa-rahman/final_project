<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MentorProfileController;
use App\Models\MentorProfile;
use Illuminate\Support\Facades\Storage;

Route::post('/mentors/{id}/upload', function (Request $request, $id) {
    $request->validate(['file' => 'required|file|max:2048']);
    $profile = \App\Models\MentorProfile::findOrFail($id);

    $path = $request->file('file')->store('mentors', 'public');

    if ($request->type === 'photo') {
        $profile->update(['profile_photo_path' => $path]);
    } else {
        $profile->update(['document_path' => $path]);
    }

    return response()->json(['message' => 'Uploaded', 'path' => $path]);
});

Route::prefix('admin')->group(function () {
    Route::get('/mentors', function () {
        return MentorProfile::with('user')->get();
    });

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
