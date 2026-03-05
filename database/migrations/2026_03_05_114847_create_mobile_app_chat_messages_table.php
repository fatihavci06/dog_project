<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_app_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->integer('order')->default(0); // type alanının altına ekle
            $table->enum('type', ['question', 'message'])->default('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_app_chat_messages');
    }
};
