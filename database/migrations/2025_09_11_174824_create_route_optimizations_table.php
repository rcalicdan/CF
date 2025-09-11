<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('route_optimizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained()->onDelete('cascade');
            $table->date('optimization_date');
            $table->json('optimization_result');
            $table->json('order_sequence');
            $table->decimal('total_distance', 8, 2)->nullable();
            $table->integer('total_time')->nullable();
            $table->decimal('estimated_fuel_cost', 8, 2)->nullable();
            $table->decimal('carbon_footprint', 8, 2)->nullable();
            $table->boolean('is_manual_edit')->default(false);
            $table->json('manual_modifications')->nullable();
            $table->timestamps();

            $table->unique(['driver_id', 'optimization_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('route_optimizations');
    }
};
