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
        Schema::create('carpet_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_carpet_id')->constrained('order_carpets', 'id')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services', 'id')->cascadeOnDelete();
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carpet_services');
    }
};
