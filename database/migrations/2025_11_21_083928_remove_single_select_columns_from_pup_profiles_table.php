<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pup_profiles', function (Blueprint $table) {

            // 1) FOREIGN KEYS DROP
            try {
                $table->dropForeign(['looking_for_id']);
            } catch (\Exception $e) {}

            try {
                $table->dropForeign(['vibe_id']);
            } catch (\Exception $e) {}

            try {
                $table->dropForeign(['health_info_id']);
            } catch (\Exception $e) {}

            try {
                $table->dropForeign(['availability_for_meetup_id']);
            } catch (\Exception $e) {}

            // 2) COLUMNS DROP
            $table->dropColumn([
                'looking_for_id',
                'vibe_id',
                'health_info_id',
                'availability_for_meetup_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('pup_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('looking_for_id')->nullable();
            $table->unsignedBigInteger('vibe_id')->nullable();
            $table->unsignedBigInteger('health_info_id')->nullable();
            $table->unsignedBigInteger('availability_for_meetup_id')->nullable();
        });
    }
};
