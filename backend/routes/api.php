<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MentorProfileController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\StudentProfileController;

Route::get('/api/universities', fn () => \App\Models\University::all());
Route::get('/universities', fn () => \App\Models\University::all());
Route::get('/mentors/directory', [DirectoryController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::put('/mentor/profile', function (Request $request) {
        $request->validate(['subject_expertise' => 'required|string', 'university' => 'required|string', 'bio' => 'nullable|string']);
        $profile = \App\Models\MentorProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            $request->only(['subject_expertise', 'university', 'bio'])
        );
        return response()->json(['profile' => $profile]);
    });

    Route::prefix('admin')->group(function () {
        Route::get('/mentors', fn () => \App\Models\MentorProfile::with('user')->get());
        
        Route::post('/mentors/{id}/toggle-verify', function ($id) {
            $profile = \App\Models\MentorProfile::findOrFail($id);
            $profile->is_verified = !$profile->is_verified;
            $profile->save();
            return response()->json(['is_verified' => $profile->is_verified]);
        });

        Route::post('/universities', function (Request $request) {
            $data = $request->validate(['name' => 'required|string', 'country' => 'required|string']);
            return response()->json(\App\Models\University::create($data));
        });
        
        Route::put('/universities/{id}', function (Request $request, $id) {
            $univ = \App\Models\University::findOrFail($id);
            $univ->update($request->validate(['name' => 'required|string', 'country' => 'required|string']));
            return response()->json($univ);
        });
        
        Route::delete('/universities/{id}', function ($id) {
            \App\Models\University::findOrFail($id)->delete();
            return response()->json(['message' => 'Deleted']);
        });
    });
});

Route::post('/mentors/{id}/upload', function (Request $request, $id) {
    $request->validate(['file' => 'required|file|mimes:jpeg,png,jpg,pdf', 'type' => 'required|string']);
    $profile = \App\Models\MentorProfile::findOrFail($id);
    $path = $request->file('file')->store('mentors', 'public');
    $column = ($request->type === 'photo') ? 'profile_photo_path' : 'document_path';
    $profile->update([$column => $path]);
    return response()->json(['path' => $path]);
});

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return response()->json(['status' => 'success', 'user' => Auth::user()]);
    }

    return response()->json(['message' => 'Invalid credentials'], 401);
});

Route::get('/universities', fn () => \App\Models\University::all());

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        return response()->json(['status' => 'success', 'user' => Auth::user()]);
    }

    return response()->json(['message' => 'Invalid credentials'], 401);
});