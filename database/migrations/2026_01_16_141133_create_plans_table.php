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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            // Genellikle planlar bir kullanıcıya aittir
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('title');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('start_time')->nullable(); // '14:30' formatı için string veya time
            $table->string('end_time')->nullable();

            $table->string('color', 7); // #FFFFFF
            $table->string('location')->nullable();
            $table->decimal('latitude', 10, 8)->nullable(); // JSON'daki "lang"
            $table->decimal('longitude', 11, 8)->nullable(); // JSON'daki "long"

            $table->text('notes')->nullable();
            $table->string('icon')->nullable(); // paw, location, check

            $table->string('type'); // single veya multi-day (Otomatik)

            $table->boolean('completed')->default(false);
            $table->boolean('cancelled')->default(false);

            $table->unsignedBigInteger('participant_id')->nullable(); // İlişki sonradan kurulabilir

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
