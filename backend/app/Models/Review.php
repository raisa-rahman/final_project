<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'student_id', 
        'meeting_id', 
        'mentor_profile_id', 
        'rating', 
        'comment'
    ];

    public function mentorProfile() {
        return $this->belongsTo(MentorProfile::class);
    }
}