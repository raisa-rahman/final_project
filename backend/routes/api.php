<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MentorProfileController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\ReviewController;

Route::post('/reviews', [\App\Http\Controllers\ReviewController::class, 'store']);
Route::get('/api/universities', fn () => \App\Models\University::all());
Route::get('/universities', fn () => \App\Models\University::all());
Route::get('/mentors/directory', [DirectoryController::class, 'index']);
Route::get('/universities', function() {
    return App\Models\University::all();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::post('/meetings/{id}/cancel', [MeetingController::class, 'cancel']);
    Route::post('/mentor/meetings/{id}/approve', [MeetingController::class, 'approve']);
    Route::post('/mentor/meetings/{id}/decline', [MeetingController::class, 'handleAction']);
    Route::post('/meetings/{id}/reschedule', [MeetingController::class, 'reschedule']);
    Route::post('/meetings/request', [MeetingController::class, 'store']);
    Route::get('/mentors/profile/{id}', function ($id) {
    return \App\Models\MentorProfile::with(['user', 'university', 'reviews'])
        ->findOrFail($id);
});

Route::get('/student/profile', function (Request $request) {
    return response()->json(['profile' => $request->user()->studentProfile]);
});

Route::get('/student/favorites', function (Request $request) {
});
    Route::get('/mentor/profile', function (Request $request) {
        return response()->json(['profile' => $request->user()->mentorProfile]);
    });
    Route::get('/student/meetings', [MeetingController::class, 'studentIndex']);
    Route::get('/mentor/meetings/requests', [MeetingController::class, 'requests']);
    Route::get('/mentor/meetings/upcoming', [MeetingController::class, 'upcoming']);
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

Route::post('/register', function (Request $request) {
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'role' => 'required|string|in:student,mentor',
        'university_id' => 'required_if:role,mentor|exists:universities,id' 
    ]);

    $user = \App\Models\User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => bcrypt($data['password']),
        'role' => $data['role'],
    ]);

    if ($data['role'] === 'mentor') {
        \App\Models\MentorProfile::create([
            'user_id' => $user->id,
            'university_id' => $data['university_id'], 
            'is_verified' => false, 
            'subject_expertise' => 'Pending update',
        ]);
    } elseif ($data['role'] === 'student') {
        \App\Models\StudentProfile::create([
            'user_id' => $user->id,
            'target_university' => null, 
            'target_country' => null,   
        ]);
    }

    return response()->json($user, 201);
});