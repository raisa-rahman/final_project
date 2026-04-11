<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The Review model captures feedback provided by students after a meeting.
 * It links the specific session, the student, and the mentor to a numerical 
 * rating and an optional written comment.
 */
class Review extends Model
{
        /**
        * The attributes that are mass assignable.
        * * @var array
        */
protected $fillable = [
        'student_id',        // Foreign key linking to the User (student) who wrote the review
        'meeting_id',        // Foreign key linking to the specific Meeting being reviewed
        'mentor_profile_id', // Foreign key linking to the MentorProfile being rated
        'rating',            // Integer value (typically 1-5) representing the student's satisfaction
        'comment'            // Optional qualitative feedback or detailed remarks
    ];

    /**
     * Define the relationship to the mentor profile.
     * * Each review is mapped to a specific mentor's profile to facilitate 
     * the calculation of their average rating.
     * * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mentorProfile() {
        return $this->belongsTo(MentorProfile::class, 'mentor_profile_id');
    }
}