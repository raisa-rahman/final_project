<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mentor_profiles', function (Blueprint $blueprint) {
            $blueprint->dropColumn('university_id');
            $blueprint->string('university')->nullable();
        });

        Schema::dropIfExists('universities');
    }

    public function down(): void
    {
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('mentor_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('university_id')->nullable();
            $table->dropColumn('university');
        });
    }
};