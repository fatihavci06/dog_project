<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // 'type' kolonu ekliyoruz, varsayılan olarak 'info' olsun
            // index() ekledik çünkü "sadece sistem bildirimlerini getir" gibi sorgular atabilirsin.
            $table->string('type')->default('info')->after('id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
