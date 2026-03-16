<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('student_profiles', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('university_name')->nullable();
        $table->string('course_of_study')->nullable();
        $table->string('current_year')->nullable();
        $table->string('target_university')->nullable();
        $table->string('target_country')->nullable(); 
        $table->string('profile_photo_path')->nullable();
        $table->text('bio')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
