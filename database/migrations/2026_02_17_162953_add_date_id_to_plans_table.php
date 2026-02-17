<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->foreignId('date_id')
                ->nullable()
                ->after('user_id')
                ->constrained('dates')
                ->cascadeOnDelete(); // date silinirse plan da silinir
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropForeign(['date_id']);
            $table->dropColumn('date_id');
        });
    }
};
