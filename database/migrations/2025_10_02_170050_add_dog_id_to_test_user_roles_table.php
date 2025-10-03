<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_user_roles', function (Blueprint $table) {
            $table->foreignId('dog_id')
                ->nullable()
                ->constrained('user_dogs')
                ->nullOnDelete()
                ->after('role_id');
        });
    }

    public function down(): void
    {
        Schema::table('test_user_roles', function (Blueprint $table) {
            $table->dropForeign(['dog_id']);
            $table->dropColumn('dog_id');
        });
    }
};
