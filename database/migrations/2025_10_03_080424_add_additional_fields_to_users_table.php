<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->after('id');
            $table->string('onesignal_player_id')->nullable()->after('role_id');
            $table->string('location_city')->nullable()->after('onesignal_player_id');
            $table->string('location_district')->nullable()->after('location_city');
            $table->text('biography')->nullable()->after('location_district');
            $table->string('photo')->nullable()->after('biography');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role_id',
                'onesignal_player_id',
                'location_city',
                'location_district',
                'biography',
                'photo'
            ]);
        });
    }
};
