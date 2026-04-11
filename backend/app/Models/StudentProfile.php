<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The StudentProfile model stores supplementary information specific to students.
 * It tracks their academic aspirations, such as preferred universities and 
 * study destinations, to help tailor the mentorship experience.
 */

class StudentProfile extends Model
{
    /**
     * The table associated with the model.
     * * Explicitly defined as 'student_profiles'.
     * * @var string
     */
    protected $table = 'student_profiles'; 
    /**
     * The attributes that are mass assignable.
     * * @var array
     */
    protected $fillable = [
        'user_id',           // Foreign key linking to the primary User account
        'target_university', // The institution the student aspires to attend
        'target_country',    // The preferred country for the student's future studies
    ];

    /**
     * Define the relationship to the base User.
     * * Each student profile belongs to a single user account.
     * * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}