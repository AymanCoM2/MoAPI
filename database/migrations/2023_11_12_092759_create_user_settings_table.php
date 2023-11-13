<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->string('app_version')->nullable()->default('');
            $table->timestamps();
        });
        // TODO Make it Here insert the First Row For the version 
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
