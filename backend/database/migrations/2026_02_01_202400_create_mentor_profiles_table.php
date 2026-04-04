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
        Schema::dropIfExists('mentor_profiles');
        Schema::create('mentor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('university_id')->nullable()->constrained();
            $table->string('subject_expertise');
            $table->string('current_role')->nullable(); 
            $table->string('company')->nullable();      
            $table->integer('graduation_year')->nullable();
            $table->text('bio')->nullable();
            $table->text('interests')->nullable();     
            $table->text('roadmap')->nullable();       
            $table->text('ask_me_about')->nullable();  
            $table->integer('is_verified')->default(0); 
            $table->decimal('rating_avg', 3, 1)->default(0);
            $table->string('profile_photo_path')->nullable();
            $table->string('document_path')->nullable();            
            $table->string('target_country')->nullable();
            $table->string('current_university_year')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentor_profiles');
    }
};