<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable = [
        'student_id',
        'mentor_profile_id',
        'date',
        'start_time',
        'end_time',
        'message',
        'status',
        'meeting_link' 
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function mentorProfile()
    {
        return $this->belongsTo(MentorProfile::class);
    }
    public function review()
{
    return $this->hasOne(Review::class);
}
}