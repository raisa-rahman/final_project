<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\University;
use App\Models\MentorProfile;
use App\Models\Meeting;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('testtest32!');

        // 1. Create Admin
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@studentcircus.com',
            'password' => $password,
            'role' => 'admin'
        ]);

        // 2. Create 5 Universities
        $unis = collect(['City, University of London', 'LSE', 'UCL', 'King\'s College London', 'Imperial College'])->map(function ($name) {
            return University::create(['name' => $name, 'country' => 'United Kingdom']);
        });

        // 3. Setup Mentor Data
        $mentorNames = ['Alex Thompson', 'Sarah Jenkins', 'Michael Chen', 'Priya Sharma', 'David Miller', 'Emma Wilson', 'James Taylor', 'Sofia Rodriguez', 'Robert Brown', 'Linda Zhang'];
        $expertise = ['Software Engineering', 'Investment Banking', 'Data Science', 'Marketing', 'Civil Engineering', 'UX Design', 'Product Management', 'Cyber Security', 'Finance', 'Healthcare'];
        $companies = ['Google', 'Goldman Sachs', 'Meta', 'Amazon', 'Arup', 'Airbnb', 'Microsoft', 'CrowdStrike', 'JP Morgan', 'NHS'];
        $roles = ['Senior Engineer', 'Analyst', 'Lead Data Scientist', 'Marketing Manager', 'Structural Engineer', 'Product Designer', 'Product Lead', 'Security Architect', 'Financial Controller', 'Clinical Researcher'];
        
        // Mapping specific images
        $profileImages = ['image1.jpeg', 'image2.jpeg', 'image3.jpeg', 'image4.jpeg', 'image5.jpeg', 'image6.jpeg', 'image7.jpeg'];

        // 4. Setup Student Data
        $studentNames = ['Alice Wong', 'Ben Foster', 'Chloe Davis', 'Daniel Kim', 'Ethan Hunt'];

        for ($i = 0; $i < 10; $i++) {
            $isApproved = $i < 7; // First 7 are approved
            
            $mentorUser = User::create([
                'name' => $mentorNames[$i],
                'email' => "mentor{$i}@example.com",
                'password' => $password,
                'role' => 'mentor'
            ]);

            // Determine which image to use (wrapping around if i > 6)
            $imageName = $profileImages[$i % count($profileImages)];

            $mentorProfile = MentorProfile::create([
                'user_id' => $mentorUser->id,
                'university_id' => $unis->random()->id,
                'subject_expertise' => $expertise[$i],
                'current_role' => $roles[$i],
                'company' => $companies[$i],
                'graduation_year' => rand(2015, 2023),
                'bio' => "Experienced mentor specializing in {$expertise[$i]} at {$companies[$i]}.",
                'interests' => 'Mentoring, Networking, Career Growth',
                'roadmap' => '1. Initial Chat, 2. CV Review, 3. Interview Prep',
                'ask_me_about' => 'Industry insights, landing roles at top firms',
                'is_verified' => $isApproved ? 1 : 0,
                'profile_photo_path' => "mentors/{$imageName}",
                // UPDATED: All mentors now use identification.jpeg
                'document_path' => "mentors/identification.jpeg",
                'rating_avg' => 0 
            ]);

            if ($isApproved) {
                for ($j = 0; $j < 5; $j++) {
                    $student = User::firstOrCreate(
                        ['email' => "student{$j}@example.com"],
                        ['name' => $studentNames[$j], 'password' => $password, 'role' => 'student']
                    );

                    $pastMeeting = Meeting::create([
                        'mentor_profile_id' => $mentorProfile->id,
                        'student_id' => $student->id,
                        'date' => Carbon::now()->subDays(7)->format('Y-m-d'),
                        'start_time' => '10:00',
                        'end_time' => '11:00',
                        'status' => 'approved'
                    ]);

                    Review::create([
                        'meeting_id' => $pastMeeting->id,
                        'mentor_profile_id' => $mentorProfile->id,
                        'student_id' => $student->id,
                        'rating' => rand(3, 5),
                        'comment' => 'Insightful session, thank you!'
                    ]);

                    $this->createMeetings($mentorProfile, $student);
                }

                $average = Review::where('mentor_profile_id', $mentorProfile->id)->avg('rating');
                $mentorProfile->update([
                    'rating_avg' => round($average, 1)
                ]);
            }
        }
    }

    private function createMeetings($mentorProfile, $student)
    {
        Meeting::create([
            'mentor_profile_id' => $mentorProfile->id,
            'student_id' => $student->id,
            'date' => Carbon::now()->startOfWeek()->addDays(rand(0, 4))->format('Y-m-d'),
            'start_time' => '14:00',
            'end_time' => '15:00',
            'status' => 'approved'
        ]);

        Meeting::create([
            'mentor_profile_id' => $mentorProfile->id,
            'student_id' => $student->id,
            'date' => Carbon::now()->addWeek()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'status' => 'approved'
        ]);

        Meeting::create([
            'mentor_profile_id' => $mentorProfile->id,
            'student_id' => $student->id,
            'date' => Carbon::now()->addMonth()->format('Y-m-d'),
            'start_time' => '11:00',
            'end_time' => '12:00',
            'status' => 'approved'
        ]);

        Meeting::create([
            'mentor_profile_id' => $mentorProfile->id,
            'student_id' => $student->id,
            'date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'start_time' => '16:00',
            'end_time' => '17:00',
            'status' => 'pending'
        ]);
    }
}