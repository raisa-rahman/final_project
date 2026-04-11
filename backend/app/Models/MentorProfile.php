<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The MentorProfile model stores professional and academic details for mentors.
 * It serves as the central hub for connecting users to their mentorship data,
 * including reviews, schedules, and verification documents.
 */
class MentorProfile extends Model
{
    /**
     * The attributes that are mass assignable.
     * * @var array
     */
protected $fillable = [
        'user_id',                 // Foreign key linking to the User model
        'university_id',           // Foreign key linking to the University model
        'subject_expertise',       // The primary field or subject the mentor teaches
        'current_role',            // The mentor's current job title
        'company',                 // The organisation where the mentor is currently employed
        'graduation_year',         // Year the mentor completed their degree
        'bio',                     // A brief personal introduction or professional summary
        'interests',               // Personal or professional interests to help match with students
        'roadmap',                 // Guidance or career path advice provided by the mentor
        'ask_me_about',            // Specific topics the mentor is happy to discuss
        'is_verified',             // Status flag for admin approval (e.g. 0: Pending, 1: Verified, 2: Rejected)
        'target_country',          // Geographic area of focus for the mentor's expertise
        'current_university_year', // Mentor's current year of study (if applicable)
        'rating_avg',              // Cached average rating calculated from reviews
        'profile_photo_path',      // File path to the mentor's uploaded profile picture
        'document_path'            // File path to verification documents (e.g. certificates or IDs)
    ];

    /**
     * Define the relationship to the base User.
     * * Each profile belongs to one specific user account.
     * * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }

    public function defaultAvailabilities()
    {
        return $this->hasMany(DefaultAvailability::class);
    }

    /**
     * Define the relationship to meetings.
     * * Tracks all mentorship sessions associated with this profile.
     * * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function meetings()
    {
        return $this->hasMany(Meeting::class);
    }

    /**
     * Define the relationship to reviews.
     * * Collects all feedback and ratings submitted by students.
     * * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reviews() 
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Define the relationship to the University.
     * * Links the mentor to their associated academic institution.
     * * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites', 'mentor_profile_id', 'user_id');
    }
}