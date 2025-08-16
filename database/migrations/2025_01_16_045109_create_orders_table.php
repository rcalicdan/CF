<?php

use App\Enums\OrderStatus;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients', 'id')->cascadeOnDelete();
            $table->foreignId('assigned_driver_id')->nullable()->constrained('drivers', 'id');
            $table->date('schedule_date')->nullable();
            $table->foreignId('price_list_id')->constrained('price_lists', 'id');
            $table->enum('status', array_column(OrderStatus::cases(), 'value'))
                ->default(OrderStatus::PENDING->value);
            $table->decimal('total_amount', 10, 2);
            $table->boolean('is_complaint')->default(false);
            $table->timestamps();

            $table->index('schedule_date', 'schedule_date_idx');
            $table->index('status', 'order_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
