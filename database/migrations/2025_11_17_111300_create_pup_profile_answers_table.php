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
        Schema::create('pup_profile_answers', function (Blueprint $table) {
            $table->id();

            // Each dog has its own answers
            $table->foreignId('pup_profile_id')->constrained('pup_profiles')->onDelete('cascade');

            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');

            // Ordered list of options → stored as multiple rows
            $table->foreignId('option_id')->constrained('options')->onDelete('cascade');
            $table->unsignedInteger('order_index');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pup_profile_answers');
    }
};
