<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\University;

class UniversitySeeder extends Seeder
{
    public function run(): void
    {
        // Using 'country' matches your migration file exactly
        University::create([
            'name' => 'City, St Georges University of London',
            'country' => 'United Kingdom'
        ]);
    }
}