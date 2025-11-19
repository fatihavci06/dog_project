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
        Schema::table('vibes', function (Blueprint $table) {
            // SVG veya PNG path için string alan
            $table->string('icon_path')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vibes', function (Blueprint $table) {
            $table->dropColumn('icon_path');
        });
    }
};
