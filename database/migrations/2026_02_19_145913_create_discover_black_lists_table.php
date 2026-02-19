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
       Schema::create('discover_blacklists', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Engelleyen kişi
    $table->foreignId('pup_profile_id')->constrained()->onDelete('cascade'); // Görülmek istenmeyen profil
    $table->timestamps();

    // Aynı profili iki kez eklememek için eşsiz index
    $table->unique(['user_id', 'pup_profile_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discover_black_lists');
    }
};
