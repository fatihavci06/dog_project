<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pup_profile_health_info', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pup_profile_id');
            $table->unsignedBigInteger('health_info_id');

            $table->foreign('pup_profile_id')->references('id')->on('pup_profiles')->onDelete('cascade');
            $table->foreign('health_info_id')->references('id')->on('health_infos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pup_profile_health_info');
    }
};
