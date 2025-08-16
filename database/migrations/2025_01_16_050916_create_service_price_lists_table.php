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
        Schema::create('service_price_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_list_id')->constrained('price_lists', 'id')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services', 'id')->cascadeOnDelete();
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_price_lists');
    }
};
