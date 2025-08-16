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
        Schema::create('order_carpet_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_carpet_id')->constrained('order_carpets', 'id')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users', 'id')->nullOnDelete();
            $table->text('photo_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_carpet_photos');
    }
};
