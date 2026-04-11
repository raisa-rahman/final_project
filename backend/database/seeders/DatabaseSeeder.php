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
use Illuminate\Support\Str;

/**
 * DatabaseSeeder populates the database with initial dummy data for development.
 * It creates a functional ecosystem of admins, universities, mentors, students, 
 * meetings, and reviews to simulate a live environment.
 */

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define a consistent hashed password for all generated users
        $password = Hash::make('testtest32!');

    // Pool for student messages to vary meeting requests        
    $studentMessages = collect([
            'I would love to discuss your experience at your current company.',
            'Can we talk about the interview process for software roles?',
            'I am looking for advice on how to pivot into this industry.',
            'Hoping to get some feedback on my portfolio/CV.',
            'I have a few questions about balancing part-time work and studies.',
            'Your career roadmap is very inspiring, would love to learn more.',
            'Looking for tips on networking within the UK market.',
            'I am currently applying for similar roles and would value your insight.',
            'Could you help me understand the day-to-day responsibilities of your role?',
            'I am interested in your transition from university to your first job.'
        ]);

        // Pool for review comments to populate the mentor profiles
        $reviewComments = collect([
            'Insightful session, thank you!',
            'Great advice on my CV, very practical.',
            'The industry insights were invaluable.',
            'Very patient and clear explanations.',
            'Highly recommend this mentor for interview prep.',
            'Helpful discussion about career paths.',
            'Provided amazing resources after the call.',
            'Really boosted my confidence for the application.',
            'Clear, professional, and very knowledgeable.',
            'A fantastic mentor with a wealth of experience.'
        ]);

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
        
        $profileImages = ['image1.jpeg', 'image2.jpeg', 'image3.jpeg', 'image4.jpeg', 'image5.jpeg', 'image6.jpeg', 'image7.jpeg'];

        // 4. Setup Student Data
        $studentNames = ['Alice Wong', 'Ben Foster', 'Chloe Davis', 'Daniel Kim', 'Ethan Hunt'];

        for ($i = 0; $i < 10; $i++) {
            $isApproved = $i < 7; 
            
            $mentorUser = User::create([
                'name' => $mentorNames[$i],
                'email' => "mentor{$i}@example.com",
                'password' => $password,
                'role' => 'mentor'
            ]);

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
                'document_path' => "mentors/identification.jpeg",
                'rating_avg' => 0 
            ]);

            if ($isApproved) {
                for ($j = 0; $j < 5; $j++) {
                    $student = User::firstOrCreate(
                        ['email' => "student{$j}@example.com"],
                        ['name' => $studentNames[$j], 'password' => $password, 'role' => 'student']
                    );

                    $meetingLink = $this->generateJitsiLink($student->name, $mentorUser->name);

                    $pastMeeting = Meeting::create([
                        'mentor_profile_id' => $mentorProfile->id,
                        'student_id' => $student->id,
                        'date' => Carbon::now()->subDays(rand(7, 30))->format('Y-m-d'),
                        'start_time' => '10:00',
                        'end_time' => '11:00',
                        'status' => 'approved',
                        'meeting_link' => $meetingLink,
                        'message' => $studentMessages->random() // Added random message
                    ]);

                    // Only create a review for roughly 60% of past meetings
                    if (rand(1, 10) <= 6) {
                        Review::create([
                            'meeting_id' => $pastMeeting->id,
                            'mentor_profile_id' => $mentorProfile->id,
                            'student_id' => $student->id,
                            'rating' => rand(3, 5),
                            'comment' => $reviewComments->random() 
                        ]);
                    }

                    $this->createMeetings($mentorProfile, $student, $mentorUser, $studentMessages);
                }

                $average = Review::where('mentor_profile_id', $mentorProfile->id)->avg('rating');
                $mentorProfile->update([
                    'rating_avg' => $average ? round($average, 1) : 0
                ]);
            }
        }
    }

    private function generateJitsiLink($studentName, $mentorName)
    {
        $sName = Str::slug($studentName, '');
        $mName = Str::slug($mentorName, '');
        return "https://meet.jit.si/StudentCircus-{$sName}-{$mName}";
    }

    private function createMeetings($mentorProfile, $student, $mentorUser, $studentMessages)
    {
        $meetingLink = $this->generateJitsiLink($student->name, $mentorUser->name);

        Meeting::create([
            'mentor_profile_id' => $mentorProfile->id,
            'student_id' => $student->id,
            'date' => Carbon::now()->startOfWeek()->addDays(rand(0, 4))->format('Y-m-d'),
            'start_time' => '14:00',
            'end_time' => '15:00',
            'status' => 'approved',
            'meeting_link' => $meetingLink,
            'message' => $studentMessages->random() // Added random message
        ]);

        Meeting::create([
            'mentor_profile_id' => $mentorProfile->id,
            'student_id' => $student->id,
            'date' => Carbon::now()->addWeek()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'status' => 'approved',
            'meeting_link' => $meetingLink,
            'message' => $studentMessages->random() // Added random message
        ]);

        Meeting::create([
            'mentor_profile_id' => $mentorProfile->id,
            'student_id' => $student->id,
            'date' => Carbon::now()->addMonth()->format('Y-m-d'),
            'start_time' => '11:00',
            'end_time' => '12:00',
            'status' => 'approved',
            'meeting_link' => $meetingLink,
            'message' => $studentMessages->random() // Added random message
        ]);

        Meeting::create([
            'mentor_profile_id' => $mentorProfile->id,
            'student_id' => $student->id,
            'date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'start_time' => '16:00',
            'end_time' => '17:00',
            'status' => 'pending',
            'meeting_link' => null,
            'message' => $studentMessages->random() // Added random message
        ]);
    }
}