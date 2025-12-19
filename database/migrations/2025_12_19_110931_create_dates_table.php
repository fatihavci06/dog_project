<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dates', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');

            // 1. Tarih ve Saat (DB'de birleşik tutmak sorgulama için en iyisidir)
            $table->dateTime('meeting_date');

            // 2. Flexible Time Range (Checkbox)
            $table->boolean('is_flexible')->default(false);

            // 3. Location (Harita ve Manuel Adres)
            $table->string('address')->nullable(); // "Enter address manually"
            $table->decimal('latitude', 10, 8)->nullable(); // "Select on Map"
            $table->decimal('longitude', 11, 8)->nullable(); // "Select on Map"

            // Durum
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');

            $table->timestamps();

            $table->index(['sender_id', 'status']);
            $table->index(['receiver_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dates');
    }
};
