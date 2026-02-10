<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\StudentProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase; 

    /** @test */
    public function automated_student_profile_creation()
    {
        $user = User::create([
            'name' => 'Test Student',
            'email' => 'student@test.com',
            'password' => bcrypt('password'),
            'role' => 'student'
        ]);

        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function custom_api_login_returns_json_user_object()
    {
        $user = User::factory()->create([
            'email' => 'login@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $response = $this->postJson('http://localhost:8000/api/login', [
            'email' => 'login@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'user' => ['id', 'name', 'email', 'role']
                 ]);
    }
}