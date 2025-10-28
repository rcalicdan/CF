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
        Schema::create('client_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('opinion');
            $table->tinyInteger('rating')->unsigned()->comment('Rating from 1 to 5');
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->index('rating');
            $table->index('is_featured');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_feedback');
    }
};