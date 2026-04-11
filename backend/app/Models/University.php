<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The University model represents a higher education institution within the system.
 * It is used to categorise both mentors and students based on their academic 
 * affiliations and geographic locations.
 */
class University extends Model
{
        /**
        * The attributes that are mass assignable.
        * * @var array
        */
    protected $fillable = ['id', 'name', 'country'];
}
