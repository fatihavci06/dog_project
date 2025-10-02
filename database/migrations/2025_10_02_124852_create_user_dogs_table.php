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
        Schema::create('user_dogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // users tablosuyla ilişki
            $table->text('name'); // köpek ismi
            $table->text('gender'); // cinsiyet, dropdown
            $table->integer('age'); // yaş, dropdown
            $table->text('photo')->nullable(); // fotoğraf URL veya dosya yolu
            $table->text('biography')->nullable(); // biyografi
            $table->text('food')->nullable(); // hangi mama yiyor
            $table->text('health_status')->nullable(); // sağlık bilgisi, dropdown
            $table->text('size')->nullable(); // boyut, deal breaker için, dropdown
            $table->timestamps();
            $table->softDeletes(); // soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_dogs');
    }
};
