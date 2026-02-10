<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\MentorProfile; 
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:student,mentor'], 
        ]);

        try {
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role, 
    ]);

if ($user->role === 'student') {
    \App\Models\StudentProfile::create([
        'user_id' => $user->id,
        'target_university' => 'Pending', 
        'target_country' => 'Pending'
    ]);
} else {
    \App\Models\MentorProfile::create([
        'user_id' => $user->id,
        'university_id' => 1, 
        'subject_expertise' => 'Setup Pending',
        'bio' => 'Mentor profile pending setup.',
        'is_verified' => 0, 
    ]);
}

    Auth::login($user);

    return response()->json($user, 201);

} catch (\Exception $e) {
    return response()->json(['message' => $e->getMessage()], 500);
}
}
}