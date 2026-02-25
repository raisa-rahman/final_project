<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'university_id',
        'subject_expertise',
        'bio',
        'is_verified',
        'target_country',
        'current_university_year',
        'rating_avg', 
        'profile_photo_path', 
        'document_path'      
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function availabilities(){
        return $this->hasMany(Availability::class);
    }

    public function defaultAvailabilities(){
        return $this->hasMany(DefaultAvailability::class);
    }

    public function meetings()
{
    return $this->hasMany(Meeting::class);
}

    public function reviews() {
    return $this->hasMany(Review::class);
}

public function university()
{
    return $this->belongsTo(University::class);
}

}