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
        Schema::table('profile_flags', function (Blueprint $table) {
            $table->unsignedTinyInteger('flag_type')->comment('1:Spam, 2:Abuse, 3:Fake, 4:Inappropriate')->after('flagged_profile_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_flags', function (Blueprint $table) {
            $table->dropColumn('flag_type');
        });
    }
};
