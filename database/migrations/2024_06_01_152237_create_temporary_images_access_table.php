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
        Schema::create('temporary_images_access', function (Blueprint $table) {
            $table->id();
            $table->string("image_uqid")->references('uqid')->on('images');;
            $table->string("token")->unique();
            $table->string("owner");
            $table->timestamp("expiresAt");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_images_access');
    }
};
