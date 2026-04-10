<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $table = 'student_profiles'; 

    protected $fillable = [
        'user_id',
        'target_university',
        'target_country',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}