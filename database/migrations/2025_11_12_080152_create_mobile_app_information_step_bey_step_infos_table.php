<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mobile_app_information_step_bey_step_infos', function (Blueprint $table) {
            $table->id();

            // Sabit adım numarası (ör: 1, 2, 3, 4...)
            $table->integer('step_number');

            // Çok dilli olmayan sabit görsel
            $table->string('image_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_app_information_step_bey_step_infos');
    }
};

