<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The Meeting model represents a scheduled mentorship session between a student and a mentor.
 * It tracks the lifecycle of the appointment from the initial request to completion.
 */
class Meeting extends Model
{
    /**
     * The attributes that are mass assignable.
     * * @var array
     */
    protected $fillable = [
        'student_id',        // Foreign key linking to the User (student)
        'mentor_profile_id', // Foreign key linking to the MentorProfile
        'date',              // The scheduled date for the session
        'start_time',        // The start time of the meeting
        'end_time',          // The end time of the meeting
        'message',           // Personal note or enquiry from the student
        'status',            // Current state (e.g., pending, approved, declined, completed)
        'meeting_link'       // The URL for the video conference (e.g., Jitsi or Google Meet)
    ];

    /**
     * Define the relationship to the student.
     * * Each meeting is requested by a specific user acting as a student.
     * * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Define the relationship to the mentor profile.
     * * Each meeting is associated with a specific mentor's professional profile.
     * * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function mentorProfile()
    {
        return $this->belongsTo(MentorProfile::class);
    }

    /**
     * Define the relationship to the review.
     * * Once a meeting has concluded, it may be associated with a single piece of feedback.
     * * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function review()
    {
        return $this->hasOne(Review::class);
    }
}