<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\University;
use App\Models\MentorProfile;

class MentorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $uni = University::create([
            'name' => 'City St Georges University of London',
            'country' => 'United Kingdom'
        ]);

        $user = User::create([
            'name' => 'Raisa Rahman',
            'email' => 'raisa.rahman@city.ac.uk',
            'password' => bcrypt('password'),
            'role' => 'mentor'
        ]);

        MentorProfile::create([
            'user_id' => $user->id,
            'university_id' => $uni->id,
            'subject_expertise' => 'Computer Science',
            'bio' => 'Alumni from CSGUoL, happy to help!',
            'is_verified' => false
        ]);
    }
}
