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
       Schema::create('pup_profiles', function (Blueprint $table) {

    $table->id();

    // A user can have multiple dogs
    $table->foreignId('user_id')
        ->constrained('users')
        ->onDelete('cascade');

    // Pup basic fields
    $table->string('name')->nullable();
    $table->enum('sex', ['male', 'female','neutered'])->nullable();

    // Optional selects
    $table->foreignId('breed_id')->nullable()->constrained('breads');
    $table->foreignId('age_range_id')->nullable()->constrained('age_ranges');
    $table->foreignId('looking_for_id')->nullable()->constrained('looking_fors');
    $table->foreignId('vibe_id')->nullable()->constrained('vibes');
    $table->foreignId('health_info_id')->nullable()->constrained('health_infos');
    $table->foreignId('travel_radius_id')->nullable()->constrained('travel_radius');
    $table->foreignId('availability_for_meetup_id')->nullable()->constrained('availability_for_meetups');

    // Location
    $table->string('lat')->nullable();
    $table->string('long')->nullable();
    $table->string('city')->nullable();
    $table->string('district')->nullable();

    // Biography (optional)
    $table->longText('biography')->nullable();

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pup_profiles');
    }
};
