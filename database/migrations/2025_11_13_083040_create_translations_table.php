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
        Schema::create('translations', function (Blueprint $table) {
             $table->id();

            $table->unsignedBigInteger('translatable_id');
            $table->string('translatable_type'); // model path

            $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');

            $table->string('key');    // name, description vs.
            $table->text('value');    // çeviri metni

            $table->timestamps();

            // performans için index
            $table->index(['translatable_id', 'translatable_type'], 'translatable_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
