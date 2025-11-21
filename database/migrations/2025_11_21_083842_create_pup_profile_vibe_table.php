<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pup_profile_vibe', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pup_profile_id');
            $table->unsignedBigInteger('vibe_id');

            $table->foreign('pup_profile_id')->references('id')->on('pup_profiles')->onDelete('cascade');
            $table->foreign('vibe_id')->references('id')->on('vibes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pup_profile_vibe');
    }
};
