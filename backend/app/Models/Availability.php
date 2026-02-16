<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    protected $fillable = ['mentor_profile_id', 'available_date', 'start_time', 'end_time', 'is_booked'];

    public function mentor(){
        return $this->belongsTo(MentorProfile::class);
    }
}