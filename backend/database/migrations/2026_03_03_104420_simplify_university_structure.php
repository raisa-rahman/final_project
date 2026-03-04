<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
    Schema::table('mentor_profiles', function (Blueprint $table) {
        // SQLite doesn't support renaming foreign keys easily, 
        // so we drop the old and add the new
        $table->dropColumn('university_id');
        $table->string('university')->nullable();
    });
    Schema::dropIfExists('universities');
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
