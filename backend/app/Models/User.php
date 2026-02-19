<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        'role',
    ];
    
    protected $with = ['mentorProfile', 'studentProfile'];

    public function studentProfile(){
        return $this->hasOne(StudentProfile::class);
    }

/**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array{
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function mentorProfile(){
        return $this->hasOne(MentorProfile::class);
    }

    protected static function booted(){
    static::created(function ($user) {
        if ($user->role === 'student') {
            \App\Models\StudentProfile::create(['user_id' => $user->id]);
        }
    });
    }

    public function favorites(){
    return $this->hasMany(Favorite::class);
    }

    public function meetings()
{
    return $this->hasMany(Meeting::class, 'student_id');
}
}