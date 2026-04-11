<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * The User model represents the primary identity for anyone accessing the platform.
 * It serves as a parent entity that can branch into either a Student or Mentor profile
 * depending on the assigned role.
 */

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Categorises the user (e.g., 'student', 'mentor', or 'admin')
    ];

    /**
     * The relationships that should always be eagerly loaded.
     * * This ensures profile data is available without triggering additional database queries.
     *
     * @var array
     */
    
    protected $with = ['mentorProfile', 'studentProfile'];

    /**
     * Define the relationship to the Student Profile.
     * * A user may have one student-specific profile containing academic goals.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function studentProfile(){
        return $this->hasOne(StudentProfile::class);
    }

 /**
     * The attributes that should be hidden for serialisation.
     * * Prevents sensitive security data from being included in JSON API responses.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

/**
     * Get the attributes that should be cast to specific types.
     * * Ensures dates are Carbon instances and passwords are automatically hashed.
     *
     * @return array<string, string>
     */
    protected function casts(): array{
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Define the relationship to the Mentor Profile.
     * * A user may have one mentor-specific profile containing professional expertise.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function mentorProfile(){
        return $this->hasOne(MentorProfile::class);
    }

    /**
     * The "booted" method of the model.
     * * Utilised here to register model observers. When a user is registered with the 
     * 'student' role, a corresponding StudentProfile is automatically initialised.
     */
    protected static function booted(){
    static::created(function ($user) {
        if ($user->role === 'student') {
            \App\Models\StudentProfile::create(['user_id' => $user->id]);
        }
    });
    }

    public function favorites() {
    return $this->belongsToMany(MentorProfile::class, 'favorites', 'user_id', 'mentor_profile_id')
                ->withTimestamps();
}

/**
     * Define the relationship to meetings.
     * * Tracks sessions where this user is participating as the student.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function meetings()
{
    return $this->hasMany(Meeting::class, 'student_id');
}
}